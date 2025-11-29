<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrackManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Track::with(['user', 'album']);

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tracks = $query->latest()->paginate(20);

        $stats = [
            'total_tracks' => Track::count(),
            'public_tracks' => Track::where('visibility', 'public')->count(),
            'private_tracks' => Track::where('visibility', 'private')->count(),
            'total_plays' => Track::sum('plays'),
        ];

        return view('admin.tracks.index', compact('tracks', 'stats'));
    }

    public function show(Track $track)
    {
        $track->load(['user', 'album', 'analytics']);
        return view('admin.tracks.show', compact('track'));
    }

    public function destroy(Track $track)
    {
        // Delete associated file from storage
        if ($track->file_path) {
            Storage::delete($track->file_path);
        }

        $track->delete();

        return redirect()->route('admin.tracks.index')
            ->with('success', 'Track deleted successfully');
    }

    public function approve(Track $track)
    {
        $track->update(['status' => 'approved']);
        return back()->with('success', 'Track approved successfully');
    }

    public function reject(Track $track)
    {
        $track->update(['status' => 'rejected']);
        return back()->with('success', 'Track rejected successfully');
    }

    /**
     * Add fake interactions to a track
     */
    public function addInteractions(Request $request, Track $track)
    {
        $request->validate([
            'plays' => 'nullable|integer|min:0',
            'views' => 'nullable|integer|min:0',
            'likes' => 'nullable|integer|min:0',
            'downloads' => 'nullable|integer|min:0',
        ]);

        $updates = [];

        if ($request->has('plays') && $request->plays > 0) {
            $track->increment('plays', $request->plays);
        }

        if ($request->has('views') && $request->views > 0) {
            $track->increment('views', $request->views);
        }

        if ($request->has('likes') && $request->likes > 0) {
            $track->increment('likes', $request->likes);
        }

        if ($request->has('downloads') && $request->downloads > 0) {
            $track->increment('downloads', $request->downloads);
        }

        return back()->with('success', 'Interactions added successfully.');
    }
}
