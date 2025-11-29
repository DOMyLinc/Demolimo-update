<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FanClub;
use App\Models\FanClubMembership;
use App\Models\ExclusiveContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FanClubController extends Controller
{
    public function index()
    {
        $fanClubs = FanClub::with('artist')
            ->where('is_active', true)
            ->withCount('members')
            ->latest()
            ->paginate(20);

        return view('user.fan-clubs.index', compact('fanClubs'));
    }

    public function show(FanClub $fanClub)
    {
        $fanClub->load(['artist', 'exclusiveContent']);

        $isMember = $fanClub->memberships()
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->exists();

        return view('user.fan-clubs.show', compact('fanClub', 'isMember'));
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user->isPro()) {
            return redirect()->route('user.subscription.plans')
                ->with('error', 'Fan clubs require a Pro subscription.');
        }

        if ($user->has_fan_club) {
            return back()->with('error', 'You already have a fan club.');
        }

        return view('user.fan-clubs.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isPro() || $user->has_fan_club) {
            return back()->with('error', 'Cannot create fan club.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'cover_image' => 'required|image|max:5120',
            'monthly_price' => 'required|numeric|min:1|max:999',
            'benefits' => 'required|array|min:1',
        ]);

        $coverPath = $request->file('cover_image')->store('fan-clubs/covers', 'public');

        $fanClub = FanClub::create([
            'artist_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'cover_image' => $coverPath,
            'monthly_price' => $validated['monthly_price'],
            'benefits' => $validated['benefits'],
        ]);

        $user->update(['has_fan_club' => true]);

        return redirect()->route('user.fan-clubs.show', $fanClub)
            ->with('success', 'Fan club created successfully!');
    }

    public function join(FanClub $fanClub)
    {
        $user = Auth::user();

        // Check if already a member
        if ($fanClub->memberships()->where('user_id', $user->id)->where('status', 'active')->exists()) {
            return back()->with('error', 'Already a member.');
        }

        // Create membership (would integrate with payment)
        FanClubMembership::create([
            'fan_club_id' => $fanClub->id,
            'user_id' => $user->id,
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        $fanClub->increment('members_count');

        return back()->with('success', 'Joined fan club successfully!');
    }

    public function leave(FanClub $fanClub)
    {
        $membership = $fanClub->memberships()
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return back()->with('error', 'Not a member.');
        }

        $membership->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $fanClub->decrement('members_count');

        return back()->with('success', 'Left fan club.');
    }
}
