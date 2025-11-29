<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $enabled = \App\Models\Setting::get('enable_feed');
            if ($enabled !== '1' && $enabled !== 'true') {
                if (!auth()->user() || !auth()->user()->hasRole('admin')) {
                    abort(404, 'Feed is currently disabled.');
                }
            }
            return $next($request);
        });
    }

    public function index()
    {
        $followingIds = Auth::user()->following()->pluck('users.id');

        $tracks = \App\Models\Track::whereIn('user_id', $followingIds)
            ->where('is_public', true)
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('user.feed.index', compact('tracks'));
    }
}
