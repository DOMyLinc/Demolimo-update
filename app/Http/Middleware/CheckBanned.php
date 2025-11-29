<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBanned
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is banned
            if ($user->is_banned) {
                // Check if ban has expired
                if ($user->banned_until && $user->banned_until->isPast()) {
                    $user->update([
                        'is_banned' => false,
                        'banned_until' => null,
                    ]);
                } else {
                    Auth::logout();

                    $message = 'Your account has been banned.';
                    if ($user->banned_until) {
                        $message .= ' Ban expires on: ' . $user->banned_until->format('M d, Y H:i');
                    }

                    return redirect()->route('login')->with('error', $message);
                }
            }
        }

        return $next($request);
    }
}
