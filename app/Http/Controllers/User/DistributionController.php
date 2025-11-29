<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\Track;
use App\Models\Album;
use Illuminate\Http\Request;

class DistributionController extends Controller
{
    /**
     * Display user's distribution requests.
     */
    public function index()
    {
        $distributions = Distribution::where('user_id', auth()->id())
            ->with(['track', 'album'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Distribution::where('user_id', auth()->id())->count(),
            'pending' => Distribution::where('user_id', auth()->id())->where('status', 'pending')->count(),
            'approved' => Distribution::where('user_id', auth()->id())->where('status', 'approved')->count(),
            'distributed' => Distribution::where('user_id', auth()->id())->where('status', 'distributed')->count(),
            'total_earnings' => Distribution::where('user_id', auth()->id())->sum('earnings'),
        ];

        return view('user.distribution.index', compact('distributions', 'stats'));
    }

    /**
     * Show the form for creating a new distribution request.
     */
    public function create()
    {
        $tracks = Track::where('user_id', auth()->id())
            ->whereDoesntHave('distributions', function ($query) {
                $query->whereIn('status', ['pending', 'approved', 'distributed']);
            })
            ->get();

        $albums = Album::where('user_id', auth()->id())
            ->whereDoesntHave('distributions', function ($query) {
                $query->whereIn('status', ['pending', 'approved', 'distributed']);
            })
            ->get();

        $platforms = [
            'spotify' => 'Spotify',
            'apple_music' => 'Apple Music',
            'youtube_music' => 'YouTube Music',
            'amazon_music' => 'Amazon Music',
            'tidal' => 'Tidal',
            'deezer' => 'Deezer',
            'soundcloud' => 'SoundCloud',
            'bandcamp' => 'Bandcamp',
        ];

        return view('user.distribution.create', compact('tracks', 'albums', 'platforms'));
    }

    /**
     * Store a newly created distribution request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:track,album',
            'content_id' => 'required|integer',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'required|string',
            'release_date' => 'required|date|after:today',
            'upc' => 'nullable|string|max:20',
            'isrc' => 'nullable|string|max:20',
        ]);

        // Verify ownership
        if ($request->type === 'track') {
            $track = Track::where('id', $request->content_id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $trackId = $track->id;
            $albumId = null;
        } else {
            $album = Album::where('id', $request->content_id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $trackId = null;
            $albumId = $album->id;
        }

        // Create distribution requests for each platform
        foreach ($request->platforms as $platform) {
            Distribution::create([
                'user_id' => auth()->id(),
                'track_id' => $trackId,
                'album_id' => $albumId,
                'platform' => $platform,
                'status' => 'pending',
                'release_date' => $request->release_date,
                'upc' => $request->upc,
                'isrc' => $request->isrc,
            ]);
        }

        return redirect()->route('distribution.index')
            ->with('success', 'Distribution request submitted successfully. Our team will review it shortly.');
    }

    /**
     * Display the specified distribution.
     */
    public function show(Distribution $distribution)
    {
        // Ensure user owns this distribution
        if ($distribution->user_id !== auth()->id()) {
            abort(403);
        }

        $distribution->load(['track', 'album']);

        return view('user.distribution.show', compact('distribution'));
    }

    /**
     * Display user's distribution earnings.
     */
    public function earnings()
    {
        $totalEarnings = Distribution::where('user_id', auth()->id())->sum('earnings');

        $earningsByPlatform = Distribution::where('user_id', auth()->id())
            ->selectRaw('platform, sum(earnings) as total')
            ->groupBy('platform')
            ->get();

        $earningsHistory = Distribution::where('user_id', auth()->id())
            ->where('earnings', '>', 0)
            ->with(['track', 'album'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('user.distribution.earnings', compact(
            'totalEarnings',
            'earningsByPlatform',
            'earningsHistory'
        ));
    }
}
