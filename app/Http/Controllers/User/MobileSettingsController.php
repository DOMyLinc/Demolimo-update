<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MobileSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileSettingsController extends Controller
{
    /**
     * Mobile settings page
     */
    public function index()
    {
        $settings = Auth::user()->mobileSettings
            ?? MobileSetting::create(['user_id' => Auth::id()]);

        return view('user.settings.mobile', compact('settings'));
    }

    /**
     * Update mobile settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'car_mode_enabled' => 'boolean',
            'sleep_timer_minutes' => 'nullable|integer|min:5|max:180',
            'data_saver_mode' => 'boolean',
            'audio_quality_mobile' => 'required|in:low,normal,high',
            'download_over_wifi_only' => 'boolean',
            'shake_to_skip' => 'boolean',
            'shake_sensitivity' => 'integer|min:1|max:10',
        ]);

        $settings = Auth::user()->mobileSettings
            ?? MobileSetting::create(['user_id' => Auth::id()]);

        $settings->update($validated);

        return back()->with('success', 'Mobile settings updated!');
    }

    /**
     * Enable car mode
     */
    public function enableCarMode()
    {
        $settings = Auth::user()->mobileSettings
            ?? MobileSetting::create(['user_id' => Auth::id()]);

        $settings->update(['car_mode_enabled' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Disable car mode
     */
    public function disableCarMode()
    {
        $settings = Auth::user()->mobileSettings;

        if ($settings) {
            $settings->update(['car_mode_enabled' => false]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Set sleep timer
     */
    public function setSleepTimer(Request $request)
    {
        $validated = $request->validate([
            'minutes' => 'required|integer|min:5|max:180',
        ]);

        $settings = Auth::user()->mobileSettings
            ?? MobileSetting::create(['user_id' => Auth::id()]);

        $settings->update(['sleep_timer_minutes' => $validated['minutes']]);

        return response()->json([
            'success' => true,
            'ends_at' => now()->addMinutes($validated['minutes'])->toIso8601String(),
        ]);
    }

    /**
     * Cancel sleep timer
     */
    public function cancelSleepTimer()
    {
        $settings = Auth::user()->mobileSettings;

        if ($settings) {
            $settings->update(['sleep_timer_minutes' => null]);
        }

        return response()->json(['success' => true]);
    }
}
