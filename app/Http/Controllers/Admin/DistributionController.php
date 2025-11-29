<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\Track;
use App\Models\Album;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistributionController extends Controller
{
    /**
     * Display a listing of distribution requests.
     */
    public function index(Request $request)
    {
        $query = Distribution::with(['user', 'track', 'album'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by platform
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        // Search by user name or track title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('track', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $distributions = $query->paginate(20);

        $stats = [
            'total' => Distribution::count(),
            'pending' => Distribution::where('status', 'pending')->count(),
            'approved' => Distribution::where('status', 'approved')->count(),
            'distributed' => Distribution::where('status', 'distributed')->count(),
            'rejected' => Distribution::where('status', 'rejected')->count(),
        ];

        return view('admin.distributions.index', compact('distributions', 'stats'));
    }

    /**
     * Display the specified distribution.
     */
    public function show(Distribution $distribution)
    {
        $distribution->load(['user', 'track', 'album']);

        return view('admin.distributions.show', compact('distribution'));
    }

    /**
     * Approve a distribution request.
     */
    public function approve(Distribution $distribution)
    {
        if ($distribution->status !== 'pending') {
            return back()->with('error', 'Only pending distributions can be approved.');
        }

        $distribution->update([
            'status' => 'approved'
        ]);

        // TODO: Trigger actual distribution process to platforms
        // This would integrate with DistroKid, TuneCore, or other APIs

        return back()->with('success', 'Distribution request approved successfully.');
    }

    /**
     * Reject a distribution request.
     */
    public function reject(Request $request, Distribution $distribution)
    {
        if ($distribution->status !== 'pending') {
            return back()->with('error', 'Only pending distributions can be rejected.');
        }

        $distribution->update([
            'status' => 'rejected'
        ]);

        // TODO: Send notification to user about rejection

        return back()->with('success', 'Distribution request rejected.');
    }

    /**
     * Update distribution status.
     */
    public function updateStatus(Request $request, Distribution $distribution)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,distributed,failed',
        ]);

        $distribution->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Distribution status updated successfully.');
    }

    /**
     * Display distribution platforms management.
     */
    public function platforms()
    {
        $platforms = [
            'spotify' => 'Spotify',
            'apple_music' => 'Apple Music',
            'youtube_music' => 'YouTube Music',
            'amazon_music' => 'Amazon Music',
            'tidal' => 'Tidal',
            'deezer' => 'Deezer',
            'soundcloud' => 'SoundCloud',
            'bandcamp' => 'Bandcamp',
        ];

        $platformStats = Distribution::select('platform', DB::raw('count(*) as total'))
            ->groupBy('platform')
            ->get()
            ->pluck('total', 'platform');

        return view('admin.distributions.platforms', compact('platforms', 'platformStats'));
    }

    /**
     * Display distribution analytics.
     */
    public function analytics()
    {
        $totalDistributions = Distribution::count();
        $totalEarnings = Distribution::sum('earnings');

        $monthlyDistributions = Distribution::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('count(*) as total')
        )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $platformBreakdown = Distribution::select('platform', DB::raw('count(*) as total'))
            ->groupBy('platform')
            ->get();

        $topArtists = Distribution::select('user_id', DB::raw('count(*) as total'))
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return view('admin.distributions.analytics', compact(
            'totalDistributions',
            'totalEarnings',
            'monthlyDistributions',
            'platformBreakdown',
            'topArtists'
        ));
    }

    /**
     * Display earnings overview.
     */
    public function earnings()
    {
        $totalEarnings = Distribution::sum('earnings');

        $earningsByPlatform = Distribution::select('platform', DB::raw('sum(earnings) as total'))
            ->groupBy('platform')
            ->get();

        $recentEarnings = Distribution::where('earnings', '>', 0)
            ->with(['user', 'track'])
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.distributions.earnings', compact(
            'totalEarnings',
            'earningsByPlatform',
            'recentEarnings'
        ));
    }

    /**
     * Delete a distribution.
     */
    public function destroy(Distribution $distribution)
    {
        $distribution->delete();

        return redirect()->route('admin.distributions.index')
            ->with('success', 'Distribution deleted successfully.');
    }
}
