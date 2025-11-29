<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialProvider;
use Illuminate\Http\Request;

class SocialProviderController extends Controller
{
    public function index()
    {
        $providers = SocialProvider::all();

        return view('admin.social.index', compact('providers'));
    }

    public function update(Request $request, SocialProvider $provider)
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'scopes' => 'nullable|array',
        ]);

        $provider->update($validated);

        return back()->with('success', ucfirst($provider->name) . ' settings updated successfully!');
    }

    public function toggle(SocialProvider $provider)
    {
        $provider->update(['enabled' => !$provider->enabled]);

        $status = $provider->enabled ? 'enabled' : 'disabled';
        return back()->with('success', ucfirst($provider->name) . ' has been ' . $status . '!');
    }

    public function test(Request $request, SocialProvider $provider)
    {
        if (!$provider->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Provider is not fully configured. Please add Client ID and Secret.',
            ]);
        }

        // Basic validation that credentials are present
        return response()->json([
            'success' => true,
            'message' => ucfirst($provider->name) . ' is configured. Test login to verify OAuth flow.',
            'redirect_url' => route('social.redirect', $provider->provider),
        ]);
    }
}
