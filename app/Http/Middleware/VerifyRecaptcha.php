<?php

namespace App\Http\Middleware;

use App\Models\SecuritySetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VerifyRecaptcha
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $action = 'login')
    {
        $settings = SecuritySetting::getCached();

        // Check if reCAPTCHA is enabled
        if (!$settings->recaptcha_enabled) {
            return $next($request);
        }

        // Check if reCAPTCHA is enabled for this action
        $enabledForAction = match ($action) {
            'login' => $settings->recaptcha_on_login,
            'register' => $settings->recaptcha_on_register,
            'forgot-password' => $settings->recaptcha_on_forgot_password,
            default => false,
        };

        if (!$enabledForAction) {
            return $next($request);
        }

        // Get reCAPTCHA response from request
        $recaptchaResponse = $request->input('g-recaptcha-response') ?? $request->input('recaptcha_token');

        if (!$recaptchaResponse) {
            return back()->withErrors([
                'recaptcha' => 'Please complete the reCAPTCHA verification.',
            ])->withInput();
        }

        // Verify with Google
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $settings->recaptcha_secret_key,
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip(),
            ]);

            $result = $response->json();

            // For v2, just check success
            if ($settings->recaptcha_version === 'v2') {
                if (!($result['success'] ?? false)) {
                    return back()->withErrors([
                        'recaptcha' => 'reCAPTCHA verification failed. Please try again.',
                    ])->withInput();
                }
            }

            // For v3, check score
            if ($settings->recaptcha_version === 'v3') {
                $score = $result['score'] ?? 0;

                if ($score < $settings->recaptcha_score_threshold) {
                    return back()->withErrors([
                        'recaptcha' => 'reCAPTCHA verification failed. Your score was too low.',
                    ])->withInput();
                }
            }

            return $next($request);
        } catch (\Exception $e) {
            // Log error but don't block user if reCAPTCHA service is down
            \Log::error('reCAPTCHA verification error: ' . $e->getMessage());

            // In production, you might want to allow the request through
            // or show a different error message
            return $next($request);
        }
    }
}
