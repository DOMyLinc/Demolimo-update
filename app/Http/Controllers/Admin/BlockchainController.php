<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrackValuation;
use App\Models\TrackInvestment;
use App\Models\Track;
use App\Services\BlockchainValuationService;
use Illuminate\Http\Request;

class BlockchainController extends Controller
{
    protected $blockchainService;

    public function __construct(BlockchainValuationService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    public function index()
    {
        if (!feature_enabled('blockchain_valuation')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Blockchain Valuation feature is currently disabled.');
        }

        $topValued = $this->blockchainService->getTopValuedTracks(10);
        $trending = $this->blockchainService->getTrendingTracks(10);

        $stats = [
            'total_track_value' => TrackValuation::sum('current_value'),
            'total_investments' => TrackInvestment::where('is_active', true)->sum('invested_amount'),
            'total_profit_loss' => TrackInvestment::where('is_active', true)->sum('profit_loss'),
            'active_investors' => TrackInvestment::where('is_active', true)->distinct('user_id')->count(),
        ];

        $settings = $this->blockchainService->getSettings();

        return view('admin.blockchain.index', compact('topValued', 'trending', 'stats', 'settings'));
    }

    public function settings()
    {
        $settings = $this->blockchainService->getSettings();
        return view('admin.blockchain.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'view_weight' => 'required|numeric|min:0|max:1',
            'play_weight' => 'required|numeric|min:0|max:1',
            'like_weight' => 'required|numeric|min:0|max:1',
            'share_weight' => 'required|numeric|min:0|max:1',
            'download_weight' => 'required|numeric|min:0|max:1',
            'base_value' => 'required|numeric|min:0',
            'initial_value' => 'required|numeric|min:0',
            'max_value' => 'required|numeric|min:0',
            'trending_days' => 'required|integer|min:1|max:365',
            'enabled' => 'boolean',
        ]);

        $this->blockchainService->updateSettings($validated);

        return back()->with('success', 'Blockchain settings updated successfully');
    }

    public function recalculateAll()
    {
        $count = $this->blockchainService->recalculateAllValues();

        return back()->with('success', "Recalculated values for {$count} tracks");
    }

    public function trackValuation(Track $track)
    {
        $valuation = $track->valuation;

        if (!$valuation) {
            $this->blockchainService->calculateTrackValue($track);
            $valuation = $track->fresh()->valuation;
        }

        $history = $valuation->history()->latest()->limit(30)->get();
        $investments = $valuation->investments()->with('user')->get();

        return view('admin.blockchain.track-valuation', compact('track', 'valuation', 'history', 'investments'));
    }

    public function manualRecalculate(Track $track)
    {
        $newValue = $this->blockchainService->calculateTrackValue($track);

        return back()->with('success', "Track value recalculated: $" . number_format($newValue, 2));
    }

    public function investments()
    {
        $investments = TrackInvestment::with(['user', 'track'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_active' => TrackInvestment::where('is_active', true)->count(),
            'total_sold' => TrackInvestment::where('is_active', false)->count(),
            'total_invested' => TrackInvestment::sum('invested_amount'),
            'total_profit' => TrackInvestment::where('profit_loss', '>', 0)->sum('profit_loss'),
            'total_loss' => TrackInvestment::where('profit_loss', '<', 0)->sum('profit_loss'),
        ];

        return view('admin.blockchain.investments', compact('investments', 'stats'));
    }

    public function toggleSystem()
    {
        $current = \App\Models\PlatformSetting::get('blockchain_enabled', true);
        \App\Models\PlatformSetting::set('blockchain_enabled', !$current, 'boolean');

        return back()->with('success', 'Blockchain system ' . (!$current ? 'enabled' : 'disabled'));
    }
}
