<?php

namespace App\Services;

use App\Models\Track;
use App\Models\TrackValuation;
use App\Models\ValuationHistory;
use App\Models\TrackInvestment;
use App\Models\PlatformSetting;

class BlockchainValuationService
{
    /**
     * Calculate track value based on engagement metrics
     */
    public function calculateTrackValue(Track $track): float
    {
        $valuation = $track->valuation ?? $this->initializeValuation($track);

        // Get algorithm settings from admin
        $viewWeight = PlatformSetting::get('blockchain_view_weight', 0.1);
        $playWeight = PlatformSetting::get('blockchain_play_weight', 0.3);
        $likeWeight = PlatformSetting::get('blockchain_like_weight', 0.25);
        $shareWeight = PlatformSetting::get('blockchain_share_weight', 0.2);
        $downloadWeight = PlatformSetting::get('blockchain_download_weight', 0.15);
        $baseValue = PlatformSetting::get('blockchain_base_value', 1.00);
        $maxValue = PlatformSetting::get('blockchain_max_value', 10000.00);

        // Calculate engagement score
        $engagementScore = (
            ($track->views * $viewWeight) +
            ($track->plays * $playWeight) +
            ($track->likes * $likeWeight) +
            ($track->shares * $shareWeight) +
            ($track->downloads * $downloadWeight)
        );

        // Calculate trending score (recent activity)
        $trendingScore = $this->calculateTrendingScore($track);

        // Calculate new value
        $newValue = $baseValue + ($engagementScore * 0.01) + ($trendingScore * 0.05);
        $newValue = min($newValue, $maxValue); // Cap at max value

        // Update valuation
        $oldValue = $valuation->current_value;
        $changePercentage = $oldValue > 0 ? (($newValue - $oldValue) / $oldValue) * 100 : 0;

        $valuation->update([
            'current_value' => $newValue,
            'peak_value' => max($valuation->peak_value, $newValue),
            'lowest_value' => min($valuation->lowest_value, $newValue),
            'total_views' => $track->views,
            'total_plays' => $track->plays,
            'total_likes' => $track->likes,
            'total_shares' => $track->shares,
            'total_downloads' => $track->downloads,
            'engagement_score' => $engagementScore,
            'trending_score' => $trendingScore,
            'last_calculated_at' => now(),
        ]);

        // Record history if significant change
        if (abs($changePercentage) >= 1) {
            $this->recordValuationHistory($track, $newValue, $changePercentage);
        }

        // Update all active investments
        $this->updateInvestments($track, $newValue);

        return $newValue;
    }

    /**
     * Initialize valuation for new track
     */
    protected function initializeValuation(Track $track): TrackValuation
    {
        $initialValue = PlatformSetting::get('blockchain_initial_value', 1.00);

        return TrackValuation::create([
            'track_id' => $track->id,
            'current_value' => $initialValue,
            'initial_value' => $initialValue,
            'peak_value' => $initialValue,
            'lowest_value' => $initialValue,
        ]);
    }

    /**
     * Calculate trending score based on recent activity
     */
    protected function calculateTrendingScore(Track $track): float
    {
        // Get recent activity (last 7 days)
        $recentDays = PlatformSetting::get('blockchain_trending_days', 7);

        // This would ideally query analytics table for recent metrics
        // For now, we'll use a simplified calculation
        $daysSinceCreated = $track->created_at->diffInDays(now());

        if ($daysSinceCreated == 0) {
            return $track->plays * 2; // New tracks get boost
        }

        $dailyPlays = $track->plays / max($daysSinceCreated, 1);
        $dailyLikes = $track->likes / max($daysSinceCreated, 1);

        return ($dailyPlays * 0.6) + ($dailyLikes * 0.4);
    }

    /**
     * Record valuation history
     */
    protected function recordValuationHistory(Track $track, float $newValue, float $changePercentage): void
    {
        ValuationHistory::create([
            'track_id' => $track->id,
            'value' => $newValue,
            'change_percentage' => $changePercentage,
            'change_reason' => $this->determineChangeReason($track),
            'metrics' => [
                'views' => $track->views,
                'plays' => $track->plays,
                'likes' => $track->likes,
                'shares' => $track->shares,
                'downloads' => $track->downloads,
            ],
        ]);
    }

    /**
     * Determine primary reason for value change
     */
    protected function determineChangeReason(Track $track): string
    {
        $valuation = $track->valuation;

        $viewIncrease = $track->views - $valuation->total_views;
        $playIncrease = $track->plays - $valuation->total_plays;
        $likeIncrease = $track->likes - $valuation->total_likes;
        $shareIncrease = $track->shares - $valuation->total_shares;

        $changes = [
            'views' => $viewIncrease,
            'plays' => $playIncrease,
            'likes' => $likeIncrease,
            'shares' => $shareIncrease,
        ];

        return array_search(max($changes), $changes);
    }

    /**
     * Update all active investments for a track
     */
    protected function updateInvestments(Track $track, float $newValue): void
    {
        $investments = TrackInvestment::where('track_id', $track->id)
            ->where('is_active', true)
            ->get();

        foreach ($investments as $investment) {
            $investment->updateCurrentValue($newValue);
        }
    }

    /**
     * Get top valued tracks
     */
    public function getTopValuedTracks(int $limit = 10)
    {
        return TrackValuation::with('track')
            ->orderBy('current_value', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending tracks (biggest gainers)
     */
    public function getTrendingTracks(int $limit = 10)
    {
        return TrackValuation::with('track')
            ->orderBy('trending_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Recalculate all track values (admin function)
     */
    public function recalculateAllValues(): int
    {
        $tracks = Track::all();
        $count = 0;

        foreach ($tracks as $track) {
            $this->calculateTrackValue($track);
            $count++;
        }

        return $count;
    }

    /**
     * Get valuation settings
     */
    public function getSettings(): array
    {
        return [
            'view_weight' => PlatformSetting::get('blockchain_view_weight', 0.1),
            'play_weight' => PlatformSetting::get('blockchain_play_weight', 0.3),
            'like_weight' => PlatformSetting::get('blockchain_like_weight', 0.25),
            'share_weight' => PlatformSetting::get('blockchain_share_weight', 0.2),
            'download_weight' => PlatformSetting::get('blockchain_download_weight', 0.15),
            'base_value' => PlatformSetting::get('blockchain_base_value', 1.00),
            'initial_value' => PlatformSetting::get('blockchain_initial_value', 1.00),
            'max_value' => PlatformSetting::get('blockchain_max_value', 10000.00),
            'trending_days' => PlatformSetting::get('blockchain_trending_days', 7),
            'enabled' => PlatformSetting::get('blockchain_enabled', true),
        ];
    }

    /**
     * Update valuation settings (admin)
     */
    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            PlatformSetting::set("blockchain_{$key}", $value, 'number');
        }
    }
}
