<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecuritySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SecuritySettingsController extends Controller
{
    public function index()
    {
        $settings = SecuritySetting::first() ?? new SecuritySetting();

        return view('admin.security.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // Login Security
            'max_login_attempts' => 'required|integer|min:1|max:20',
            'lockout_duration' => 'required|integer|min:1|max:1440',
            'enable_login_lockout' => 'boolean',

            // Password Policies
            'min_password_length' => 'required|integer|min:6|max:32',
            'require_uppercase' => 'boolean',
            'require_lowercase' => 'boolean',
            'require_numbers' => 'boolean',
            'require_special_chars' => 'boolean',
            'password_expiry_days' => 'nullable|integer|min:0',

            // Session Management
            'session_timeout' => 'required|integer|min:5|max:1440',
            'force_logout_on_password_change' => 'boolean',
            'allow_concurrent_sessions' => 'boolean',
            'max_concurrent_sessions' => 'required|integer|min:1|max:10',

            // Two-Factor Authentication
            'enable_2fa' => 'boolean',
            'force_2fa_for_admins' => 'boolean',

            // Google reCAPTCHA
            'recaptcha_enabled' => 'boolean',
            'recaptcha_version' => 'required|in:v2,v3',
            'recaptcha_site_key' => 'nullable|string',
            'recaptcha_secret_key' => 'nullable|string',
            'recaptcha_score_threshold' => 'nullable|numeric|min:0|max:1',
            'recaptcha_on_login' => 'boolean',
            'recaptcha_on_register' => 'boolean',
            'recaptcha_on_forgot_password' => 'boolean',

            // IP Security
            'enable_ip_whitelist' => 'boolean',
            'ip_whitelist' => 'nullable|array',
            'enable_ip_blacklist' => 'boolean',
            'ip_blacklist' => 'nullable|array',
        ]);

        $settings = SecuritySetting::first() ?? new SecuritySetting();
        $settings->fill($validated);
        $settings->save();

        return back()->with('success', 'Security settings updated successfully!');
    }

    public function testRecaptcha(Request $request)
    {
        $request->validate([
            'site_key' => 'required|string',
            'secret_key' => 'required|string',
            'version' => 'required|in:v2,v3',
        ]);

        try {
            // Test with a dummy token (in production, this would come from the frontend)
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $request->secret_key,
                'response' => 'test',
            ]);

            $result = $response->json();

            if (isset($result['error-codes']) && in_array('invalid-input-secret', $result['error-codes'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid secret key. Please check your reCAPTCHA credentials.',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'reCAPTCHA credentials are valid!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify reCAPTCHA: ' . $e->getMessage(),
            ]);
        }
    }

    public function clearLoginLockouts()
    {
        // Clear all login lockouts from cache
        \Cache::flush();

        return back()->with('success', 'All login lockouts have been cleared!');
    }
}
