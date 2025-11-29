<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Track;
use App\Models\Album;
use App\Models\Post;
use App\Models\Comment;
use App\Models\SongBattle;
use App\Models\Event;
use App\Models\PaymentTransaction;
use App\Models\Revenue;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Overview Stats
        $stats = [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'new_users_month' => User::where('created_at', '>=', now()->subMonth())->count(),

            'total_tracks' => Track::count(),
            'new_tracks_today' => Track::whereDate('created_at', today())->count(),
            'total_plays' => Track::sum('plays'),
            'total_downloads' => Track::sum('downloads'),

            'total_albums' => Album::count(),
            'total_posts' => Post::count(),
            'total_comments' => Comment::count(),

            'active_subscriptions' => UserSubscription::where('status', 'active')->count(),
            'total_revenue' => PaymentTransaction::where('status', 'completed')->sum('amount'),
            'platform_earnings' => Revenue::sum('commission'),
            'monthly_revenue' => PaymentTransaction::where('status', 'completed')
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('amount'),
            'daily_revenue' => PaymentTransaction::where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('amount'),

            'active_song_battles' => SongBattle::where('status', 'active')->count(),
            'upcoming_events' => Event::where('start_date', '>', now())->count(),

            'pending_moderation' => Track::where('status', 'pending')->count(),
            'reported_content' => 0, // Implement if you have reporting system
        ];

        // User Growth (Last 30 days)
        $userGrowth = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue Chart (Last 12 months)
        $revenueChart = PaymentTransaction::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total')
        )
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('month')
            ->get();

        // Revenue by Source
        $revenueBySource = Revenue::select('source_type', DB::raw('SUM(amount) as total_amount'), DB::raw('SUM(commission) as total_commission'))
            ->groupBy('source_type')
            ->get();

        // Top Artists (by plays)
        $topArtists = User::select('users.*', DB::raw('SUM(tracks.plays) as total_plays'))
            ->join('tracks', 'users.id', '=', 'tracks.user_id')
            ->groupBy('users.id')
            ->orderBy('total_plays', 'desc')
            ->limit(10)
            ->get();

        // Top Tracks
        $topTracks = Track::with('user')
            ->orderBy('plays', 'desc')
            ->limit(10)
            ->get();

        // Recent Users
        $recentUsers = User::latest()
            ->limit(10)
            ->get();

        // Recent Transactions
        $recentTransactions = PaymentTransaction::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Platform Activity (Last 7 days)
        $platformActivity = [
            'uploads' => Track::where('created_at', '>=', now()->subDays(7))->count(),
            'plays' => Track::where('updated_at', '>=', now()->subDays(7))->sum('plays'),
            'new_users' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'comments' => Comment::where('created_at', '>=', now()->subDays(7))->count(),
            'subscriptions' => UserSubscription::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        // Storage Usage
        $storageStats = [
            'total_used' => Track::sum('file_size') + Album::sum('total_size'),
            'total_limit' => User::sum('storage_limit'),
            'average_per_user' => User::avg('used_storage'),
        ];

        // Genre Distribution
        $genreDistribution = Track::select('genre', DB::raw('COUNT(*) as count'))
            ->groupBy('genre')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'userGrowth',
            'revenueChart',
            'topArtists',
            'topTracks',
            'recentUsers',
            'recentTransactions',
            'platformActivity',
            'storageStats',
            'genreDistribution',
            'revenueBySource'
        ));
    }
}
