<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Zipcode;
use App\Models\ZipcodeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZipcodeGroupController extends Controller
{
    public function create(Zipcode $zipcode)
    {
        return view('user.zipcodes.groups.create', compact('zipcode'));
    }

    public function store(Request $request, Zipcode $zipcode)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_private' => 'boolean',
        ]);

        $group = ZipcodeGroup::create([
            'zipcode_id' => $zipcode->id,
            'name' => $request->name,
            'description' => $request->description,
            'creator_id' => Auth::id(),
            'is_private' => $request->has('is_private'),
        ]);

        // Add creator as admin
        $group->members()->attach(Auth::id(), ['role' => 'admin']);

        return redirect()->route('zipcodes.groups.show', [$zipcode, $group])
            ->with('success', 'Group created successfully!');
    }

    public function show(Zipcode $zipcode, ZipcodeGroup $group)
    {
        $group->load(['members', 'creator']);
        return view('user.zipcodes.groups.show', compact('zipcode', 'group'));
    }

    public function join(Zipcode $zipcode, ZipcodeGroup $group)
    {
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            $group->members()->attach(Auth::id(), ['role' => 'member']);
        }
        return back()->with('success', 'Joined group successfully!');
    }

    public function leave(Zipcode $zipcode, ZipcodeGroup $group)
    {
        $group->members()->detach(Auth::id());
        return redirect()->route('zipcodes.show', $zipcode)->with('success', 'Left group successfully.');
    }
}
