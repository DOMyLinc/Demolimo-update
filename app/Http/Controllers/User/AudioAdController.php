<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AudioAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AudioAdController extends Controller
{
    public function index()
    {
        $ads = AudioAd::where('user_id', Auth::id())->latest()->paginate(12);
        return view('user.ads.index', compact('ads'));
    }

    public function create()
    {
        return view('user.ads.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'audio_file' => 'required|file|mimes:mp3,wav|max:10240',
            'url' => 'nullable|url',
            'budget' => 'required|numeric|min:5',
        ]);

        $ad = new AudioAd($validated);
        $ad->user_id = Auth::id();
        $ad->status = 'pending';

        if ($request->hasFile('audio_file')) {
            $ad->audio_path = $request->file('audio_file')->store('ads', 'public');
        }

        $ad->save();

        return redirect()->route('user.ads.index')->with('success', 'Ad campaign created and pending approval.');
    }

    public function show(AudioAd $ad)
    {
        if ($ad->user_id !== Auth::id()) {
            abort(403);
        }
        return view('user.ads.show', compact('ad'));
    }
}
