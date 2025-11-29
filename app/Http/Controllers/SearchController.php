<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\Album;
use App\Models\User;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return view('search.index', [
                'query' => '',
                'tracks' => collect(),
                'albums' => collect(),
                'artists' => collect(),
                'playlists' => collect(),
            ]);
        }

        // Search tracks
        $tracks = Track::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('genre', 'LIKE', "%{$query}%")
            ->with('user')
            ->limit(20)
            ->get();

        // Search albums
        $albums = Album::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->with('user')
            ->limit(10)
            ->get();

        // Search artists
        $artists = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('bio', 'LIKE', "%{$query}%")
            ->whereHas('tracks')
            ->withCount('tracks', 'followers')
            ->limit(10)
            ->get();

        // Search playlists
        $playlists = Playlist::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->where('is_public', true)
            ->with('user')
            ->limit(10)
            ->get();

        return view('search.index', compact('query', 'tracks', 'albums', 'artists', 'playlists'));
    }

    public function api(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all'); // all, tracks, albums, artists, playlists

        if (empty($query)) {
            return response()->json([
                'tracks' => [],
                'albums' => [],
                'artists' => [],
                'playlists' => [],
            ]);
        }

        $results = [];

        if ($type === 'all' || $type === 'tracks') {
            $results['tracks'] = Track::where('title', 'LIKE', "%{$query}%")
                ->with('user:id,name')
                ->limit(10)
                ->get()
                ->map(function ($track) {
                    return [
                        'id' => $track->id,
                        'title' => $track->title,
                        'artist' => $track->user->name,
                        'duration' => $track->duration,
                        'cover' => $track->cover_image,
                        'url' => route('user.tracks.show', $track),
                    ];
                });
        }

        if ($type === 'all' || $type === 'albums') {
            $results['albums'] = Album::where('title', 'LIKE', "%{$query}%")
                ->with('user:id,name')
                ->limit(10)
                ->get()
                ->map(function ($album) {
                    return [
                        'id' => $album->id,
                        'title' => $album->title,
                        'artist' => $album->user->name,
                        'tracks_count' => $album->tracks()->count(),
                        'cover' => $album->cover_image,
                    ];
                });
        }

        if ($type === 'all' || $type === 'artists') {
            $results['artists'] = User::where('name', 'LIKE', "%{$query}%")
                ->whereHas('tracks')
                ->withCount('tracks', 'followers')
                ->limit(10)
                ->get()
                ->map(function ($artist) {
                    return [
                        'id' => $artist->id,
                        'name' => $artist->name,
                        'tracks_count' => $artist->tracks_count,
                        'followers_count' => $artist->followers_count,
                        'avatar' => $artist->avatar,
                    ];
                });
        }

        if ($type === 'all' || $type === 'playlists') {
            $results['playlists'] = Playlist::where('name', 'LIKE', "%{$query}%")
                ->where('is_public', true)
                ->with('user:id,name')
                ->limit(10)
                ->get()
                ->map(function ($playlist) {
                    return [
                        'id' => $playlist->id,
                        'name' => $playlist->name,
                        'creator' => $playlist->user->name,
                        'tracks_count' => $playlist->track_count,
                        'cover' => $playlist->cover_image,
                    ];
                });
        }

        return response()->json($results);
    }

    public function autocomplete(Request $request)
    {
        $query = $request->input('q');

        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = [];

        // Get top 5 tracks
        $tracks = Track::where('title', 'LIKE', "%{$query}%")
            ->with('user:id,name')
            ->limit(5)
            ->get()
            ->map(function ($track) {
                return [
                    'type' => 'track',
                    'id' => $track->id,
                    'text' => $track->title,
                    'subtitle' => $track->user->name,
                    'url' => route('user.tracks.show', $track),
                ];
            });

        // Get top 3 artists
        $artists = User::where('name', 'LIKE', "%{$query}%")
            ->whereHas('tracks')
            ->limit(3)
            ->get()
            ->map(function ($artist) {
                return [
                    'type' => 'artist',
                    'id' => $artist->id,
                    'text' => $artist->name,
                    'subtitle' => 'Artist',
                    'url' => '#',
                ];
            });

        $suggestions = $tracks->concat($artists)->take(8);

        return response()->json($suggestions);
    }
}
