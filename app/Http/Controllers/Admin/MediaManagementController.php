<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\PodcastEpisode;
use App\Models\LiveStream;
use App\Models\LiveStreamMessage;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MediaManagementController extends Controller
{
    /**
     * Podcast Management
     */
    public function podcasts()
    {
        $podcasts = Podcast::with('user')
            ->withCount('episodes')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_podcasts' => Podcast::count(),
            'total_episodes' => PodcastEpisode::count(),
            'total_plays' => Podcast::sum('total_plays'),
            'total_subscribers' => Podcast::sum('subscribers'),
            'by_category' => Podcast::select('category', DB::raw('COUNT(*) as count'))
                ->groupBy('category')
                ->get(),
        ];

        return view('admin.media.podcasts', compact('podcasts', 'stats'));
    }

    /**
     * View podcast details
     */
    public function showPodcast(Podcast $podcast)
    {
        $podcast->load(['user', 'episodes']);
        return view('admin.media.podcast-details', compact('podcast'));
    }

    /**
     * Delete podcast
     */
    public function deletePodcast(Podcast $podcast)
    {
        // Delete all episodes
        foreach ($podcast->episodes as $episode) {
            \Storage::delete('public/' . $episode->audio_file);
            $episode->delete();
        }

        // Delete cover image
        if ($podcast->cover_image) {
            \Storage::delete('public/' . $podcast->cover_image);
        }

        $podcast->delete();

        return redirect()->route('admin.media.podcasts')
            ->with('success', 'Podcast deleted successfully!');
    }

    /**
     * Podcast Settings
     */
    public function podcastSettings()
    {
        $settings = [
            'enable_podcasts' => FeatureFlag::isEnabled('enable_podcasts'),
            'enable_podcast_rss' => FeatureFlag::isEnabled('enable_podcast_rss'),
            'free_podcast_limit' => FeatureFlag::getValue('free_podcast_limit', 0),
            'pro_podcast_limit' => FeatureFlag::getValue('pro_podcast_limit', 'unlimited'),
        ];

        return view('admin.media.podcast-settings', compact('settings'));
    }

    /**
     * Update podcast settings
     */
    public function updatePodcastSettings(Request $request)
    {
        $validated = $request->validate([
            'enable_podcasts' => 'boolean',
            'enable_podcast_rss' => 'boolean',
            'free_podcast_limit' => 'integer|min:0',
            'pro_podcast_limit' => 'string',
        ]);

        FeatureFlag::where('key', 'enable_podcasts')->update(['is_enabled' => $validated['enable_podcasts'] ?? false]);
        FeatureFlag::where('key', 'enable_podcast_rss')->update(['is_enabled' => $validated['enable_podcast_rss'] ?? false]);
        FeatureFlag::where('key', 'free_podcast_limit')->update(['value' => $validated['free_podcast_limit']]);
        FeatureFlag::where('key', 'pro_podcast_limit')->update(['value' => $validated['pro_podcast_limit']]);

        return back()->with('success', 'Podcast settings updated!');
    }

    /**
     * Live Streams Management
     */
    public function liveStreams()
    {
        $liveStreams = LiveStream::with('user')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_streams' => LiveStream::count(),
            'live_now' => LiveStream::where('status', 'live')->count(),
            'scheduled' => LiveStream::where('status', 'scheduled')->count(),
            'total_views' => LiveStream::sum('total_views'),
            'peak_viewers' => LiveStream::max('peak_viewers'),
        ];

        return view('admin.media.live-streams', compact('liveStreams', 'stats'));
    }

    /**
     * View live stream details
     */
    public function showLiveStream(LiveStream $stream)
    {
        $stream->load([
            'user',
            'messages' => function ($q) {
                $q->latest()->limit(100);
            }
        ]);

        return view('admin.media.live-stream-details', compact('stream'));
    }

    /**
     * End live stream (admin action)
     */
    public function endLiveStream(LiveStream $stream)
    {
        $stream->end();
        return back()->with('success', 'Live stream ended!');
    }

    /**
     * Delete live stream message
     */
    public function deleteLiveStreamMessage(LiveStreamMessage $message)
    {
        $message->update(['is_deleted' => true]);
        return back()->with('success', 'Message deleted!');
    }

    /**
     * Live Stream Settings
     */
    public function liveStreamSettings()
    {
        $settings = [
            'enable_livestreaming' => FeatureFlag::isEnabled('enable_livestreaming'),
            'max_livestream_duration' => FeatureFlag::getValue('max_livestream_duration', 8),
        ];

        return view('admin.media.livestream-settings', compact('settings'));
    }

    /**
     * Update live stream settings
     */
    public function updateLiveStreamSettings(Request $request)
    {
        $validated = $request->validate([
            'enable_livestreaming' => 'boolean',
            'max_livestream_duration' => 'integer|min:1|max:24',
        ]);

        FeatureFlag::where('key', 'enable_livestreaming')->update(['is_enabled' => $validated['enable_livestreaming'] ?? false]);
        FeatureFlag::where('key', 'max_livestream_duration')->update(['value' => $validated['max_livestream_duration']]);

        return back()->with('success', 'Live stream settings updated!');
    }

    /**
     * Music Videos Management
     */
    public function musicVideos()
    {
        $videos = \App\Models\MusicVideo::with(['track', 'track.user'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_videos' => \App\Models\MusicVideo::count(),
            'total_views' => \App\Models\MusicVideo::sum('views'),
            'total_storage' => \App\Models\MusicVideo::sum('file_size'),
            'by_quality' => \App\Models\MusicVideo::select('quality', DB::raw('COUNT(*) as count'))
                ->groupBy('quality')
                ->get(),
        ];

        return view('admin.media.music-videos', compact('videos', 'stats'));
    }

    /**
     * Delete music video
     */
    public function deleteMusicVideo($videoId)
    {
        $video = \App\Models\MusicVideo::findOrFail($videoId);

        \Storage::delete('public/' . $video->video_file);
        if ($video->thumbnail) {
            \Storage::delete('public/' . $video->thumbnail);
        }

        $video->delete();

        return back()->with('success', 'Music video deleted!');
    }
}
