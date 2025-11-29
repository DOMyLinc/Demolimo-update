<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\RecommendationEngine;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    protected $recommendationEngine;

    public function __construct(RecommendationEngine $recommendationEngine)
    {
        $this->recommendationEngine = $recommendationEngine;
    }

    public function discoverWeekly()
    {
        if (!FeatureFlag::isEnabled('enable_discover_weekly')) {
            return back()->with('error', 'Discover Weekly is currently disabled.');
        }

        $user = Auth::user();

        // Free users get basic recommendations
        if (!$user->isPro() && !$this->recommendationEngine->canAccessAdvancedRecommendations($user)) {
            return redirect()->route('user.subscription.plans')
                ->with('info', 'Upgrade to Pro for AI-powered Discover Weekly playlists.');
        }

        $recommendations = $this->recommendationEngine->generateRecommendations($user->id, 'discover_weekly');

        return view('user.recommendations.discover-weekly', compact('recommendations'));
    }

    public function releaseRadar()
    {
        if (!FeatureFlag::isEnabled('enable_release_radar')) {
            return back()->with('error', 'Release Radar is currently disabled.');
        }

        $user = Auth::user();
        $recommendations = $this->recommendationEngine->generateRecommendations($user->id, 'release_radar');

        return view('user.recommendations.release-radar', compact('recommendations'));
    }

    public function dailyMix()
    {
        if (!FeatureFlag::isEnabled('enable_daily_mix')) {
            return back()->with('error', 'Daily Mix is currently disabled.');
        }

        $user = Auth::user();

        if (!$user->isPro()) {
            return redirect()->route('user.subscription.plans')
                ->with('info', 'Upgrade to Pro for personalized Daily Mixes.');
        }

        $recommendations = $this->recommendationEngine->generateRecommendations($user->id, 'daily_mix');

        return view('user.recommendations.daily-mix', compact('recommendations'));
    }

    public function refresh(Request $request)
    {
        $type = $request->input('type', 'discover_weekly');

        $user = Auth::user();
        $recommendations = $this->recommendationEngine->generateRecommendations($user->id, $type);

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
        ]);
    }
}
