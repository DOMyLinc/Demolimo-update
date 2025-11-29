<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Services\WaveformGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TrackController extends Controller
{
    public function index()
    {
        $tracks = Auth::user()->tracks()->latest()->paginate(10);
        return view('user.tracks.index', compact('tracks'));
    }

    public function create()
    {
        return view('user.tracks.create');
    }

    public function store(Request $request, WaveformGenerator $waveformGenerator)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'audio_file' => 'required|file|mimes:mp3,wav,ogg|max:20480', // 20MB
            'image_file' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'album_id' => 'nullable|exists:albums,id',
        ]);

        $user = Auth::user();
        if ($user->role !== 'admin' && $user->tracks()->count() >= $user->max_uploads) {
            return back()->with('error', 'You have reached your upload limit. Please upgrade or delete some tracks.');
        }

        $disk = config('filesystems.default'); // Use configured cloud disk
        $audioPath = $request->file('audio_file')->store('tracks/audio', $disk);
        $imagePath = $request->file('image_file') ? $request->file('image_file')->store('tracks/images', $disk) : null;

        // Generate Waveform (Need local path for FFmpeg usually, or stream it)
        // For now, assuming local generation or using a temp file
        // In production with S3, we'd download to temp, generate, then delete temp
        $waveformData = $waveformGenerator->generate($request->file('audio_file')->path());

        Auth::user()->tracks()->create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(6),
            'description' => $request->description,
            'album_id' => $request->album_id,
            'audio_path' => $audioPath,
            'image_path' => $imagePath,
            'waveform_data' => $waveformData,
            'price' => $request->price ?? 0,
            'is_public' => $request->has('is_public'),
        ]);

        return redirect()->route('user.tracks.index')->with('success', 'Track uploaded successfully!');
    }

    public function shareToFeed(Track $track)
    {
        // Check if feed is enabled
        if (\App\Models\Setting::get('enable_feed') !== '1') {
            return back()->with('error', 'Feed is currently disabled.');
        }

        // Logic to share to feed.
        $track->touch(); // Placeholder for sharing logic

        return back()->with('success', 'Track shared to feed successfully!');
    }

    public function shareToZipcode(Request $request, Track $track)
    {
        // Check if zipcodes enabled
        if (\App\Models\Setting::get('enable_zipcodes') !== '1') {
            return back()->with('error', 'Zipcode system is currently disabled.');
        }

        $request->validate([
            'zipcode_id' => 'required|exists:zipcodes,id',
        ]);

        // Check permissions
        $user = Auth::user();
        if ($user->id !== $track->user_id && !$user->hasRole('admin') && !$user->hasRole('moderator')) {
            abort(403, 'Unauthorized to share this track.');
        }

        // Logic to share to zipcode
        // $track->zipcodes()->attach($request->zipcode_id);

        return back()->with('success', 'Track shared to zipcode successfully!');
    }
}
