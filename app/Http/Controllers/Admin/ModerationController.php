<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentModerationService;
use App\Services\ArtistAnalyticsService;
use App\Models\ContentReport;
use App\Models\DmcaTakedown;
use App\Models\User;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    protected $moderationService;

    public function __construct(ContentModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    /**
     * Content Reports Queue
     */
    public function reports(Request $request)
    {
        $type = $request->get('type', 'all');
        $reports = $this->moderationService->getModerationQueue($type);

        $stats = [
            'pending' => ContentReport::where('status', 'pending')->count(),
            'resolved' => ContentReport::where('status', 'resolved')->count(),
            'dismissed' => ContentReport::where('status', 'dismissed')->count(),
            'by_reason' => ContentReport::select('reason', \DB::raw('COUNT(*) as count'))
                ->where('status', 'pending')
                ->groupBy('reason')
                ->get(),
        ];

        return view('admin.moderation.reports', compact('reports', 'stats', 'type'));
    }

    /**
     * View report details
     */
    public function showReport(ContentReport $report)
    {
        $report->load(['reporter', 'reportable']);
        return view('admin.moderation.report-details', compact('report'));
    }

    /**
     * Review report
     */
    public function reviewReport(Request $request, ContentReport $report)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,dismiss',
            'notes' => 'nullable|string',
        ]);

        $this->moderationService->reviewReport(
            $report->id,
            auth()->id(),
            $validated['action'],
            $validated['notes']
        );

        return redirect()->route('admin.moderation.reports')
            ->with('success', 'Report reviewed successfully!');
    }

    /**
     * DMCA Takedowns Queue
     */
    public function dmcaTakedowns()
    {
        $takedowns = $this->moderationService->getDmcaQueue();

        $stats = [
            'pending' => DmcaTakedown::where('status', 'pending')->count(),
            'approved' => DmcaTakedown::where('status', 'approved')->count(),
            'rejected' => DmcaTakedown::where('status', 'rejected')->count(),
        ];

        return view('admin.moderation.dmca-takedowns', compact('takedowns', 'stats'));
    }

    /**
     * View DMCA takedown details
     */
    public function showDmcaTakedown(DmcaTakedown $takedown)
    {
        return view('admin.moderation.dmca-details', compact('takedown'));
    }

    /**
     * Process DMCA takedown
     */
    public function processDmcaTakedown(Request $request, DmcaTakedown $takedown)
    {
        $validated = $request->validate([
            'action' => 'required|in:approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $this->moderationService->processDmcaTakedown(
            $takedown->id,
            auth()->id(),
            $validated['action'],
            $validated['notes']
        );

        return redirect()->route('admin.moderation.dmca-takedowns')
            ->with('success', 'DMCA takedown processed!');
    }

    /**
     * Artist Analytics Overview
     */
    public function artistAnalytics()
    {
        $topArtists = User::where('role', 'artist')
            ->withCount('tracks', 'followers')
            ->orderBy('followers_count', 'desc')
            ->limit(20)
            ->get();

        $stats = [
            'total_artists' => User::where('role', 'artist')->count(),
            'verified_artists' => User::where('role', 'artist')->where('is_verified', true)->count(),
            'pro_artists' => User::where('role', 'artist')->where('subscription_type', 'pro')->count(),
        ];

        return view('admin.analytics.artists', compact('topArtists', 'stats'));
    }

    /**
     * View individual artist analytics
     */
    public function showArtistAnalytics(User $user, ArtistAnalyticsService $analyticsService)
    {
        if ($user->role !== 'artist') {
            abort(404);
        }

        $analytics = $analyticsService->generateAnalytics($user->id);

        return view('admin.analytics.artist-details', compact('user', 'analytics'));
    }

    /**
     * Platform-wide Analytics
     */
    public function platformAnalytics()
    {
        $stats = [
            'total_users' => User::count(),
            'pro_users' => User::where('subscription_type', 'pro')->count(),
            'total_tracks' => \App\Models\Track::count(),
            'total_plays' => \App\Models\Listener::count(),
            'total_revenue' => \App\Models\Revenue::sum('amount'),
            'platform_fees' => \App\Models\Revenue::sum('platform_fee'),
        ];

        // Daily stats for last 30 days
        $dailyStats = \App\Models\Listener::select(
            \DB::raw('DATE(started_at) as date'),
            \DB::raw('COUNT(*) as plays'),
            \DB::raw('COUNT(DISTINCT user_id) as unique_listeners')
        )
            ->where('started_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.analytics.platform', compact('stats', 'dailyStats'));
    }
}
