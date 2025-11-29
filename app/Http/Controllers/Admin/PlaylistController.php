<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::with('user')->withCount('tracks')->latest()->paginate(20);
        return view('admin.playlists.index', compact('playlists'));
    }

    public function show(Playlist $playlist)
    {
        $playlist->load(['tracks.user', 'user']);
        return view('admin.playlists.show', compact('playlist'));
    }

    public function destroy(Playlist $playlist)
    {
        $playlist->delete();
        return redirect()->route('admin.playlists.index')->with('success', 'Playlist deleted successfully.');
    }

    public function toggleVisibility(Playlist $playlist)
    {
        $playlist->update(['is_public' => !$playlist->is_public]);
        return back()->with('success', 'Playlist visibility updated.');
    }
}
