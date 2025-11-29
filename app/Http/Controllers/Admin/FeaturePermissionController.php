<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturePermission;
use App\Models\UserFeatureAccess;
use App\Models\User;
use Illuminate\Http\Request;

class FeaturePermissionController extends Controller
{
    public function index()
    {
        $features = FeaturePermission::all();
        return view('admin.permissions.index', compact('features'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feature_name' => 'required|unique:feature_permissions',
            'display_name' => 'required',
            'description' => 'nullable',
            'free_plan' => 'boolean',
            'pro_plan' => 'boolean',
            'premium_plan' => 'boolean',
            'limits' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        FeaturePermission::create($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Feature permission created successfully');
    }

    public function edit(FeaturePermission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, FeaturePermission $permission)
    {
        $validated = $request->validate([
            'display_name' => 'required',
            'description' => 'nullable',
            'free_plan' => 'boolean',
            'pro_plan' => 'boolean',
            'premium_plan' => 'boolean',
            'limits' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $permission->update($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Feature permission updated successfully');
    }

    public function grantToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'feature_permission_id' => 'required|exists:feature_permissions,id',
            'custom_limits' => 'nullable|json',
        ]);

        UserFeatureAccess::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'feature_permission_id' => $request->feature_permission_id,
            ],
            [
                'is_granted' => true,
                'custom_limits' => $request->custom_limits ? json_decode($request->custom_limits, true) : null,
            ]
        );

        return back()->with('success', 'Feature access granted to user');
    }

    public function revokeFromUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'feature_permission_id' => 'required|exists:feature_permissions,id',
        ]);

        UserFeatureAccess::where([
            'user_id' => $request->user_id,
            'feature_permission_id' => $request->feature_permission_id,
        ])->delete();

        return back()->with('success', 'Feature access revoked from user');
    }
}
