<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RadioStation;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MyRadioController extends Controller
{
    public function index()
    {
        $stations = auth()->user()->radioStations()->latest()->paginate(12);

        return view('user.my-radio.index', compact('stations'));
    }

    public function create()
    {
        $genres = ['Pop', 'Rock', 'Hip-Hop', 'Electronic', 'Jazz', 'Classical', 'Country', 'R&B'];
        $moods = ['Energetic', 'Chill', 'Focus', 'Party', 'Romantic', 'Workout'];

        return view('user.my-radio.create', compact('genres', 'moods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:auto,live',
            'genre' => 'nullable|string',
            'mood' => 'nullable|string',
            'cover_image' => 'nullable|image|max:5120', // 5MB
            'stream_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'social_links' => 'nullable|array',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_user_created'] = true;
        $validated['is_active'] = false; // Pending approval
        $validated['dj_name'] = auth()->user()->name;

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('radio/covers', 'public');
        }

        $station = RadioStation::create($validated);

        // Generate embed code
        $station->update(['embed_code' => $station->generateEmbedCode()]);

        return redirect()->route('my-radio.edit', $station)
            ->with('success', 'Radio station created! It will be active after admin approval.');
    }

    public function edit(RadioStation $myRadio)
    {
        // Ensure user owns this station
        if ($myRadio->user_id !== auth()->id()) {
            abort(403);
        }

        $genres = ['Pop', 'Rock', 'Hip-Hop', 'Electronic', 'Jazz', 'Classical', 'Country', 'R&B'];
        $moods = ['Energetic', 'Chill', 'Focus', 'Party', 'Romantic', 'Workout'];
        $tracks = auth()->user()->tracks()->where('is_approved', true)->latest()->get();

        $station = $myRadio->load('playlist.track');

        return view('user.my-radio.edit', compact('station', 'genres', 'moods', 'tracks'));
    }

    public function update(Request $request, RadioStation $myRadio)
    {
        // Ensure user owns this station
        if ($myRadio->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:auto,live',
            'genre' => 'nullable|string',
            'mood' => 'nullable|string',
            'cover_image' => 'nullable|image|max:5120',
            'stream_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'social_links' => 'nullable|array',
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('radio/covers', 'public');
        }

        $myRadio->update($validated);

        return back()->with('success', 'Radio station updated successfully!');
    }

    public function destroy(RadioStation $myRadio)
    {
        // Ensure user owns this station
        if ($myRadio->user_id !== auth()->id()) {
            abort(403);
        }

        $myRadio->delete();

        return redirect()->route('my-radio.index')->with('success', 'Radio station deleted successfully!');
    }

    public function addTrack(Request $request, RadioStation $myRadio)
    {
        // Ensure user owns this station
        if ($myRadio->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'track_id' => 'required|exists:tracks,id',
        ]);

        // Ensure user owns the track
        $track = Track::findOrFail($request->track_id);
        if ($track->user_id !== auth()->id()) {
            abort(403, 'You can only add your own tracks.');
        }

        $maxPosition = $myRadio->playlist()->max('position') ?? 0;

        $myRadio->playlist()->create([
            'track_id' => $request->track_id,
            'position' => $maxPosition + 1,
        ]);

        return back()->with('success', 'Track added to playlist!');
    }

    public function removeTrack(RadioStation $myRadio, $playlistId)
    {
        // Ensure user owns this station
        if ($myRadio->user_id !== auth()->id()) {
            abort(403);
        }

        $myRadio->playlist()->where('id', $playlistId)->delete();

        return back()->with('success', 'Track removed from playlist!');
    }
}
