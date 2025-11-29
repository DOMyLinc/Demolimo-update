<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureAccess;
use Illuminate\Http\Request;

class FeatureAccessController extends Controller
{
    public function index()
    {
        $features = FeatureAccess::all()->groupBy('access_level');

        return view('admin.features.index', compact('features'));
    }

    public function update(Request $request, FeatureAccess $feature)
    {
        $validated = $request->validate([
            'access_level' => 'required|in:free,pro,admin',
            'is_beta' => 'boolean',
            'is_enabled' => 'boolean',
            'free_user_limit' => 'nullable|integer|min:0',
            'pro_user_limit' => 'nullable|integer|min:0',
        ]);

        $feature->update($validated);

        return back()->with('success', 'Feature settings updated successfully!');
    }

    public function toggleBeta(FeatureAccess $feature)
    {
        $feature->update(['is_beta' => !$feature->is_beta]);

        return back()->with('success', 'Beta status updated!');
    }

    public function toggleEnabled(FeatureAccess $feature)
    {
        $feature->update(['is_enabled' => !$feature->is_enabled]);

        return back()->with('success', 'Feature ' . ($feature->is_enabled ? 'enabled' : 'disabled') . '!');
    }
}
