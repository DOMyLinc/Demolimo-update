<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogins
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'login'): Response
    {
        $key = $this->resolveRequestSignature($request, $type);
        $maxAttempts = $this->getMaxAttempts($type);
        $decayMinutes = $this->getDecayMinutes($type);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Too many {$type} attempts. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $response;
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request, string $type): string
    {
        $email = $request->input('email', '');
        $ip = $request->ip();

        // Use both email and IP for better security
        return sha1($type . '|' . $email . '|' . $ip);
    }

    /**
     * Get max attempts based on type
     */
    protected function getMaxAttempts(string $type): int
    {
        return match ($type) {
            'login' => config('security.rate_limit.login', 5),
            'register' => config('security.rate_limit.register', 3),
            'password_reset' => config('security.rate_limit.password_reset', 3),
            'api' => config('security.rate_limit.api', 60),
            default => 5,
        };
    }

    /**
     * Get decay minutes based on type
     */
    protected function getDecayMinutes(string $type): int
    {
        return match ($type) {
            'login' => 1,
            'register' => 60,
            'password_reset' => 60,
            'api' => 1,
            default => 1,
        };
    }
}
