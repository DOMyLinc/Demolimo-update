<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\Album;
use Illuminate\Http\Request;

class MusicController extends Controller
{
    public function tracks(Request $request)
    {
        $query = Track::with('user');
        
        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        
        $tracks = $query->latest()->paginate(20);
        return view('admin.music.tracks', compact('tracks'));
    }

    public function albums(Request $request)
    {
        $query = Album::with('user');
        
        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        
        $albums = $query->latest()->paginate(20);
        return view('admin.music.albums', compact('albums'));
    }

    public function toggleFeatured(Track $track)
    {
        $track->update(['is_featured' => !$track->is_featured]);
        return back()->with('success', 'Track featured status updated');
    }

    public function deleteTrack(Track $track)
    {
        $track->delete();
        return back()->with('success', 'Track deleted successfully');
    }

    public function deleteAlbum(Album $album)
    {
        $album->delete();
        return back()->with('success', 'Album deleted successfully');
    }
}
