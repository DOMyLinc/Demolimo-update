<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('feature_access', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key')->unique();
            $table->string('feature_name');
            $table->text('description')->nullable();
            $table->string('access_level')->default('free'); // free, pro, admin
            $table->boolean('is_beta')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->integer('free_user_limit')->nullable(); // e.g., max 5 radio stations for free users
            $table->integer('pro_user_limit')->nullable(); // e.g., unlimited for pro users
            $table->json('additional_settings')->nullable();
            $table->timestamps();
        });

        // Seed default features
        DB::table('feature_access')->insert([
            // Core Features (Free)
            [
                'feature_key' => 'track_upload',
                'feature_name' => 'Track Upload',
                'description' => 'Upload and share music tracks',
                'access_level' => 'free',
                'is_beta' => false,
                'is_enabled' => true,
                'free_user_limit' => 10,
                'pro_user_limit' => null, // unlimited
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'feature_key' => 'album_creation',
                'feature_name' => 'Album Creation',
                'description' => 'Create and manage albums',
                'access_level' => 'free',
                'is_beta' => false,
                'is_enabled' => true,
                'free_user_limit' => 3,
                'pro_user_limit' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'feature_key' => 'playlist_creation',
                'feature_name' => 'Playlist Creation',
                'description' => 'Create and manage playlists',
                'access_level' => 'free',
                'is_beta' => false,
                'is_enabled' => true,
                'free_user_limit' => 5,
                'pro_user_limit' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pro Features
            [
                'feature_key' => 'radio_station',
                'feature_name' => 'Radio Stations',
                'description' => 'Create and manage radio stations',
                'access_level' => 'pro',
                'is_beta' => false,
                'is_enabled' => true,
                'free_user_limit' => 0,
                'pro_user_limit' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'feature_key' => 'podcast',
                'feature_name' => 'Podcasts',
                'description' => 'Create and host podcasts',
                'access_level' => 'pro',
                'is_beta' => false,
                'is_enabled' => true,
                'free_user_limit' => 0,
                'pro_user_limit' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'feature_key' => 'flash_album',
                'feature_name' => 'Flash Albums (Physical Products)',
                'description' => 'Create and sell physical flash drive albums',
                'access_level' => 'pro',
                'is_beta' => true,
                'is_enabled' => true,
                'free_user_limit' => 0,
                'pro_user_limit' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'feature_key' => 'advanced_analytics',
                'feature_name' => 'Advanced Analytics',
                'description' => 'Detailed insights and statistics',
                'access_level' => 'pro',
                'is_beta' => false,
                'is_enabled' => true,
                'free_user_limit' => 0,
                'pro_user_limit' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'feature_key' => 'custom_branding',
                'feature_name' => 'Custom Branding',
                'description' => 'Custom colors, logos, and themes',
                'access_level' => 'pro',
                'is_beta' => false,
                'is_enabled' => true,
                'free_user_limit' => 0,
                'pro_user_limit' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'feature_key' => 'monetization',
                'feature_name' => 'Monetization Tools',
                'description' => 'Sell tracks, albums, and receive donations',
                'access_level' => 'pro',
                'is_beta' => false,
                'is_enabled' => true,
                'free_user_limit' => 0,
                'pro_user_limit' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'feature_key' => 'live_streaming',
                'feature_name' => 'Live Streaming',
                'description' => 'Stream live performances',
                'access_level' => 'pro',
                'is_beta' => true,
                'is_enabled' => true,
                'free_user_limit' => 0,
                'pro_user_limit' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('feature_access');
    }
};
