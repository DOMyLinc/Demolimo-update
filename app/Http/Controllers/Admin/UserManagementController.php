<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with('subscription')->paginate(20);
        $stats = [
            'total_users' => User::count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'banned_users' => User::where('is_banned', true)->count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'artist_users' => User::where('role', 'artist')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function show(User $user)
    {
        $user->load(['tracks', 'subscription', 'points']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,artist,admin',
            'is_verified' => 'boolean',
            'is_banned' => 'boolean',
            'storage_limit' => 'required|integer|min:0',
            'max_uploads' => 'required|integer|min:0',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }

    public function ban(Request $request, User $user)
    {
        $request->validate([
            'days' => 'nullable|integer|min:1',
        ]);

        if ($request->days) {
            $user->update([
                'is_banned' => true,
                'banned_until' => \Carbon\Carbon::now()->addDays($request->days),
            ]);
            $message = "User banned for {$request->days} days.";
        } else {
            $user->update([
                'is_banned' => true,
                'banned_until' => null, // Indefinite
            ]);
            $message = "User banned indefinitely.";
        }

        return back()->with('success', $message);
    }

    public function unban(User $user)
    {
        $user->update(['is_banned' => false]);
        return back()->with('success', 'User unbanned successfully');
    }

    public function verify(User $user)
    {
        $user->update(['is_verified' => true]);
        return back()->with('success', 'User verified successfully');
    }

    public function updateLimit(Request $request, User $user)
    {
        $request->validate([
            'max_uploads' => 'required|integer|min:0',
        ]);

        $user->update(['max_uploads' => $request->max_uploads]);

        return back()->with('success', 'User upload limit updated.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,ban,verify',
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $count = count($ids);

        switch ($action) {
            case 'delete':
                User::whereIn('id', $ids)->delete();
                $message = "{$count} users deleted successfully.";
                break;

            case 'ban':
                User::whereIn('id', $ids)->update([
                    'is_banned' => true,
                    'banned_until' => null // Indefinite ban
                ]);
                $message = "{$count} users banned successfully.";
                break;

            case 'verify':
                User::whereIn('id', $ids)->update(['is_verified' => true]);
                $message = "{$count} users verified successfully.";
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Add fake followers to a user
     */
    public function addFollowers(Request $request, User $user)
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:1000',
        ]);

        $count = $request->count;
        $created = 0;

        // Names for fake users
        $firstNames = ['James', 'John', 'Robert', 'Michael', 'William', 'David', 'Richard', 'Joseph', 'Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth', 'Barbara', 'Susan', 'Jessica', 'Sarah', 'Karen', 'Nancy', 'Lisa', 'Margaret', 'Betty', 'Sandra', 'Ashley', 'Kimberly', 'Emily', 'Donna', 'Michelle', 'Carol', 'Amanda'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson'];

        for ($i = 0; $i < $count; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $name = $firstName . ' ' . $lastName;
            $username = strtolower($firstName . $lastName . rand(100, 9999));
            $email = $username . '@fake.com';

            if (User::where('email', $email)->exists()) {
                continue;
            }

            // Create fake user
            $follower = User::create([
                'name' => $name,
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'username' => $username,
                'role' => 'user',
                'is_verified' => true,
                'is_fake' => true, // Assuming you have this column or want to track it
                'avatar' => "https://ui-avatars.com/api/?name=" . urlencode($name) . "&size=200&background=random",
            ]);

            // Follow the target user
            // Assuming a 'followers' pivot table exists as per User model relationship
            if (!$user->followers()->where('follower_id', $follower->id)->exists()) {
                $user->followers()->attach($follower->id);
                $created++;
            }
        }

        return back()->with('success', "Added {$created} followers successfully.");
    }
}
