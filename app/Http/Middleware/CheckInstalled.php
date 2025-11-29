<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip check for install routes
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        if (!file_exists(public_path('.installed'))) {
            return redirect()->route('install.index');
        }

        return $next($request);
    }
}
