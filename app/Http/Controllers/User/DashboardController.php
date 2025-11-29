<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\Album;
use App\Models\Post;
use App\Models\Playlist;
use App\Models\Analytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // User Stats
        $stats = [
            'total_tracks' => $user->tracks()->count(),
            'total_albums' => $user->albums()->count(),
            'total_playlists' => $user->playlists()->count(),
            'total_plays' => $user->tracks()->sum('plays'),
            'total_likes' => $user->tracks()->sum('likes'),
            'total_downloads' => $user->tracks()->sum('downloads'),
            'total_followers' => $user->followers()->count(),
            'total_following' => $user->following()->count(),
            'total_points' => $user->points ?? 0,
            'total_revenue' => $user->earnings()->sum('amount') ?? 0,
        ];

        // Recent Tracks
        $recentTracks = $user->tracks()
            ->latest()
            ->limit(5)
            ->get();

        // Top Tracks (by plays)
        $topTracks = $user->tracks()
            ->orderBy('plays', 'desc')
            ->limit(5)
            ->get();

        // Analytics - Last 30 days
        $analytics = [
            'plays_chart' => $this->getPlaysChart($user),
            'listeners_chart' => $this->getListenersChart($user),
            'revenue_chart' => $this->getRevenueChart($user),
        ];

        // Recent Activity
        $recentActivity = $this->getRecentActivity($user);

        // Recommendations
        $recommendations = [
            'trending_tracks' => Track::where('user_id', '!=', $user->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('plays', 'desc')
                ->limit(5)
                ->get(),
            'suggested_artists' => $this->getSuggestedArtists($user),
        ];

        // Storage Info
        $storage = [
            'used' => $user->used_storage ?? 0,
            'limit' => $user->storage_limit ?? (1024 * 1024 * 1024), // 1GB default
            'percentage' => (($user->used_storage ?? 0) / ($user->storage_limit ?? 1)) * 100,
        ];

        // Upcoming Events
        $upcomingEvents = $user->events()
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->limit(3)
            ->get();

        // Active Song Battles
        $activeBattles = $user->songBattleVersions()
            ->whereHas('songBattle', function ($q) {
                $q->where('status', 'active');
            })
            ->with('songBattle')
            ->limit(3)
            ->get();

        // Notifications
        $notifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get();

        return view('user.dashboard', compact(
            'stats',
            'recentTracks',
            'topTracks',
            'analytics',
            'recentActivity',
            'recommendations',
            'storage',
            'upcomingEvents',
            'activeBattles',
            'notifications'
        ));
    }

    private function getPlaysChart($user)
    {
        return Track::select(DB::raw('DATE(updated_at) as date'), DB::raw('SUM(plays) as plays'))
            ->where('user_id', $user->id)
            ->where('updated_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getListenersChart($user)
    {
        // Get listeners data from the last 30 days
        return \App\Models\Listener::whereHasMorph('listenable', ['App\Models\Track', 'App\Models\Album'], function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->selectRaw('DATE(started_at) as date, COUNT(DISTINCT user_id) as listeners')
            ->where('started_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getRevenueChart($user)
    {
        return DB::table('payment_transactions')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as revenue'))
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getRecentActivity($user)
    {
        $activities = [];

        // Recent uploads
        $uploads = $user->tracks()
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($track) {
                return [
                    'type' => 'upload',
                    'message' => "You uploaded \"{$track->title}\"",
                    'time' => $track->created_at,
                    'icon' => '⬆️',
                ];
            });

        // Recent likes/plays milestones
        $milestones = $user->tracks()
            ->where('plays', '>=', 1000)
            ->latest()
            ->limit(2)
            ->get()
            ->map(function ($track) {
                return [
                    'type' => 'milestone',
                    'message' => "\"{$track->title}\" reached " . number_format($track->plays) . " plays! 🎉",
                    'time' => $track->updated_at,
                    'icon' => '🎉',
                ];
            });

        $activities = $uploads->concat($milestones)
            ->sortByDesc('time')
            ->take(5);

        return $activities;
    }

    private function getSuggestedArtists($user)
    {
        // Get artists in similar genres
        $userGenres = $user->tracks()->pluck('genre')->unique();

        return User::whereHas('tracks', function ($q) use ($userGenres) {
            $q->whereIn('genre', $userGenres);
        })
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $user->following()->pluck('following_id'))
            ->withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->limit(5)
            ->get();
    }
}
