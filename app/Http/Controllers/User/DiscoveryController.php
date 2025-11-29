<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\Album;
use App\Models\Playlist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DiscoveryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $data = [
            'personalized' => $this->getPersonalizedRecommendations($user),
            'trending' => $this->getTrendingTracks(),
            'newReleases' => $this->getNewReleases(),
            'topGenres' => $this->getTopGenres(),
            'featuredPlaylists' => $this->getFeaturedPlaylists(),
        ];

        return view('user.discovery.index', $data);
    }

    public function genre($genre)
    {
        $tracks = Track::where('genre', $genre)
            ->with('user')
            ->orderBy('plays', 'desc')
            ->paginate(24);

        $topArtists = User::whereHas('tracks', function ($q) use ($genre) {
            $q->where('genre', $genre);
        })
            ->withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->limit(10)
            ->get();

        return view('user.discovery.genre', compact('genre', 'tracks', 'topArtists'));
    }

    public function trending()
    {
        $periods = ['today', 'week', 'month', 'all_time'];
        $period = request('period', 'week');

        $tracks = $this->getTrendingTracks($period);

        return view('user.discovery.trending', compact('tracks', 'period', 'periods'));
    }

    public function newReleases()
    {
        $tracks = Track::where('created_at', '>=', now()->subDays(30))
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        $albums = Album::where('created_at', '>=', now()->subDays(30))
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('user.discovery.new-releases', compact('tracks', 'albums'));
    }

    public function charts()
    {
        $topTracks = Track::orderBy('plays', 'desc')
            ->with('user')
            ->limit(50)
            ->get();

        $topArtists = User::withCount(['tracks', 'followers'])
            ->orderBy('followers_count', 'desc')
            ->limit(20)
            ->get();

        $topAlbums = Album::withCount('plays')
            ->orderBy('plays_count', 'desc')
            ->limit(20)
            ->get();

        return view('user.discovery.charts', compact('topTracks', 'topArtists', 'topAlbums'));
    }

    public function forYou()
    {
        $user = Auth::user();

        // Get user's listening history
        $listenedGenres = $user->tracks()->pluck('genre')->unique();

        // Recommend tracks in similar genres
        $recommendations = Track::whereIn('genre', $listenedGenres)
            ->where('user_id', '!=', $user->id)
            ->whereNotIn('id', $user->likedTracks()->pluck('track_id'))
            ->with('user')
            ->inRandomOrder()
            ->limit(20)
            ->get();

        // Recommended artists
        $recommendedArtists = User::whereHas('tracks', function ($q) use ($listenedGenres) {
            $q->whereIn('genre', $listenedGenres);
        })
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $user->following()->pluck('following_id'))
            ->withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->limit(10)
            ->get();

        return view('user.discovery.for-you', compact('recommendations', 'recommendedArtists'));
    }

    private function getPersonalizedRecommendations($user)
    {
        // Get user's favorite genres
        $favoriteGenres = $user->tracks()
            ->select('genre', DB::raw('COUNT(*) as count'))
            ->groupBy('genre')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->pluck('genre');

        if ($favoriteGenres->isEmpty()) {
            // Return popular tracks if no history
            return Track::orderBy('plays', 'desc')->limit(10)->get();
        }

        return Track::whereIn('genre', $favoriteGenres)
            ->where('user_id', '!=', $user->id)
            ->with('user')
            ->inRandomOrder()
            ->limit(10)
            ->get();
    }

    private function getTrendingTracks($period = 'week')
    {
        $query = Track::with('user');

        $dateFilter = match ($period) {
            'today' => now()->startOfDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            default => null,
        };

        if ($dateFilter) {
            $query->where('created_at', '>=', $dateFilter);
        }

        return $query->orderBy('plays', 'desc')
            ->limit(20)
            ->get();
    }

    private function getNewReleases()
    {
        return Track::where('created_at', '>=', now()->subDays(7))
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function getTopGenres()
    {
        return Track::select('genre', DB::raw('COUNT(*) as count'))
            ->groupBy('genre')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }

    private function getFeaturedPlaylists()
    {
        return Playlist::where('is_public', true)
            ->withCount('tracks')
            ->orderBy('followers', 'desc')
            ->limit(6)
            ->get();
    }
}
