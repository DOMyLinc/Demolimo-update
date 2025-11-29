<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BannerService;
use Illuminate\Http\Request;

class BannerTrackingController extends Controller
{
    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    /**
     * Record a banner impression
     */
    public function impression(Request $request)
    {
        $validated = $request->validate([
            'banner_id' => 'required|exists:banners,id',
        ]);

        $this->bannerService->recordImpression(
            $validated['banner_id'],
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return response()->json(['success' => true]);
    }

    /**
     * Record a banner click
     */
    public function click(Request $request)
    {
        $validated = $request->validate([
            'banner_id' => 'required|exists:banners,id',
        ]);

        $this->bannerService->recordClick(
            $validated['banner_id'],
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return response()->json(['success' => true]);
    }
}
