<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting; // Assuming you have a Setting model, if not I'll create one or use a config file approach
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'site_title' => config('app.name'),
            'footer_text' => Setting::get('footer_text', '© 2024 DemoLimo. All rights reserved.'),
            'copyright_text' => Setting::get('copyright_text', 'Made with ❤️ by DemoLimo'),
            'facebook_url' => Setting::get('facebook_url'),
            'twitter_url' => Setting::get('twitter_url'),
            'instagram_url' => Setting::get('instagram_url'),
            'youtube_url' => Setting::get('youtube_url'),
            'meta_description' => Setting::get('meta_description'),
            'meta_keywords' => Setting::get('meta_keywords'),
        ];

        return view('admin.settings.general', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_title' => 'required|string|max:50',
            'footer_text' => 'nullable|string|max:255',
            'copyright_text' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        // Update .env for site title
        $this->updateEnv(['APP_NAME' => '"' . $validated['site_title'] . '"']);

        // Update database settings
        foreach ($validated as $key => $value) {
            if ($key !== 'site_title') {
                Setting::set($key, $value);
            }
        }

        Cache::forget('settings');

        return back()->with('success', 'General settings updated successfully!');
    }

    protected function updateEnv($data)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            foreach ($data as $key => $value) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            }
            file_put_contents($path, $content);
        }
    }
}
