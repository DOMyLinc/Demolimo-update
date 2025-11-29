<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrackTrial;
use App\Models\User;
use Illuminate\Http\Request;

class TrackTrialController extends Controller
{
    public function index()
    {
        $trials = TrackTrial::withCount('entries')->latest()->get();
        return view('admin.track-trials.index', compact('trials'));
    }

    public function create()
    {
        return view('admin.track-trials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,upcoming',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_featured' => 'boolean',
        ]);

        TrackTrial::create($validated);

        return redirect()->route('admin.track-trials.index')
            ->with('success', 'Track Trial created successfully!');
    }

    // User/Creator Management
    public function users()
    {
        $users = User::paginate(20);
        return view('admin.track-trials.users', compact('users'));
    }

    public function toggleCreator(User $user)
    {
        $user->is_creator = !$user->is_creator;
        // Set default title if becoming a creator
        if ($user->is_creator && !$user->creator_title) {
            $user->creator_title = 'Beta Creator';
        }
        $user->save();

        return back()->with('success', 'User creator status updated!');
    }

    public function updateCreatorTitle(Request $request, User $user)
    {
        $request->validate(['creator_title' => 'required|string|max:50']);
        $user->update(['creator_title' => $request->creator_title]);
        return back()->with('success', 'Creator title updated!');
    }
}
