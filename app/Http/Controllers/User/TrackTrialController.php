<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TrackTrial;
use App\Models\TrackTrialEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackTrialController extends Controller
{
    public function index()
    {
        // Landing Page Data
        $activeTrials = TrackTrial::where('status', 'active')
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $bestTracks = TrackTrialEntry::with(['creator', 'trial'])
            ->orderBy('votes', 'desc')
            ->orderBy('plays', 'desc')
            ->take(6)
            ->get();

        return view('user.track-trials.index', compact('activeTrials', 'bestTracks'));
    }

    public function show(TrackTrial $trial)
    {
        $trial->load(['entries.creator']);
        return view('user.track-trials.show', compact('trial'));
    }

    public function upload(TrackTrial $trial)
    {
        // Check if user is a creator
        if (!Auth::user()->is_creator) {
            return redirect()->route('track-trials.index')
                ->with('error', 'Only Creators can upload to Track Trials.');
        }

        return view('user.track-trials.upload', compact('trial'));
    }

    public function store(Request $request, TrackTrial $trial)
    {
        if (!Auth::user()->is_creator) {
            abort(403, 'Only Creators can upload.');
        }

        $request->validate([
            'track_title' => 'required|string|max:255',
            'audio_file' => 'required|file|mimes:mp3,wav,flac|max:51200', // 50MB
            'cover_image' => 'nullable|image|max:5120', // 5MB
        ]);

        // Handle File Uploads (Using our SecureFileUpload service ideally, but simplifying for now)
        $audioPath = $request->file('audio_file')->store('track-trials/audio', 'public');
        $coverPath = $request->file('cover_image') ? $request->file('cover_image')->store('track-trials/covers', 'public') : null;

        TrackTrialEntry::create([
            'track_trial_id' => $trial->id,
            'user_id' => Auth::id(),
            'track_title' => $request->track_title,
            'audio_path' => $audioPath,
            'cover_image' => $coverPath,
        ]);

        return redirect()->route('track-trials.show', $trial)
            ->with('success', 'Track uploaded successfully!');
    }
}
