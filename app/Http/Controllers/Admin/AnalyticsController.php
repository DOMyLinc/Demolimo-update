<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Track;
use App\Models\Analytics;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $stats = [
            // User Stats
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'new_users_month' => User::where('created_at', '>=', now()->subMonth())->count(),

            // Track Stats
            'total_tracks' => Track::count(),
            'total_plays' => Track::sum('plays'),
            'total_downloads' => Track::sum('downloads'),
            'tracks_uploaded_today' => Track::whereDate('created_at', today())->count(),

            // Revenue Stats
            'active_subscriptions' => UserSubscription::where('status', 'active')->count(),
            'monthly_revenue' => UserSubscription::where('status', 'active')
                ->join('subscriptions', 'user_subscriptions.subscription_id', '=', 'subscriptions.id')
                ->sum('subscriptions.price'),

            // Storage Stats
            'total_storage_used' => User::sum('used_storage'),
            'total_storage_limit' => User::sum('storage_limit'),
        ];

        // Chart data for last 30 days
        $userGrowth = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trackUploads = Track::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topTracks = Track::with('user')
            ->orderBy('plays', 'desc')
            ->limit(10)
            ->get();

        $topArtists = User::where('role', 'artist')
            ->withCount('tracks')
            ->withSum('tracks', 'plays')
            ->orderBy('tracks_sum_plays', 'desc')
            ->limit(10)
            ->get();

        return view('admin.analytics.index', compact(
            'stats',
            'userGrowth',
            'trackUploads',
            'topTracks',
            'topArtists'
        ));
    }

    public function revenue()
    {
        $revenueByMonth = UserSubscription::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as subscriptions')
        )
            ->where('status', 'active')
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('admin.analytics.revenue', compact('revenueByMonth'));
    }
}
