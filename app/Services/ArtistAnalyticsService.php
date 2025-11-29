<?php

namespace App\Services;

use App\Models\User;
use App\Models\Track;
use App\Models\ArtistAnalytics;
use App\Models\Listener;
use App\Models\Revenue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ArtistAnalyticsService
{
    /**
     * Generate comprehensive analytics for an artist
     */
    public function generateAnalytics($userId, $startDate = null, $endDate = null)
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        return [
            'overview' => $this->getOverview($user, $startDate, $endDate),
            'demographics' => $this->getDemographics($user, $startDate, $endDate),
            'geographic' => $this->getGeographic($user, $startDate, $endDate),
            'revenue' => $this->getRevenue($user, $startDate, $endDate),
            'top_tracks' => $this->getTopTracks($user, $startDate, $endDate),
            'playlist_placements' => $this->getPlaylistPlacements($user),
            'growth' => $this->getGrowth($user, $startDate, $endDate),
            'engagement' => $this->getEngagement($user, $startDate, $endDate),
        ];
    }

    /**
     * Overview statistics
     */
    protected function getOverview($user, $startDate, $endDate)
    {
        $tracks = $user->tracks;

        return [
            'total_plays' => Listener::whereHasMorph('listenable', ['App\Models\Track'], function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->whereBetween('started_at', [$startDate, $endDate])
                ->count(),
            'unique_listeners' => Listener::whereHasMorph('listenable', ['App\Models\Track'], function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->whereBetween('started_at', [$startDate, $endDate])
                ->distinct('user_id')
                ->count('user_id'),
            'total_followers' => $user->followers()->count(),
            'new_followers' => $user->followers()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'total_tracks' => $tracks->count(),
            'total_albums' => $user->albums()->count(),
        ];
    }

    /**
     * Demographics data
     */
    protected function getDemographics($user, $startDate, $endDate)
    {
        // Get listener demographics
        $listeners = Listener::whereHasMorph('listenable', ['App\Models\Track'], function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->with('user')
            ->get();

        // Age groups (if user has birthdate)
        $ageGroups = $listeners->groupBy(function ($listener) {
            if (!$listener->user || !$listener->user->birthdate) {
                return 'Unknown';
            }
            $age = $listener->user->birthdate->age;
            if ($age < 18)
                return '< 18';
            if ($age < 25)
                return '18-24';
            if ($age < 35)
                return '25-34';
            if ($age < 45)
                return '35-44';
            if ($age < 55)
                return '45-54';
            return '55+';
        })->map->count();

        return [
            'age_groups' => $ageGroups,
            'total_listeners' => $listeners->count(),
        ];
    }

    /**
     * Geographic distribution
     */
    protected function getGeographic($user, $startDate, $endDate)
    {
        $byCountry = Listener::whereHasMorph('listenable', ['App\Models\Track'], function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereBetween('started_at', [$startDate, $endDate])
            ->select('country', DB::raw('COUNT(*) as count'))
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $byCity = Listener::whereHasMorph('listenable', ['App\Models\Track'], function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereBetween('started_at', [$startDate, $endDate])
            ->select('city', 'country', DB::raw('COUNT(*) as count'))
            ->groupBy('city', 'country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'by_country' => $byCountry,
            'by_city' => $byCity,
        ];
    }

    /**
     * Revenue breakdown
     */
    protected function getRevenue($user, $startDate, $endDate)
    {
        $revenues = Revenue::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $bySource = $revenues->groupBy('source')->map(function ($items) {
            return [
                'total' => $items->sum('amount'),
                'platform_fee' => $items->sum('platform_fee'),
                'artist_amount' => $items->sum('artist_amount'),
                'count' => $items->count(),
            ];
        });

        $daily = $revenues->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($items) {
            return $items->sum('artist_amount');
        });

        return [
            'total_revenue' => $revenues->sum('artist_amount'),
            'platform_fees' => $revenues->sum('platform_fee'),
            'by_source' => $bySource,
            'daily_revenue' => $daily,
        ];
    }

    /**
     * Top performing tracks
     */
    protected function getTopTracks($user, $startDate, $endDate)
    {
        return Track::where('user_id', $user->id)
            ->withCount([
                'listeners' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('started_at', [$startDate, $endDate]);
                }
            ])
            ->orderByDesc('listeners_count')
            ->limit(10)
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'plays' => $track->listeners_count,
                    'likes' => $track->likes,
                    'downloads' => $track->downloads,
                ];
            });
    }

    /**
     * Playlist placements
     */
    protected function getPlaylistPlacements($user)
    {
        $trackIds = $user->tracks()->pluck('id');

        $placements = DB::table('playlist_track')
            ->whereIn('track_id', $trackIds)
            ->join('playlists', 'playlist_track.playlist_id', '=', 'playlists.id')
            ->where('playlists.is_public', true)
            ->select('playlists.name', 'playlists.user_id', DB::raw('COUNT(*) as track_count'))
            ->groupBy('playlists.id', 'playlists.name', 'playlists.user_id')
            ->orderByDesc('track_count')
            ->limit(20)
            ->get();

        return [
            'total_playlists' => $placements->count(),
            'top_playlists' => $placements,
        ];
    }

    /**
     * Growth metrics
     */
    protected function getGrowth($user, $startDate, $endDate)
    {
        $days = $startDate->diffInDays($endDate);

        $dailyFollowers = DB::table('followers')
            ->where('following_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyPlays = Listener::whereHasMorph('listenable', ['App\Models\Track'], function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereBetween('started_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(started_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'daily_followers' => $dailyFollowers,
            'daily_plays' => $dailyPlays,
            'follower_growth_rate' => $this->calculateGrowthRate($dailyFollowers),
            'play_growth_rate' => $this->calculateGrowthRate($dailyPlays),
        ];
    }

    /**
     * Engagement metrics
     */
    protected function getEngagement($user, $startDate, $endDate)
    {
        $tracks = $user->tracks;

        return [
            'total_likes' => $tracks->sum('likes'),
            'total_comments' => DB::table('comments')
                ->whereIn('commentable_id', $tracks->pluck('id'))
                ->where('commentable_type', 'App\Models\Track')
                ->count(),
            'total_shares' => DB::table('song_battle_shares')
                ->whereIn('song_battle_id', $user->songBattles()->pluck('id'))
                ->count(),
            'average_completion_rate' => $this->getAverageCompletionRate($user, $startDate, $endDate),
        ];
    }

    /**
     * Calculate growth rate
     */
    protected function calculateGrowthRate($dailyData)
    {
        if ($dailyData->count() < 2) {
            return 0;
        }

        $first = $dailyData->first()->count ?? 0;
        $last = $dailyData->last()->count ?? 0;

        if ($first == 0) {
            return 100;
        }

        return round((($last - $first) / $first) * 100, 2);
    }

    /**
     * Get average completion rate
     */
    protected function getAverageCompletionRate($user, $startDate, $endDate)
    {
        $listeners = Listener::whereHasMorph('listenable', ['App\Models\Track'], function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('duration')
            ->with('listenable')
            ->get();

        if ($listeners->isEmpty()) {
            return 0;
        }

        $totalCompletionRate = $listeners->sum(function ($listener) {
            if (!$listener->listenable || !$listener->listenable->duration) {
                return 0;
            }
            return min(100, ($listener->duration / $listener->listenable->duration) * 100);
        });

        return round($totalCompletionRate / $listeners->count(), 2);
    }

    /**
     * Store daily analytics snapshot
     */
    public function storeDailySnapshot($userId)
    {
        $analytics = $this->generateAnalytics($userId, now()->subDay(), now());

        ArtistAnalytics::create([
            'user_id' => $userId,
            'date' => now()->toDateString(),
            'plays' => $analytics['overview']['total_plays'],
            'unique_listeners' => $analytics['overview']['unique_listeners'],
            'new_followers' => $analytics['overview']['new_followers'],
            'revenue' => $analytics['revenue']['total_revenue'],
            'top_countries' => $analytics['geographic']['by_country']->take(5)->pluck('country'),
            'top_tracks' => $analytics['top_tracks']->take(5)->pluck('id'),
            'demographics' => $analytics['demographics'],
        ]);
    }
}
