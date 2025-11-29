<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $featureKey, string $featureName = null): Response
    {
        // Check if feature is enabled
        if (!feature_enabled($featureKey)) {
            // Allow admins to bypass
            if ($request->user() && $request->user()->role === 'admin') {
                return $next($request);
            }

            // Return feature disabled view
            $displayName = $featureName ?? ucwords(str_replace('_', ' ', $featureKey));

            return response()->view('components.feature-disabled', [
                'feature' => $displayName,
                'message' => "The {$displayName} feature is currently disabled. Please contact your administrator for more information."
            ], 403);
        }

        return $next($request);
    }
}
