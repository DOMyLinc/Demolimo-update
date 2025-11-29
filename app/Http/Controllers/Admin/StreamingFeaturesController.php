<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfflineDownload;
use App\Models\Lyric;
use App\Models\UserQueue;
use App\Models\Recommendation;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StreamingFeaturesController extends Controller
{
    /**
     * Audio Quality Settings
     */
    public function audioQualitySettings()
    {
        $settings = [
            'enable_audio_quality_options' => FeatureFlag::isEnabled('enable_audio_quality_options'),
            'enable_flac_streaming' => FeatureFlag::isEnabled('enable_flac_streaming'),
            'enable_offline_downloads' => FeatureFlag::isEnabled('enable_offline_downloads'),
        ];

        $stats = [
            'total_downloads' => OfflineDownload::count(),
            'active_downloads' => OfflineDownload::active()->count(),
            'total_storage_used' => OfflineDownload::sum('file_size'),
            'downloads_by_quality' => OfflineDownload::select('quality', DB::raw('COUNT(*) as count'))
                ->groupBy('quality')
                ->get(),
        ];

        return view('admin.streaming.audio-quality', compact('settings', 'stats'));
    }

    /**
     * Update audio quality settings
     */
    public function updateAudioQualitySettings(Request $request)
    {
        $validated = $request->validate([
            'enable_audio_quality_options' => 'boolean',
            'enable_flac_streaming' => 'boolean',
            'enable_offline_downloads' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            FeatureFlag::where('key', $key)->update(['is_enabled' => $value]);
        }

        return back()->with('success', 'Audio quality settings updated!');
    }

    /**
     * Offline Downloads Management
     */
    public function offlineDownloads()
    {
        $downloads = OfflineDownload::with(['user', 'downloadable'])
            ->latest()
            ->paginate(50);

        $stats = [
            'total_downloads' => OfflineDownload::count(),
            'active_downloads' => OfflineDownload::active()->count(),
            'expired_downloads' => OfflineDownload::whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count(),
            'total_storage' => OfflineDownload::sum('file_size'),
            'by_type' => OfflineDownload::select('downloadable_type', DB::raw('COUNT(*) as count'))
                ->groupBy('downloadable_type')
                ->get(),
        ];

        return view('admin.streaming.offline-downloads', compact('downloads', 'stats'));
    }

    /**
     * Delete offline download
     */
    public function deleteOfflineDownload(OfflineDownload $download)
    {
        \Storage::delete($download->file_path);
        $download->delete();

        return back()->with('success', 'Download deleted successfully!');
    }

    /**
     * Lyrics Management
     */
    public function lyrics()
    {
        $lyrics = Lyric::with(['track', 'contributor'])
            ->latest()
            ->paginate(50);

        $stats = [
            'total_lyrics' => Lyric::count(),
            'synced_lyrics' => Lyric::where('is_synced', true)->count(),
            'verified_lyrics' => Lyric::where('is_verified', true)->count(),
            'pending_verification' => Lyric::where('is_verified', false)->count(),
        ];

        return view('admin.streaming.lyrics', compact('lyrics', 'stats'));
    }

    /**
     * Verify lyrics
     */
    public function verifyLyrics(Lyric $lyric)
    {
        $lyric->update(['is_verified' => true]);
        return back()->with('success', 'Lyrics verified!');
    }

    /**
     * Delete lyrics
     */
    public function deleteLyrics(Lyric $lyric)
    {
        $lyric->delete();
        return back()->with('success', 'Lyrics deleted!');
    }

    /**
     * Queue Analytics
     */
    public function queueAnalytics()
    {
        $stats = [
            'total_queues' => UserQueue::count(),
            'active_queues' => UserQueue::where('last_updated_at', '>', now()->subHours(24))->count(),
            'average_queue_size' => UserQueue::avg(DB::raw('JSON_LENGTH(queue_data)')),
            'shuffle_enabled' => UserQueue::where('shuffle_enabled', true)->count(),
            'repeat_modes' => UserQueue::select('repeat_mode', DB::raw('COUNT(*) as count'))
                ->groupBy('repeat_mode')
                ->get(),
        ];

        $recentQueues = UserQueue::with('user')
            ->latest('last_updated_at')
            ->limit(20)
            ->get();

        return view('admin.streaming.queue-analytics', compact('stats', 'recentQueues'));
    }

    /**
     * Recommendation Settings
     */
    public function recommendationSettings()
    {
        $settings = [
            'enable_recommendations' => FeatureFlag::isEnabled('enable_recommendations'),
            'enable_discover_weekly' => FeatureFlag::isEnabled('enable_discover_weekly'),
            'enable_release_radar' => FeatureFlag::isEnabled('enable_release_radar'),
            'enable_daily_mix' => FeatureFlag::isEnabled('enable_daily_mix'),
        ];

        $stats = [
            'total_recommendations' => Recommendation::count(),
            'active_recommendations' => Recommendation::where('expires_at', '>', now())->count(),
            'by_type' => Recommendation::select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get(),
            'average_confidence' => Recommendation::avg('confidence_score'),
        ];

        return view('admin.streaming.recommendations', compact('settings', 'stats'));
    }

    /**
     * Update recommendation settings
     */
    public function updateRecommendationSettings(Request $request)
    {
        $validated = $request->validate([
            'enable_recommendations' => 'boolean',
            'enable_discover_weekly' => 'boolean',
            'enable_release_radar' => 'boolean',
            'enable_daily_mix' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            FeatureFlag::where('key', $key)->update(['is_enabled' => $value]);
        }

        return back()->with('success', 'Recommendation settings updated!');
    }
}
