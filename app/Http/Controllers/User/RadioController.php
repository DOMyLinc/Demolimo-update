<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RadioStation;
use App\Models\RadioListener;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RadioController extends Controller
{
    public function index()
    {
        $featured = RadioStation::where('is_featured', true)
            ->where('is_active', true)
            ->withCount('listeners')
            ->get();

        $stations = RadioStation::where('is_active', true)
            ->withCount('listeners')
            ->latest()
            ->paginate(12);

        return view('user.radio.index', compact('featured', 'stations'));
    }

    public function show($slug)
    {
        $station = RadioStation::where('slug', $slug)
            ->where('is_active', true)
            ->with(['playlist.track', 'schedules'])
            ->firstOrFail();

        // Get current track for auto-DJ stations
        $currentTrack = $station->type === 'auto' ? $station->getCurrentTrack() : null;

        // Get active listeners count
        $activeListeners = $station->getActiveListeners();

        return view('user.radio.show', compact('station', 'currentTrack', 'activeListeners'));
    }

    public function listen(Request $request, $slug)
    {
        $station = RadioStation::where('slug', $slug)->firstOrFail();

        // Create listener session
        $sessionId = Str::uuid()->toString();

        RadioListener::create([
            'radio_station_id' => $station->id,
            'user_id' => auth()->id(),
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'connected_at' => now(),
        ]);

        // Increment listeners count
        $station->incrementListeners();
        $station->increment('total_plays');

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'stream_url' => $station->stream_url,
            'current_track' => $station->getCurrentTrack(),
        ]);
    }

    public function disconnect(Request $request, $slug)
    {
        $station = RadioStation::where('slug', $slug)->firstOrFail();

        RadioListener::where('session_id', $request->session_id)
            ->whereNull('disconnected_at')
            ->update(['disconnected_at' => now()]);

        // Decrement listeners count
        $station->decrementListeners();

        return response()->json(['success' => true]);
    }

    public function getCurrentTrack($slug)
    {
        $station = RadioStation::where('slug', $slug)->firstOrFail();

        if ($station->type !== 'auto') {
            return response()->json(['track' => null]);
        }

        $track = $station->getCurrentTrack();

        return response()->json([
            'track' => $track ? [
                'id' => $track->id,
                'title' => $track->title,
                'artist' => $track->artist->name ?? 'Unknown',
                'cover' => $track->cover_image,
                'duration' => $track->duration,
                'audio_url' => $track->audio_file,
            ] : null,
        ]);
    }

    public function embed($slug)
    {
        $station = RadioStation::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('user.radio.embed', compact('station'));
    }
}
