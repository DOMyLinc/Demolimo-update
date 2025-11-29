<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ZipcodeOwner;
use App\Models\ZipcodeMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZipcodeManagementController extends Controller
{
    /**
     * Zipcode overview
     */
    public function index()
    {
        $zipcodes = ZipcodeOwner::with('owner', 'members')
            ->latest()
            ->paginate(50);

        $stats = [
            'total_zipcodes' => ZipcodeOwner::count(),
            'active_zipcodes' => ZipcodeOwner::where('is_active', true)->count(),
            'verified_zipcodes' => ZipcodeOwner::where('is_verified', true)->count(),
            'total_members' => ZipcodeMember::count(),
        ];

        return view('admin.zipcode.index', compact('zipcodes', 'stats'));
    }

    /**
     * View zipcode details
     */
    public function show(ZipcodeOwner $zipcode)
    {
        $zipcode->load(['owner', 'members.user', 'posts', 'events', 'settings']);

        $stats = [
            'total_members' => $zipcode->members()->count(),
            'total_posts' => $zipcode->posts()->count(),
            'total_events' => $zipcode->events()->count(),
        ];

        return view('admin.zipcode.show', compact('zipcode', 'stats'));
    }

    /**
     * Verify zipcode
     */
    public function verify(ZipcodeOwner $zipcode)
    {
        // Verify zipcode exists
        $isValid = $this->verifyZipcodeExists($zipcode->zipcode, $zipcode->country_code);

        if ($isValid) {
            $zipcode->update(['is_verified' => true]);
            return back()->with('success', 'Zipcode verified successfully!');
        }

        return back()->with('error', 'Invalid zipcode!');
    }

    /**
     * Transfer ownership
     */
    public function transferOwnership(Request $request, ZipcodeOwner $zipcode)
    {
        $validated = $request->validate([
            'new_owner_id' => 'required|exists:users,id',
        ]);

        $newOwner = User::find($validated['new_owner_id']);

        // Check if new owner is Pro
        if (!$newOwner->is_pro) {
            return back()->with('error', 'New owner must be a Pro member!');
        }

        // Check if new owner already owns a zipcode
        if (ZipcodeOwner::where('owner_id', $newOwner->id)->exists()) {
            return back()->with('error', 'User already owns a zipcode!');
        }

        $zipcode->update(['owner_id' => $newOwner->id]);

        return back()->with('success', "Ownership transferred to {$newOwner->name}!");
    }

    /**
     * Revoke zipcode
     */
    public function revoke(ZipcodeOwner $zipcode)
    {
        $zipcode->delete();
        return redirect()->route('admin.zipcode.index')
            ->with('success', 'Zipcode revoked successfully!');
    }

    /**
     * Activate/Deactivate zipcode
     */
    public function toggleActive(ZipcodeOwner $zipcode)
    {
        $zipcode->update(['is_active' => !$zipcode->is_active]);

        $status = $zipcode->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Zipcode {$status}!");
    }

    /**
     * Remove member
     */
    public function removeMember(ZipcodeMember $member)
    {
        $member->delete();
        return back()->with('success', 'Member removed successfully!');
    }

    /**
     * Ban user from zipcode
     */
    public function banUser(Request $request, ZipcodeOwner $zipcode)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Remove member
        ZipcodeMember::where('zipcode_owner_id', $zipcode->id)
            ->where('user_id', $validated['user_id'])
            ->delete();

        // Add to ban list (you can create a separate table for this)

        return back()->with('success', 'User banned from zipcode!');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,verify,delete',
            'zipcode_ids' => 'required|array',
            'zipcode_ids.*' => 'exists:zipcode_owners,id',
        ]);

        $zipcodes = ZipcodeOwner::whereIn('id', $validated['zipcode_ids']);

        switch ($validated['action']) {
            case 'activate':
                $zipcodes->update(['is_active' => true]);
                $message = 'Zipcodes activated!';
                break;
            case 'deactivate':
                $zipcodes->update(['is_active' => false]);
                $message = 'Zipcodes deactivated!';
                break;
            case 'verify':
                $zipcodes->update(['is_verified' => true]);
                $message = 'Zipcodes verified!';
                break;
            case 'delete':
                $zipcodes->delete();
                $message = 'Zipcodes deleted!';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Zipcode analytics
     */
    public function analytics()
    {
        $topZipcodes = ZipcodeOwner::withCount('members')
            ->orderByDesc('members_count')
            ->limit(10)
            ->get();

        $zipcodesByCountry = ZipcodeOwner::selectRaw('country_code, COUNT(*) as count')
            ->groupBy('country_code')
            ->get();

        $recentClaims = ZipcodeOwner::latest('claimed_at')
            ->limit(20)
            ->get();

        return view('admin.zipcode.analytics', compact('topZipcodes', 'zipcodesByCountry', 'recentClaims'));
    }

    /**
     * Verify zipcode exists (using external API)
     */
    protected function verifyZipcodeExists($zipcode, $countryCode)
    {
        try {
            // Use Zippopotam.us API (free)
            $response = Http::get("http://api.zippopotam.us/{$countryCode}/{$zipcode}");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get zipcode info
     */
    public function getZipcodeInfo(Request $request)
    {
        $validated = $request->validate([
            'zipcode' => 'required|string',
            'country_code' => 'required|string|size:2',
        ]);

        try {
            $response = Http::get("http://api.zippopotam.us/{$validated['country_code']}/{$validated['zipcode']}");

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'city' => $data['places'][0]['place name'] ?? null,
                        'state' => $data['places'][0]['state'] ?? null,
                        'country' => $data['country'] ?? null,
                        'latitude' => $data['places'][0]['latitude'] ?? null,
                        'longitude' => $data['places'][0]['longitude'] ?? null,
                    ],
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Zipcode not found']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error verifying zipcode']);
        }
    }
}
