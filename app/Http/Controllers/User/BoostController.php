<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Boost;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoostController extends Controller
{
    public function index()
    {
        $ads = Advertisement::where('user_id', Auth::id())->latest()->get();
        $boosts = Boost::where('user_id', Auth::id())->with('track')->latest()->get();

        return view('user.boost.index', compact('ads', 'boosts'));
    }

    public function createAd()
    {
        return view('user.boost.create-ad');
    }

    public function storeAd(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048', // 2MB Max
            'target_url' => 'required|url',
            'budget' => 'required|numeric|min:5',
        ]);

        $imagePath = $request->file('image')->store('ads', 'public');

        Advertisement::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'image_path' => '/storage/' . $imagePath,
            'target_url' => $request->target_url,
            'budget' => $request->budget,
            'status' => 'pending',
        ]);

        return redirect()->route('user.boost.index')->with('success', 'Advertisement submitted for review!');
    }

    public function createBoost()
    {
        $tracks = Track::where('user_id', Auth::id())->get();
        return view('user.boost.create-boost', compact('tracks'));
    }

    public function storeBoost(Request $request)
    {
        $request->validate([
            'track_id' => 'required|exists:tracks,id',
            'budget' => 'required|numeric|min:5',
            'target_views' => 'required|integer|min:100',
        ]);

        // Verify track belongs to user
        $track = Track::where('id', $request->track_id)->where('user_id', Auth::id())->firstOrFail();

        Boost::create([
            'user_id' => Auth::id(),
            'track_id' => $track->id,
            'budget' => $request->budget,
            'target_views' => $request->target_views,
            'current_views' => 0,
            'status' => 'pending',
        ]);

        return redirect()->route('user.boost.index')->with('success', 'Boost campaign submitted for review!');
    }
}
