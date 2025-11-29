<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RadioStation;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RadioStationController extends Controller
{
    public function index()
    {
        $stations = RadioStation::withCount('listeners')->latest()->paginate(20);

        return view('admin.radio.index', compact('stations'));
    }

    public function create()
    {
        $genres = ['Pop', 'Rock', 'Hip-Hop', 'Electronic', 'Jazz', 'Classical', 'Country', 'R&B'];
        $moods = ['Energetic', 'Chill', 'Focus', 'Party', 'Romantic', 'Workout'];

        return view('admin.radio.create', compact('genres', 'moods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:auto,live,scheduled',
            'genre' => 'nullable|string',
            'mood' => 'nullable|string',
            'stream_url' => 'nullable|url',
            'stream_type' => 'nullable|in:icecast,shoutcast,hls',
            'dj_name' => 'nullable|string',
            'dj_bio' => 'nullable|string',
            'website_url' => 'nullable|url',
            'social_links' => 'nullable|array',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('radio/covers', 'public');
        }

        // Handle DJ avatar upload
        if ($request->hasFile('dj_avatar')) {
            $validated['dj_avatar'] = $request->file('dj_avatar')->store('radio/avatars', 'public');
        }

        $station = RadioStation::create($validated);

        // Generate embed code
        $station->update(['embed_code' => $station->generateEmbedCode()]);

        return redirect()->route('admin.radio.edit', $station)->with('success', 'Radio station created successfully!');
    }

    public function edit(RadioStation $radio)
    {
        $genres = ['Pop', 'Rock', 'Hip-Hop', 'Electronic', 'Jazz', 'Classical', 'Country', 'R&B'];
        $moods = ['Energetic', 'Chill', 'Focus', 'Party', 'Romantic', 'Workout'];
        $tracks = Track::where('is_approved', true)->latest()->get();

        $station = $radio->load('playlist.track');

        return view('admin.radio.edit', compact('station', 'genres', 'moods', 'tracks'));
    }

    public function update(Request $request, RadioStation $radio)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:auto,live,scheduled',
            'genre' => 'nullable|string',
            'mood' => 'nullable|string',
            'stream_url' => 'nullable|url',
            'stream_type' => 'nullable|in:icecast,shoutcast,hls',
            'dj_name' => 'nullable|string',
            'dj_bio' => 'nullable|string',
            'website_url' => 'nullable|url',
            'social_links' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('radio/covers', 'public');
        }

        // Handle DJ avatar upload
        if ($request->hasFile('dj_avatar')) {
            $validated['dj_avatar'] = $request->file('dj_avatar')->store('radio/avatars', 'public');
        }

        $radio->update($validated);

        return back()->with('success', 'Radio station updated successfully!');
    }

    public function destroy(RadioStation $radio)
    {
        $radio->delete();

        return redirect()->route('admin.radio.index')->with('success', 'Radio station deleted successfully!');
    }

    public function addTrack(Request $request, RadioStation $radio)
    {
        $request->validate([
            'track_id' => 'required|exists:tracks,id',
        ]);

        $maxPosition = $radio->playlist()->max('position') ?? 0;

        $radio->playlist()->create([
            'track_id' => $request->track_id,
            'position' => $maxPosition + 1,
        ]);

        return back()->with('success', 'Track added to playlist!');
    }

    public function removeTrack(RadioStation $radio, $playlistId)
    {
        $radio->playlist()->where('id', $playlistId)->delete();

        return back()->with('success', 'Track removed from playlist!');
    }

    public function reorderPlaylist(Request $request, RadioStation $radio)
    {
        $request->validate([
            'order' => 'required|array',
        ]);

        foreach ($request->order as $position => $id) {
            $radio->playlist()->where('id', $id)->update(['position' => $position]);
        }

        return response()->json(['success' => true]);
    }

    public function analytics(RadioStation $radio)
    {
        $listeners = $radio->listeners()
            ->whereNotNull('disconnected_at')
            ->selectRaw('DATE(connected_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        $topTracks = $radio->playlist()
            ->with('track')
            ->orderBy('play_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.radio.analytics', compact('radio', 'listeners', 'topTracks'));
    }
}
