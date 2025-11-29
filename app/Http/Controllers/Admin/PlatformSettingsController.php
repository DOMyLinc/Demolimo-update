<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    public function index()
    {
        $settings = PlatformSetting::orderBy('category')->orderBy('key')->get()->groupBy('category');

        return view('admin.settings.platform', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token', '_method') as $key => $value) {
            $setting = PlatformSetting::where('key', $key)->first();

            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        return back()->with('success', 'Platform settings updated successfully');
    }

    public function create()
    {
        return view('admin.settings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required',
            'key' => 'required|unique:platform_settings',
            'value' => 'nullable',
            'type' => 'required|in:text,boolean,number,json',
            'description' => 'nullable',
            'is_public' => 'boolean',
        ]);

        PlatformSetting::create($validated);

        return redirect()->route('admin.settings.platform')
            ->with('success', 'Setting created successfully');
    }

    public function destroy(PlatformSetting $setting)
    {
        $setting->delete();
        return back()->with('success', 'Setting deleted successfully');
    }

    // Quick settings methods
    public function toggleMaintenance()
    {
        $current = PlatformSetting::get('maintenance_mode', false);
        PlatformSetting::set('maintenance_mode', !$current, 'boolean');

        return back()->with('success', 'Maintenance mode ' . (!$current ? 'enabled' : 'disabled'));
    }

    public function toggleRegistration()
    {
        $current = PlatformSetting::get('allow_registration', true);
        PlatformSetting::set('allow_registration', !$current, 'boolean');

        return back()->with('success', 'Registration ' . (!$current ? 'enabled' : 'disabled'));
    }
}
