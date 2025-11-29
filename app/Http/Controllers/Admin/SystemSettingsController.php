<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'maintenance_mode' => File::exists(storage_path('framework/maintenance.php')),
            'registration_enabled' => config('app.registration_enabled', true),
            'email_verification_required' => config('app.email_verification_required', false),
        ];

        return view('admin.settings.system', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
            'email_verification_required' => 'boolean',
            'maintenance_message' => 'nullable|string',
        ]);

        // Handle Maintenance Mode
        if ($request->has('maintenance_mode')) {
            if ($request->maintenance_mode) {
                $message = $request->maintenance_message ?? 'We are currently performing maintenance. Please check back soon.';
                Artisan::call('down', [
                    '--render' => 'errors::503',
                    '--secret' => config('app.key'),
                ]);
            } else {
                Artisan::call('up');
            }
        }

        // Handle Registration Control
        if ($request->has('registration_enabled')) {
            $this->updateEnvValue('REGISTRATION_ENABLED', $request->registration_enabled ? 'true' : 'false');
        }

        // Handle Email Verification Requirement
        if ($request->has('email_verification_required')) {
            $this->updateEnvValue('EMAIL_VERIFICATION_REQUIRED', $request->email_verification_required ? 'true' : 'false');
        }

        return back()->with('success', 'System settings updated successfully.');
    }

    private function updateEnvValue($key, $value)
    {
        $path = base_path('.env');

        if (File::exists($path)) {
            $env = File::get($path);

            // Check if key exists
            if (preg_match("/^{$key}=.*/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}\n";
            }

            File::put($path, $env);
        }
    }
}
