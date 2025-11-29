<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AudioAd;
use App\Models\Advertisement;
use App\Models\AdAnalytics;
use Illuminate\Http\Request;

class AdvertisingController extends Controller
{
    public function index()
    {
        $imageAds = Advertisement::with('user')->latest()->paginate(10);
        $audioAds = AudioAd::with('user')->latest()->paginate(10);

        $stats = [
            'total_image_ads' => Advertisement::count(),
            'active_image_ads' => Advertisement::where('status', 'active')->count(),
            'total_audio_ads' => AudioAd::count(),
            'active_audio_ads' => AudioAd::where('status', 'active')->count(),
            'total_revenue' => AdAnalytics::sum('cost'),
        ];

        return view('admin.advertising.index', compact('imageAds', 'audioAds', 'stats'));
    }

    public function approveImageAd(Advertisement $ad)
    {
        $ad->update(['status' => 'active']);
        return back()->with('success', 'Image ad approved!');
    }

    public function approveAudioAd(AudioAd $ad)
    {
        $ad->update(['status' => 'active']);
        return back()->with('success', 'Audio ad approved!');
    }

    public function pauseImageAd(Advertisement $ad)
    {
        $ad->update(['status' => 'paused']);
        return back()->with('success', 'Image ad paused!');
    }

    public function pauseAudioAd(AudioAd $ad)
    {
        $ad->pause();
        return back()->with('success', 'Audio ad paused!');
    }

    public function analytics()
    {
        $imageAdStats = Advertisement::selectRaw('
                COUNT(*) as total_ads,
                SUM(views) as total_views,
                SUM(clicks) as total_clicks,
                SUM(total_spent) as total_spent
            ')
            ->first();

        $audioAdStats = AudioAd::selectRaw('
                COUNT(*) as total_ads,
                SUM(total_plays) as total_plays,
                SUM(total_clicks) as total_clicks,
                SUM(total_spent) as total_spent
            ')
            ->first();

        $topImageAds = Advertisement::orderByDesc('clicks')->limit(10)->get();
        $topAudioAds = AudioAd::orderByDesc('total_clicks')->limit(10)->get();

        return view('admin.advertising.analytics', compact(
            'imageAdStats',
            'audioAdStats',
            'topImageAds',
            'topAudioAds'
        ));
    }

    public function settings()
    {
        return view('admin.advertising.settings');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'default_cpc_rate' => 'required|numeric|min:0.01',
            'default_cpm_rate' => 'required|numeric|min:0.01',
            'artist_revenue_share' => 'required|integer|min:0|max:100',
            'require_approval' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            config(['advertising.' . $key => $value]);
        }

        return back()->with('success', 'Advertising settings updated!');
    }
}
