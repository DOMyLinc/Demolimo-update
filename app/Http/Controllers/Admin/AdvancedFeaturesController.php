<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FanClub;
use App\Models\FanClubMembership;
use App\Models\PresaveCampaign;
use App\Models\SmartPlaylist;
use App\Models\PlaylistFolder;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvancedFeaturesController extends Controller
{
    /**
     * Fan Clubs Management
     */
    public function fanClubs()
    {
        $fanClubs = FanClub::with('artist')
            ->withCount('members')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_fan_clubs' => FanClub::count(),
            'active_fan_clubs' => FanClub::where('is_active', true)->count(),
            'total_members' => FanClubMembership::where('status', 'active')->count(),
            'total_revenue' => FanClub::join('fan_club_memberships', 'fan_clubs.id', '=', 'fan_club_memberships.fan_club_id')
                ->where('fan_club_memberships.status', 'active')
                ->sum('fan_clubs.monthly_price'),
        ];

        return view('admin.advanced.fan-clubs', compact('fanClubs', 'stats'));
    }

    /**
     * View fan club details
     */
    public function showFanClub(FanClub $fanClub)
    {
        $fanClub->load([
            'artist',
            'memberships' => function ($q) {
                $q->with('user')->latest();
            }
        ]);

        return view('admin.advanced.fan-club-details', compact('fanClub'));
    }

    /**
     * Disable fan club
     */
    public function disableFanClub(FanClub $fanClub)
    {
        $fanClub->update(['is_active' => false]);
        return back()->with('success', 'Fan club disabled!');
    }

    /**
     * Enable fan club
     */
    public function enableFanClub(FanClub $fanClub)
    {
        $fanClub->update(['is_active' => true]);
        return back()->with('success', 'Fan club enabled!');
    }

    /**
     * Fan Club Settings
     */
    public function fanClubSettings()
    {
        $settings = [
            'enable_fan_clubs' => FeatureFlag::isEnabled('enable_fan_clubs'),
            'enable_exclusive_content' => FeatureFlag::isEnabled('enable_exclusive_content'),
        ];

        return view('admin.advanced.fan-club-settings', compact('settings'));
    }

    /**
     * Pre-Save Campaigns
     */
    public function presaveCampaigns()
    {
        $campaigns = PresaveCampaign::with(['user', 'releasable'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_campaigns' => PresaveCampaign::count(),
            'active_campaigns' => PresaveCampaign::where('is_active', true)->count(),
            'total_presaves' => DB::table('presave_users')->count(),
            'upcoming_releases' => PresaveCampaign::where('release_date', '>', now())
                ->where('is_active', true)
                ->count(),
        ];

        return view('admin.advanced.presave-campaigns', compact('campaigns', 'stats'));
    }

    /**
     * Smart Playlists Management
     */
    public function smartPlaylists()
    {
        $smartPlaylists = SmartPlaylist::with('playlist.user')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_smart_playlists' => SmartPlaylist::count(),
            'auto_updating' => SmartPlaylist::where('auto_update', true)->count(),
            'recently_updated' => SmartPlaylist::where('last_updated_at', '>', now()->subHours(6))->count(),
        ];

        return view('admin.advanced.smart-playlists', compact('smartPlaylists', 'stats'));
    }

    /**
     * Force update smart playlist
     */
    public function updateSmartPlaylist(SmartPlaylist $smartPlaylist)
    {
        $service = new \App\Services\SmartPlaylistService();
        $service->updatePlaylist($smartPlaylist);

        return back()->with('success', 'Smart playlist updated!');
    }

    /**
     * Playlist Folders Management
     */
    public function playlistFolders()
    {
        $folders = PlaylistFolder::with('user')
            ->withCount('playlists')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_folders' => PlaylistFolder::count(),
            'folders_with_playlists' => PlaylistFolder::has('playlists')->count(),
        ];

        return view('admin.advanced.playlist-folders', compact('folders', 'stats'));
    }

    /**
     * Search Analytics
     */
    public function searchAnalytics()
    {
        $trendingSearches = DB::table('search_history')
            ->select('query', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit(50)
            ->get();

        $stats = [
            'total_searches' => DB::table('search_history')->count(),
            'searches_today' => DB::table('search_history')->whereDate('created_at', today())->count(),
            'unique_queries' => DB::table('search_history')->distinct('query')->count('query'),
            'average_results' => DB::table('search_history')->avg('results_count'),
        ];

        $recentSearches = DB::table('search_history')
            ->latest()
            ->limit(100)
            ->get();

        return view('admin.advanced.search-analytics', compact('trendingSearches', 'stats', 'recentSearches'));
    }

    /**
     * Voice Search Logs
     */
    public function voiceSearchLogs()
    {
        $voiceSearches = DB::table('voice_searches')
            ->latest()
            ->paginate(50);

        $stats = [
            'total_voice_searches' => DB::table('voice_searches')->count(),
            'today' => DB::table('voice_searches')->whereDate('created_at', today())->count(),
        ];

        return view('admin.advanced.voice-search-logs', compact('voiceSearches', 'stats'));
    }

    /**
     * Feature Flags Management
     */
    public function featureFlags()
    {
        $flags = FeatureFlag::orderBy('category')->orderBy('name')->get();

        $categories = $flags->groupBy('category');

        return view('admin.advanced.feature-flags', compact('flags', 'categories'));
    }

    /**
     * Toggle feature flag
     */
    public function toggleFeatureFlag(FeatureFlag $flag)
    {
        $flag->update(['is_enabled' => !$flag->is_enabled]);

        return back()->with('success', "Feature '{$flag->name}' " . ($flag->is_enabled ? 'enabled' : 'disabled') . '!');
    }

    /**
     * Update feature flag value
     */
    public function updateFeatureFlagValue(Request $request, FeatureFlag $flag)
    {
        $validated = $request->validate([
            'value' => 'nullable|string',
        ]);

        $flag->update(['value' => $validated['value']]);

        return back()->with('success', 'Feature flag value updated!');
    }
}
