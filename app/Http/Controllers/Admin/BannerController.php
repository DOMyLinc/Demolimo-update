<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    /**
     * Display a listing of banners
     */
    public function index(Request $request)
    {
        $query = Banner::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by placement
        if ($request->filled('placement')) {
            $query->forZone($request->placement);
        }

        $banners = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new banner
     */
    public function create()
    {
        $placementZones = $this->getPlacementZones();

        return view('admin.banners.create', compact('placementZones'));
    }

    /**
     * Store a newly created banner
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:image,audio,text',
            'content' => 'required',
            'link' => 'nullable|url',
            'placement_zones' => 'required|array',
            'target_audience' => 'required|in:all,free,pro',
            'status' => 'required|in:draft,scheduled,published,expired',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'priority' => 'nullable|integer|min:0',
        ]);

        // Handle file uploads for image/audio
        if ($request->hasFile('content_file')) {
            $file = $request->file('content_file');
            $path = $file->store('banners', 'public');
            $validated['content'] = Storage::url($path);
        }

        $banner = Banner::create($validated);

        // Clear cache
        $this->bannerService->clearAllCaches();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner created successfully!');
    }

    /**
     * Show the form for editing a banner
     */
    public function edit(Banner $banner)
    {
        $placementZones = $this->getPlacementZones();

        return view('admin.banners.edit', compact('banner', 'placementZones'));
    }

    /**
     * Update the specified banner
     */
    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:image,audio,text',
            'content' => 'required_without:content_file',
            'link' => 'nullable|url',
            'placement_zones' => 'required|array',
            'target_audience' => 'required|in:all,free,pro',
            'status' => 'required|in:draft,scheduled,published,expired',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'priority' => 'nullable|integer|min:0',
        ]);

        // Handle file uploads for image/audio
        if ($request->hasFile('content_file')) {
            $file = $request->file('content_file');
            $path = $file->store('banners', 'public');
            $validated['content'] = Storage::url($path);
        }

        $banner->update($validated);

        // Clear cache
        $this->bannerService->clearAllCaches();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner updated successfully!');
    }

    /**
     * Remove the specified banner
     */
    public function destroy(Banner $banner)
    {
        $banner->delete();

        // Clear cache
        $this->bannerService->clearAllCaches();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner deleted successfully!');
    }

    /**
     * Publish a banner
     */
    public function publish(Banner $banner)
    {
        $banner->update(['status' => 'published']);

        // Clear cache
        $this->bannerService->clearAllCaches();

        return redirect()->back()
            ->with('success', 'Banner published successfully!');
    }

    /**
     * Show banner analytics
     */
    public function analytics(Banner $banner)
    {
        $impressions = $banner->impressions()->where('action', 'impression')->count();
        $clicks = $banner->impressions()->where('action', 'click')->count();
        $ctr = $banner->getCTR();

        // Get daily stats for the last 30 days
        $dailyStats = $banner->impressions()
            ->selectRaw('DATE(created_at) as date, action, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date', 'action')
            ->orderBy('date')
            ->get();

        return view('admin.banners.analytics', compact('banner', 'impressions', 'clicks', 'ctr', 'dailyStats'));
    }

    /**
     * Get available placement zones
     */
    protected function getPlacementZones(): array
    {
        return [
            'landing_hero' => 'Landing Page - Hero Section',
            'landing_sidebar' => 'Landing Page - Sidebar',
            'landing_footer' => 'Landing Page - Footer',
            'player_top' => 'Music Player - Top',
            'player_inline' => 'Music Player - Inline (Ad Break)',
            'player_bottom' => 'Music Player - Bottom',
            'track_page_top' => 'Track Page - Top',
            'track_page_bottom' => 'Track Page - Bottom',
            'dashboard_notification' => 'User Dashboard - Notification Area',
            'global_top' => 'Global - Top Bar',
            'global_bottom' => 'Global - Bottom Bar',
        ];
    }
}
