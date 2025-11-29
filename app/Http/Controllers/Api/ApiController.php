<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\Album;
use App\Models\User;
use App\Models\Playlist;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    // Tracks
    public function tracks(Request $request)
    {
        $tracks = Track::with('user:id,name')
            ->when($request->genre, fn($q) => $q->where('genre', $request->genre))
            ->latest()
            ->paginate(20);

        return response()->json($tracks);
    }

    public function track($id)
    {
        $track = Track::with(['user', 'album'])->findOrFail($id);
        return response()->json($track);
    }

    // Albums
    public function albums()
    {
        $albums = Album::with('user:id,name')->latest()->paginate(20);
        return response()->json($albums);
    }

    public function album($id)
    {
        $album = Album::with(['user', 'tracks'])->findOrFail($id);
        return response()->json($album);
    }

    // Artists
    public function artists()
    {
        $artists = User::whereHas('tracks')
            ->withCount('tracks', 'followers')
            ->latest()
            ->paginate(20);

        return response()->json($artists);
    }

    public function artist($id)
    {
        $artist = User::with(['tracks', 'albums'])
            ->withCount('tracks', 'followers')
            ->findOrFail($id);

        return response()->json($artist);
    }

    // Playlists
    public function playlists()
    {
        $playlists = Playlist::where('is_public', true)
            ->with('user:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($playlists);
    }

    public function playlist($id)
    {
        $playlist = Playlist::with(['user', 'tracks'])->findOrFail($id);
        return response()->json($playlist);
    }

    // Search
    public function search(Request $request)
    {
        $query = $request->input('q');

        return response()->json([
            'tracks' => Track::where('title', 'LIKE', "%{$query}%")->limit(10)->get(),
            'albums' => Album::where('title', 'LIKE', "%{$query}%")->limit(10)->get(),
            'artists' => User::where('name', 'LIKE', "%{$query}%")->whereHas('tracks')->limit(10)->get(),
        ]);
    }
}
