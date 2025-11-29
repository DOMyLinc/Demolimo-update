<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Boost;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    public function index()
    {
        $ads = Advertisement::with('user')->latest()->paginate(10);
        $boosts = Boost::with(['user', 'track'])->latest()->paginate(10);

        return view('admin.ads.index', compact('ads', 'boosts'));
    }

    public function approveAd(Advertisement $ad)
    {
        $ad->update(['status' => 'active']);
        return back()->with('success', 'Ad approved successfully!');
    }

    public function rejectAd(Advertisement $ad)
    {
        $ad->update(['status' => 'rejected']);
        return back()->with('success', 'Ad rejected.');
    }

    public function approveBoost(Boost $boost)
    {
        $boost->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(7) // Default 7 days
        ]);
        return back()->with('success', 'Boost approved successfully!');
    }
}
