<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Insert Phase 1 feature flags
        $features = [
            // Audio & Streaming
            ['key' => 'enable_audio_quality_options', 'name' => 'Audio Quality Options', 'category' => 'streaming', 'description' => 'Allow users to select audio quality', 'is_enabled' => true],
            ['key' => 'enable_offline_downloads', 'name' => 'Offline Downloads', 'category' => 'streaming', 'description' => 'Allow Pro users to download for offline listening', 'is_enabled' => true],
            ['key' => 'enable_flac_streaming', 'name' => 'FLAC Streaming', 'category' => 'streaming', 'description' => 'Enable lossless FLAC streaming for Pro users', 'is_enabled' => true],

            // Lyrics
            ['key' => 'enable_lyrics', 'name' => 'Lyrics', 'category' => 'content', 'description' => 'Display song lyrics', 'is_enabled' => true],
            ['key' => 'enable_synced_lyrics', 'name' => 'Synchronized Lyrics', 'category' => 'content', 'description' => 'Karaoke-style synchronized lyrics (Pro only)', 'is_enabled' => true],

            // Queue
            ['key' => 'enable_advanced_queue', 'name' => 'Advanced Queue', 'category' => 'playback', 'description' => 'Advanced queue management features', 'is_enabled' => true],
            ['key' => 'enable_queue_persistence', 'name' => 'Queue Persistence', 'category' => 'playback', 'description' => 'Save queue across sessions', 'is_enabled' => true],

            // Recommendations
            ['key' => 'enable_recommendations', 'name' => 'Recommendations', 'category' => 'discovery', 'description' => 'AI-powered music recommendations', 'is_enabled' => true],
            ['key' => 'enable_discover_weekly', 'name' => 'Discover Weekly', 'category' => 'discovery', 'description' => 'Weekly personalized playlist', 'is_enabled' => true],
            ['key' => 'enable_release_radar', 'name' => 'Release Radar', 'category' => 'discovery', 'description' => 'New releases from followed artists', 'is_enabled' => true],
            ['key' => 'enable_daily_mix', 'name' => 'Daily Mix', 'category' => 'discovery', 'description' => 'Daily personalized mixes', 'is_enabled' => true],

            // Social
            ['key' => 'enable_collaborative_playlists', 'name' => 'Collaborative Playlists', 'category' => 'social', 'description' => 'Allow multiple users to edit playlists', 'is_enabled' => true],
            ['key' => 'enable_direct_messaging', 'name' => 'Direct Messaging', 'category' => 'social', 'description' => 'User-to-user messaging', 'is_enabled' => true],
            ['key' => 'enable_friend_activity', 'name' => 'Friend Activity', 'category' => 'social', 'description' => 'See what friends are listening to', 'is_enabled' => false],

            // Limits
            ['key' => 'free_skip_limit', 'name' => 'Free Skip Limit', 'category' => 'limits', 'description' => 'Number of skips per hour for free users', 'is_enabled' => true, 'value' => '6'],
            ['key' => 'free_playlist_limit', 'name' => 'Free Playlist Limit', 'category' => 'limits', 'description' => 'Maximum playlists for free users', 'is_enabled' => true, 'value' => '50'],
            ['key' => 'free_message_limit', 'name' => 'Free Message Limit', 'category' => 'limits', 'description' => 'Messages per day for free users', 'is_enabled' => true, 'value' => '10'],
        ];

        foreach ($features as $feature) {
            DB::table('feature_flags')->updateOrInsert(
                ['key' => $feature['key']],
                array_merge($feature, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down()
    {
        $keys = [
            'enable_audio_quality_options',
            'enable_offline_downloads',
            'enable_flac_streaming',
            'enable_lyrics',
            'enable_synced_lyrics',
            'enable_advanced_queue',
            'enable_queue_persistence',
            'enable_recommendations',
            'enable_discover_weekly',
            'enable_release_radar',
            'enable_daily_mix',
            'enable_collaborative_playlists',
            'enable_direct_messaging',
            'enable_friend_activity',
            'free_skip_limit',
            'free_playlist_limit',
            'free_message_limit',
        ];

        DB::table('feature_flags')->whereIn('key', $keys)->delete();
    }
};
