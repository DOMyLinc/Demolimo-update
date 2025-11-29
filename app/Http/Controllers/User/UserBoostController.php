<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Boost;
use App\Models\BoostPackage;
use App\Models\FlashAlbum;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBoostController extends Controller
{
    /**
     * Display user's boosts
     */
    public function index()
    {
        $boosts = Auth::user()->boosts()
            ->with('boostable')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('user.boosts.index', compact('boosts'));
    }

    /**
     * Show boost purchase form
     */
    public function create(Request $request)
    {
        $packages = BoostPackage::active()->get();

        // Get boostable item (flash album or track)
        $boostableType = $request->input('type'); // 'flash_album' or 'track'
        $boostableId = $request->input('id');

        $boostable = null;
        if ($boostableType === 'flash_album' && $boostableId) {
            $boostable = FlashAlbum::where('user_id', Auth::id())->findOrFail($boostableId);
        } elseif ($boostableType === 'track' && $boostableId) {
            $boostable = Track::where('user_id', Auth::id())->findOrFail($boostableId);
        }

        // Get user's flash albums and tracks
        $flashAlbums = Auth::user()->flashAlbums;
        $tracks = Auth::user()->tracks;

        return view('user.boosts.create', compact('packages', 'boostable', 'flashAlbums', 'tracks'));
    }

    /**
     * Purchase a boost
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'boostable_type' => 'required|in:flash_album,track',
            'boostable_id' => 'required|integer',
            'package_id' => 'required|exists:boost_packages,id',
        ]);

        // Get the package
        $package = BoostPackage::findOrFail($validated['package_id']);

        // Get the boostable item
        if ($validated['boostable_type'] === 'flash_album') {
            $boostable = FlashAlbum::where('user_id', Auth::id())->findOrFail($validated['boostable_id']);
            $boostableType = FlashAlbum::class;
        } else {
            $boostable = Track::where('user_id', Auth::id())->findOrFail($validated['boostable_id']);
            $boostableType = Track::class;
        }

        // Check if already has active boost
        $existingBoost = Boost::where('boostable_id', $boostable->id)
            ->where('boostable_type', $boostableType)
            ->active()
            ->first();

        if ($existingBoost) {
            return back()->with('error', 'This item already has an active boost!');
        }

        // TODO: Integrate with payment gateway
        // For now, create boost directly

        $boost = Boost::create([
            'user_id' => Auth::id(),
            'boostable_id' => $boostable->id,
            'boostable_type' => $boostableType,
            'package' => $package->slug,
            'budget' => $package->price,
            'cost' => $package->price,
            'target_views' => $package->target_views,
            'target_impressions' => $package->target_impressions,
            'status' => 'pending', // Requires admin approval
            'is_active' => false,
            'starts_at' => now(),
            'ends_at' => now()->addDays($package->duration_days),
        ]);

        return redirect()->route('user.boosts.show', $boost)
            ->with('success', 'Boost purchased successfully! Waiting for admin approval.');
    }

    /**
     * Display boost details and analytics
     */
    public function show(Boost $boost)
    {
        // Ensure user owns this boost
        if ($boost->user_id !== Auth::id()) {
            abort(403);
        }

        $boost->load('boostable');

        return view('user.boosts.show', compact('boost'));
    }

    /**
     * Cancel a boost
     */
    public function cancel(Boost $boost)
    {
        // Ensure user owns this boost
        if ($boost->user_id !== Auth::id()) {
            abort(403);
        }

        // TODO: Implement refund logic based on remaining days

        $boost->update([
            'status' => 'cancelled',
            'is_active' => false,
        ]);

        return redirect()->route('user.boosts.index')
            ->with('success', 'Boost cancelled successfully!');
    }
}
