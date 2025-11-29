<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Auth::user()->playlists()
            ->withCount('tracks')
            ->latest()
            ->get();

        $collaborativePlaylists = Playlist::whereHas('collaborators', function ($q) {
            $q->where('user_id', Auth::id());
        })
            ->withCount('tracks')
            ->latest()
            ->get();

        $followedPlaylists = Auth::user()->followedPlaylists()
            ->withCount('tracks')
            ->latest()
            ->get();

        return view('user.playlists.index', compact('playlists', 'collaborativePlaylists', 'followedPlaylists'));
    }

    public function create()
    {
        return view('user.playlists.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
            'is_public' => 'boolean',
            'is_collaborative' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('playlists', 'public');
        }

        $playlist = Playlist::create($validated);

        return redirect()->route('user.playlists.show', $playlist)
            ->with('success', 'Playlist created successfully!');
    }

    public function show(Playlist $playlist)
    {
        $this->authorize('view', $playlist);

        $playlist->load(['tracks.user', 'user']);

        return view('user.playlists.show', compact('playlist'));
    }

    public function edit(Playlist $playlist)
    {
        $this->authorize('update', $playlist);

        return view('user.playlists.edit', compact('playlist'));
    }

    public function update(Request $request, Playlist $playlist)
    {
        $this->authorize('update', $playlist);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
            'is_public' => 'boolean',
            'is_collaborative' => 'boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('playlists', 'public');
        }

        $playlist->update($validated);

        return redirect()->route('user.playlists.show', $playlist)
            ->with('success', 'Playlist updated successfully!');
    }

    public function destroy(Playlist $playlist)
    {
        $this->authorize('delete', $playlist);

        $playlist->delete();

        return redirect()->route('user.playlists.index')
            ->with('success', 'Playlist deleted successfully!');
    }

    public function addTrack(Request $request, Playlist $playlist)
    {
        $this->authorize('update', $playlist);

        $validated = $request->validate([
            'track_id' => 'required|exists:tracks,id',
        ]);

        $track = Track::findOrFail($validated['track_id']);

        if (!$playlist->tracks()->where('track_id', $track->id)->exists()) {
            $position = $playlist->tracks()->max('position') + 1;

            $playlist->tracks()->attach($track->id, [
                'position' => $position,
                'added_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Track added to playlist',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Track already in playlist',
        ], 400);
    }

    public function removeTrack(Playlist $playlist, Track $track)
    {
        $this->authorize('update', $playlist);

        $playlist->tracks()->detach($track->id);

        // Reorder positions
        $tracks = $playlist->tracks()->orderBy('position')->get();
        foreach ($tracks as $index => $t) {
            $playlist->tracks()->updateExistingPivot($t->id, ['position' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Track removed from playlist',
        ]);
    }

    public function reorderTracks(Request $request, Playlist $playlist)
    {
        $this->authorize('update', $playlist);

        $validated = $request->validate([
            'tracks' => 'required|array',
            'tracks.*' => 'exists:tracks,id',
        ]);

        foreach ($validated['tracks'] as $position => $trackId) {
            $playlist->tracks()->updateExistingPivot($trackId, ['position' => $position + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Playlist reordered successfully',
        ]);
    }

    public function follow(Playlist $playlist)
    {
        if (!$playlist->is_public) {
            return response()->json(['error' => 'Cannot follow private playlist'], 403);
        }

        Auth::user()->followedPlaylists()->syncWithoutDetaching([$playlist->id]);

        return response()->json([
            'success' => true,
            'message' => 'Following playlist',
        ]);
    }

    public function unfollow(Playlist $playlist)
    {
        Auth::user()->followedPlaylists()->detach($playlist->id);

        return response()->json([
            'success' => true,
            'message' => 'Unfollowed playlist',
        ]);
    }
}
