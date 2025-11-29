<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function index()
    {
        $themes = Theme::all();
        $activeTheme = Theme::getActive();

        return view('admin.themes.index', compact('themes', 'activeTheme'));
    }

    public function activate(Theme $theme)
    {
        $theme->activate();

        return back()->with('success', ucfirst($theme->display_name) . ' theme activated successfully!');
    }

    public function setDefault(Theme $theme)
    {
        $theme->setAsDefault();

        return back()->with('success', ucfirst($theme->display_name) . ' set as default theme!');
    }

    public function preview(Theme $theme)
    {
        return view('admin.themes.preview', compact('theme'));
    }

    public function update(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color_scheme' => 'nullable|array',
        ]);

        $theme->update($validated);

        return back()->with('success', 'Theme updated successfully!');
    }
}
