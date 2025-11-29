<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ThemeSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'theme_primary_color' => Setting::get('theme_primary_color', '#ff3333'),
            'theme_secondary_color' => Setting::get('theme_secondary_color', '#ff6b00'),
            'theme_background_color' => Setting::get('theme_background_color', '#0f0f0f'),
            'theme_panel_background_color' => Setting::get('theme_panel_background_color', '#1a1a1a'),
            'theme_text_main_color' => Setting::get('theme_text_main_color', '#ffffff'),
            'theme_text_muted_color' => Setting::get('theme_text_muted_color', '#888888'),
            'theme_glass_opacity' => Setting::get('theme_glass_opacity', '0.6'),
            'theme_enable_lava' => Setting::get('theme_enable_lava', true),
        ];

        return view('admin.settings.theme', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme_primary_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'theme_secondary_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'theme_background_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'theme_panel_background_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'theme_text_main_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'theme_text_muted_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'theme_glass_opacity' => 'required|numeric|min:0|max:1',
            'theme_enable_lava' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        // Clear cache to ensure new styles are loaded
        Cache::forget('settings');
        Cache::forget('dynamic_css');

        return back()->with('success', 'Theme settings updated successfully!');
    }

    public function dynamicCss()
    {
        return Cache::rememberForever('dynamic_css', function () {
            $primary = Setting::get('theme_primary_color', '#ff3333');
            $secondary = Setting::get('theme_secondary_color', '#ff6b00');
            $bg = Setting::get('theme_background_color', '#0f0f0f');
            $panel = Setting::get('theme_panel_background_color', '#1a1a1a');
            $text = Setting::get('theme_text_main_color', '#ffffff');
            $muted = Setting::get('theme_text_muted_color', '#888888');
            $glass = Setting::get('theme_glass_opacity', '0.6');

            $css = "
:root {
    --primary: {$primary};
    --secondary: {$secondary};
    --bg-dark: {$bg};
    --bg-panel: {$panel};
    --text-main: {$text};
    --text-muted: {$muted};
    --glass-bg: rgba(30, 30, 30, {$glass});
    --glass-border: rgba(255, 255, 255, 0.05);
}

body {
    background-color: var(--bg-dark);
    color: var(--text-main);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
}

.text-primary {
    color: var(--primary);
}
";
            return response($css)->header('Content-Type', 'text/css');
        });
    }
}
