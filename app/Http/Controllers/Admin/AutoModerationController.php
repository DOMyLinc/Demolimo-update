<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModerationRule;
use Illuminate\Http\Request;

class AutoModerationController extends Controller
{
    public function index()
    {
        $rules = ModerationRule::orderBy('priority', 'desc')->paginate(20);
        return view('admin.moderation.rules', compact('rules'));
    }

    public function create()
    {
        return view('admin.moderation.rule-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content_type' => 'required|string',
            'conditions' => 'required|json',
            'action' => 'required|string',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ]);

        $validated['conditions'] = json_decode($validated['conditions'], true);

        ModerationRule::create($validated);

        return redirect()->route('admin.moderation.rules.index')->with('success', 'Rule created successfully.');
    }

    public function edit(ModerationRule $rule)
    {
        return view('admin.moderation.rule-edit', compact('rule'));
    }

    public function update(Request $request, ModerationRule $rule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content_type' => 'required|string',
            'conditions' => 'required|json',
            'action' => 'required|string',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ]);

        $validated['conditions'] = json_decode($validated['conditions'], true);

        $rule->update($validated);

        return redirect()->route('admin.moderation.rules.index')->with('success', 'Rule updated successfully.');
    }

    public function destroy(ModerationRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Rule deleted successfully.');
    }
}
