<?php

namespace App\Http\Middleware;

use App\Models\SecuritySetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class LoginLockout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $settings = SecuritySetting::getCached();

        if (!$settings->enable_login_lockout) {
            return $next($request);
        }

        $email = $request->input('email');
        $ip = $request->ip();
        $key = $this->throttleKey($email, $ip);

        // Check if user is locked out
        if (RateLimiter::tooManyAttempts($key, $settings->max_login_attempts)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$minutes} minutes.",
            ])->withInput($request->only('email'));
        }

        $response = $next($request);

        // If login failed (check for validation errors or auth failure)
        if ($response->isRedirection() && session()->has('errors')) {
            RateLimiter::hit($key, $settings->lockout_duration * 60);
        }

        // If login succeeded, clear the rate limiter
        if (auth()->check()) {
            RateLimiter::clear($key);
        }

        return $response;
    }

    /**
     * Get the throttle key for the given request.
     */
    protected function throttleKey(string $email, string $ip): string
    {
        return 'login_attempts:' . strtolower($email) . '|' . $ip;
    }
}
