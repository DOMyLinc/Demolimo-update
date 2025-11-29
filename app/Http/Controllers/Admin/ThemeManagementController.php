<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ThemeManagementController extends Controller
{
    /**
     * Theme settings dashboard
     */
    public function index()
    {
        $settings = ThemeSetting::all()->groupBy('group');

        return view('admin.theme.index', compact('settings'));
    }

    /**
     * Update theme settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            ThemeSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Theme settings updated successfully!');
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        $validated = $request->validate([
            'logo' => 'required|image|mimes:png,jpg,svg|max:2048',
            'type' => 'required|in:main_logo,footer_logo,email_logo,admin_logo',
        ]);

        $file = $request->file('logo');
        $type = $validated['type'];

        // Delete old logo
        $oldLogo = ThemeSetting::where('key', $type)->first();
        if ($oldLogo && $oldLogo->value) {
            Storage::disk('public')->delete($oldLogo->value);
        }

        // Upload new logo
        $path = $file->store('branding/logos', 'public');

        // Update setting
        ThemeSetting::updateOrCreate(
            ['key' => $type],
            [
                'value' => $path,
                'type' => 'image',
                'group' => 'branding',
            ]
        );

        return back()->with('success', 'Logo uploaded successfully!');
    }

    /**
     * Upload favicon
     */
    public function uploadFavicon(Request $request)
    {
        $validated = $request->validate([
            'favicon' => 'required|image|mimes:png,ico|max:512',
        ]);

        $file = $request->file('favicon');

        // Delete old favicon
        $oldFavicon = ThemeSetting::where('key', 'favicon')->first();
        if ($oldFavicon && $oldFavicon->value) {
            Storage::disk('public')->delete($oldFavicon->value);
        }

        // Resize to 32x32
        $image = Image::make($file)->resize(32, 32);
        $filename = 'favicon-' . time() . '.png';
        $path = 'branding/favicon/' . $filename;

        Storage::disk('public')->put($path, (string) $image->encode('png'));

        ThemeSetting::updateOrCreate(
            ['key' => 'favicon'],
            [
                'value' => $path,
                'type' => 'image',
                'group' => 'branding',
            ]
        );

        return back()->with('success', 'Favicon uploaded successfully!');
    }

    /**
     * Upload PWA icons
     */
    public function uploadPwaIcons(Request $request)
    {
        $validated = $request->validate([
            'icon' => 'required|image|mimes:png|max:2048',
        ]);

        $file = $request->file('icon');

        // Generate multiple sizes for PWA
        $sizes = [72, 96, 128, 144, 152, 192, 384, 512];
        $icons = [];

        foreach ($sizes as $size) {
            $image = Image::make($file)->resize($size, $size);
            $filename = "icon-{$size}x{$size}.png";
            $path = "branding/pwa/{$filename}";

            Storage::disk('public')->put($path, (string) $image->encode('png'));

            $icons[$size] = $path;
        }

        // Store icon paths
        ThemeSetting::updateOrCreate(
            ['key' => 'pwa_icons'],
            [
                'value' => json_encode($icons),
                'type' => 'json',
                'group' => 'pwa',
            ]
        );

        return back()->with('success', 'PWA icons generated successfully!');
    }

    /**
     * Update colors
     */
    public function updateColors(Request $request)
    {
        $validated = $request->validate([
            'primary_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'accent_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'background_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'text_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
        ]);

        foreach ($validated as $key => $value) {
            ThemeSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => 'color',
                    'group' => 'colors',
                ]
            );
        }

        return back()->with('success', 'Colors updated successfully!');
    }

    /**
     * Update PWA settings
     */
    public function updatePwaSettings(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_short_name' => 'required|string|max:12',
            'app_description' => 'required|string|max:500',
            'theme_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'background_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'display' => 'required|in:standalone,fullscreen,minimal-ui,browser',
            'orientation' => 'required|in:any,portrait,landscape',
        ]);

        foreach ($validated as $key => $value) {
            ThemeSetting::updateOrCreate(
                ['key' => 'pwa_' . $key],
                [
                    'value' => $value,
                    'type' => 'text',
                    'group' => 'pwa',
                ]
            );
        }

        // Generate manifest.json
        $this->generateManifest();

        return back()->with('success', 'PWA settings updated successfully!');
    }

    /**
     * Generate PWA manifest.json
     */
    protected function generateManifest()
    {
        $icons = json_decode(ThemeSetting::where('key', 'pwa_icons')->value('value') ?? '[]', true);

        $manifest = [
            'name' => ThemeSetting::where('key', 'pwa_app_name')->value('value') ?? config('app.name'),
            'short_name' => ThemeSetting::where('key', 'pwa_app_short_name')->value('value') ?? config('app.name'),
            'description' => ThemeSetting::where('key', 'pwa_app_description')->value('value') ?? '',
            'start_url' => '/',
            'display' => ThemeSetting::where('key', 'pwa_display')->value('value') ?? 'standalone',
            'orientation' => ThemeSetting::where('key', 'pwa_orientation')->value('value') ?? 'any',
            'theme_color' => ThemeSetting::where('key', 'pwa_theme_color')->value('value') ?? '#000000',
            'background_color' => ThemeSetting::where('key', 'pwa_background_color')->value('value') ?? '#ffffff',
            'icons' => [],
        ];

        // Add icons
        foreach ($icons as $size => $path) {
            $manifest['icons'][] = [
                'src' => Storage::url($path),
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ];
        }

        // Save manifest.json
        Storage::disk('public')->put('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    }

    /**
     * Get theme setting value
     */
    public static function get($key, $default = null)
    {
        $setting = ThemeSetting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Preview theme
     */
    public function preview()
    {
        $settings = ThemeSetting::all()->pluck('value', 'key');
        return view('admin.theme.preview', compact('settings'));
    }

    /**
     * Reset to default
     */
    public function reset()
    {
        // Delete all custom logos and icons
        $imageSettings = ThemeSetting::where('type', 'image')->get();
        foreach ($imageSettings as $setting) {
            if ($setting->value) {
                Storage::disk('public')->delete($setting->value);
            }
        }

        // Reset to defaults
        ThemeSetting::truncate();
        $this->seedDefaults();

        return back()->with('success', 'Theme reset to defaults!');
    }

    /**
     * Seed default settings
     */
    protected function seedDefaults()
    {
        $defaults = [
            // General
            ['key' => 'site_name', 'value' => config('app.name'), 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Your Music, Your Way', 'type' => 'text', 'group' => 'general'],

            // Colors
            ['key' => 'primary_color', 'value' => '#3B82F6', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_color', 'value' => '#8B5CF6', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'accent_color', 'value' => '#F59E0B', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'background_color', 'value' => '#FFFFFF', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'text_color', 'value' => '#1F2937', 'type' => 'color', 'group' => 'colors'],

            // PWA
            ['key' => 'pwa_app_name', 'value' => config('app.name'), 'type' => 'text', 'group' => 'pwa'],
            ['key' => 'pwa_app_short_name', 'value' => 'Music', 'type' => 'text', 'group' => 'pwa'],
            ['key' => 'pwa_theme_color', 'value' => '#3B82F6', 'type' => 'color', 'group' => 'pwa'],
            ['key' => 'pwa_background_color', 'value' => '#FFFFFF', 'type' => 'color', 'group' => 'pwa'],
            ['key' => 'pwa_display', 'value' => 'standalone', 'type' => 'text', 'group' => 'pwa'],
        ];

        foreach ($defaults as $default) {
            ThemeSetting::create($default);
        }
    }
}
