<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    public function index()
    {
        // Ensure only Super Admin or Admins with 'manage_admins' permission can access
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('manage_admins')) {
            abort(403, 'Unauthorized action.');
        }

        $admins = User::where('role', 'admin')->get();
        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('manage_admins')) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('manage_admins')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'permissions' => 'nullable|array',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => \App\Helpers\UsernameHelper::generate($request->name),
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'permissions' => $request->permissions ?? [],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.admins.index')->with('success', 'Admin created successfully.');
    }

    public function edit(User $admin)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('manage_admins')) {
            abort(403, 'Unauthorized action.');
        }

        if ($admin->role !== 'admin') {
            abort(404);
        }

        return view('admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, User $admin)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('manage_admins')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent editing Super Admin permissions by others (though policy check above handles most)
        if ($admin->id === 1 && auth()->id() !== 1) {
            abort(403, 'Cannot edit Super Admin.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
            'permissions' => 'nullable|array',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'permissions' => $request->permissions ?? [],
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()->route('admin.admins.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(User $admin)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('manage_admins')) {
            abort(403, 'Unauthorized action.');
        }

        if ($admin->id === 1) {
            return back()->with('error', 'Cannot delete Super Admin.');
        }

        if ($admin->id === auth()->id()) {
            return back()->with('error', 'Cannot delete yourself.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', 'Admin deleted successfully.');
    }
}
