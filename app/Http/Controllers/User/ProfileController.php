<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $user->loadCount(['followers', 'following', 'tracks']);
        $tracks = $user->tracks()->latest()->where('is_public', true)->paginate(10);

        return view('user.profile.show', compact('user', 'tracks'));
    }

    public function edit()
    {
        return view('user.profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        return back()->with('success', 'Profile updated successfully!');
    }
}
