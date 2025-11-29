<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ZipcodeOwner;
use App\Models\ZipcodeMember;
use App\Models\ZipcodePost;
use App\Models\ZipcodeEvent;
use App\Models\ZipcodeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ZipcodeController extends Controller
{
    /**
     * Claim zipcode (Pro users only)
     */
    public function claim(Request $request)
    {
        $user = Auth::user();

        // Check if user is Pro
        if (!$user->is_pro) {
            return back()->with('error', 'Only Pro members can claim zipcodes!');
        }

        // Check if user already owns a zipcode
        if (ZipcodeOwner::where('owner_id', $user->id)->exists()) {
            return back()->with('error', 'You already own a zipcode!');
        }

        $validated = $request->validate([
            'zipcode' => 'required|string|max:10',
            'country_code' => 'required|string|size:2',
        ]);

        // Check if zipcode is already claimed
        $existing = ZipcodeOwner::where('zipcode', $validated['zipcode'])
            ->where('country_code', $validated['country_code'])
            ->first();

        if ($existing) {
            return back()->with('error', 'This zipcode is already claimed!');
        }

        // Verify zipcode exists
        $zipcodeInfo = $this->getZipcodeInfo($validated['zipcode'], $validated['country_code']);

        if (!$zipcodeInfo) {
            return back()->with('error', 'Invalid zipcode!');
        }

        // Create zipcode ownership
        $zipcode = ZipcodeOwner::create([
            'zipcode' => $validated['zipcode'],
            'country_code' => $validated['country_code'],
            'owner_id' => $user->id,
            'city' => $zipcodeInfo['city'],
            'state' => $zipcodeInfo['state'],
            'country' => $zipcodeInfo['country'],
            'latitude' => $zipcodeInfo['latitude'],
            'longitude' => $zipcodeInfo['longitude'],
            'is_verified' => true,
            'claimed_at' => now(),
        ]);

        // Create default settings
        ZipcodeSetting::create([
            'zipcode_owner_id' => $zipcode->id,
        ]);

        return redirect()->route('user.zipcode.dashboard')
            ->with('success', 'Zipcode claimed successfully!');
    }

    /**
     * Zipcode owner dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $zipcode = ZipcodeOwner::where('owner_id', $user->id)->first();

        if (!$zipcode) {
            return view('user.zipcode.claim');
        }

        $zipcode->load(['members', 'posts', 'events', 'settings']);

        $stats = [
            'total_members' => $zipcode->members()->count(),
            'total_posts' => $zipcode->posts()->count(),
            'total_events' => $zipcode->events()->count(),
            'new_members_week' => $zipcode->members()->where('joined_at', '>=', now()->subWeek())->count(),
        ];

        return view('user.zipcode.dashboard', compact('zipcode', 'stats'));
    }

    /**
     * Join zipcode (any user)
     */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'zipcode' => 'required|string',
            'country_code' => 'required|string|size:2',
        ]);

        $zipcode = ZipcodeOwner::where('zipcode', $validated['zipcode'])
            ->where('country_code', $validated['country_code'])
            ->where('is_active', true)
            ->firstOrFail();

        $user = Auth::user();

        // Check if already a member
        if (
            ZipcodeMember::where('zipcode_owner_id', $zipcode->id)
                ->where('user_id', $user->id)
                ->exists()
        ) {
            return back()->with('error', 'You are already a member!');
        }

        // Check if requires approval
        $requiresApproval = $zipcode->settings->require_approval ?? false;

        ZipcodeMember::create([
            'zipcode_owner_id' => $zipcode->id,
            'user_id' => $user->id,
            'is_approved' => !$requiresApproval,
            'joined_at' => now(),
        ]);

        $message = $requiresApproval
            ? 'Join request sent! Waiting for approval.'
            : 'Successfully joined zipcode!';

        return back()->with('success', $message);
    }

    /**
     * Leave zipcode
     */
    public function leave(ZipcodeOwner $zipcode)
    {
        ZipcodeMember::where('zipcode_owner_id', $zipcode->id)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', 'Left zipcode successfully!');
    }

    /**
     * View zipcode feed
     */
    public function feed(ZipcodeOwner $zipcode)
    {
        // Check if user is a member
        $isMember = ZipcodeMember::where('zipcode_owner_id', $zipcode->id)
            ->where('user_id', Auth::id())
            ->where('is_approved', true)
            ->exists();

        if (!$isMember && $zipcode->owner_id !== Auth::id()) {
            return redirect()->route('user.zipcode.view', $zipcode)
                ->with('error', 'You must be a member to view the feed!');
        }

        $posts = ZipcodePost::where('zipcode_owner_id', $zipcode->id)
            ->with(['user', 'track'])
            ->latest()
            ->paginate(20);

        return view('user.zipcode.feed', compact('zipcode', 'posts'));
    }

    /**
     * Create post
     */
    public function createPost(Request $request, ZipcodeOwner $zipcode)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'track_id' => 'nullable|exists:tracks,id',
            'media' => 'nullable|array',
        ]);

        ZipcodePost::create([
            'zipcode_owner_id' => $zipcode->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'track_id' => $validated['track_id'] ?? null,
            'media' => $validated['media'] ?? null,
        ]);

        return back()->with('success', 'Post created!');
    }

    /**
     * Update zipcode settings (owner only)
     */
    public function updateSettings(Request $request, ZipcodeOwner $zipcode)
    {
        if ($zipcode->owner_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'allow_posts' => 'boolean',
            'require_approval' => 'boolean',
            'allow_events' => 'boolean',
            'theme_color' => 'string',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('zipcode/covers', 'public');
            $validated['cover_image'] = $path;
        }

        $zipcode->settings()->update($validated);

        return back()->with('success', 'Settings updated!');
    }

    /**
     * Get zipcode info from API
     */
    protected function getZipcodeInfo($zipcode, $countryCode)
    {
        try {
            $response = Http::get("http://api.zippopotam.us/{$countryCode}/{$zipcode}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'city' => $data['places'][0]['place name'] ?? null,
                    'state' => $data['places'][0]['state'] ?? null,
                    'country' => $data['country'] ?? null,
                    'latitude' => $data['places'][0]['latitude'] ?? null,
                    'longitude' => $data['places'][0]['longitude'] ?? null,
                ];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
