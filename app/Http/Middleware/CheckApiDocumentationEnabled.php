<?php

namespace App\Http\Middleware;

use App\Models\FeatureFlag;
use Closure;
use Illuminate\Http\Request;

class CheckApiDocumentationEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if API documentation is publicly enabled
        $isEnabled = FeatureFlag::isEnabled('enable_public_api_docs');

        if (!$isEnabled) {
            // If user is logged in and is admin, allow access
            if (auth()->check() && auth()->user()->role === 'admin') {
                return $next($request);
            }

            // Otherwise show 404 or custom message
            abort(404, 'API documentation is currently unavailable.');
        }

        return $next($request);
    }
}
