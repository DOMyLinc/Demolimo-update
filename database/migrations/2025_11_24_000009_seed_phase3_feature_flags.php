<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        $features = [
            // Phase 3 Features
            ['key' => 'enable_podcasts', 'name' => 'Podcasts', 'category' => 'content', 'description' => 'Enable podcast hosting and playback', 'is_enabled' => true],
            ['key' => 'enable_podcast_rss', 'name' => 'Podcast RSS Feeds', 'category' => 'content', 'description' => 'Generate RSS feeds for podcasts', 'is_enabled' => true],
            ['key' => 'enable_livestreaming', 'name' => 'Live Streaming', 'category' => 'streaming', 'description' => 'Allow artists to live stream (Pro only)', 'is_enabled' => true],
            ['key' => 'enable_music_videos', 'name' => 'Music Videos', 'category' => 'content', 'description' => 'Support for music videos', 'is_enabled' => true],
            ['key' => 'enable_fan_clubs', 'name' => 'Fan Clubs', 'category' => 'monetization', 'description' => 'Artist fan clubs with subscriptions', 'is_enabled' => true],
            ['key' => 'enable_presave_campaigns', 'name' => 'Pre-Save Campaigns', 'category' => 'marketing', 'description' => 'Pre-save campaigns for releases', 'is_enabled' => true],
            ['key' => 'enable_voice_search', 'name' => 'Voice Search', 'category' => 'search', 'description' => 'Voice-activated search', 'is_enabled' => false],
            ['key' => 'enable_exclusive_content', 'name' => 'Exclusive Content', 'category' => 'content', 'description' => 'Fan club exclusive content', 'is_enabled' => true],

            // Limits
            ['key' => 'free_podcast_limit', 'name' => 'Free Podcast Limit', 'category' => 'limits', 'description' => 'Max podcasts for free users', 'is_enabled' => true, 'value' => '0'],
            ['key' => 'pro_podcast_limit', 'name' => 'Pro Podcast Limit', 'category' => 'limits', 'description' => 'Max podcasts for Pro users', 'is_enabled' => true, 'value' => 'unlimited'],
            ['key' => 'max_livestream_duration', 'name' => 'Max Livestream Duration', 'category' => 'limits', 'description' => 'Maximum hours per stream', 'is_enabled' => true, 'value' => '8'],
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
            'enable_podcasts',
            'enable_podcast_rss',
            'enable_livestreaming',
            'enable_music_videos',
            'enable_fan_clubs',
            'enable_presave_campaigns',
            'enable_voice_search',
            'enable_exclusive_content',
            'free_podcast_limit',
            'pro_podcast_limit',
            'max_livestream_duration',
        ];

        DB::table('feature_flags')->whereIn('key', $keys)->delete();
    }
};
