<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('system_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('category'); // ffmpeg, redis, storage, etc.
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->text('description')->nullable();
            $table->boolean('is_sensitive')->default(false); // for passwords, API keys
            $table->timestamps();
        });

        // Insert default FFMPEG configuration
        DB::table('system_configurations')->insert([
            [
                'key' => 'ffmpeg_path',
                'category' => 'audio_processing',
                'value' => 'C:\\ffmpeg\\bin\\ffmpeg.exe',
                'type' => 'string',
                'description' => 'Path to FFMPEG executable for audio processing and waveform generation',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ffmpeg_enabled',
                'category' => 'audio_processing',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable FFMPEG audio processing features',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'waveform_generation',
                'category' => 'audio_processing',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Automatically generate waveforms for uploaded tracks',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'audio_normalization',
                'category' => 'audio_processing',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Normalize audio levels during processing',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'reverb_app_id',
                'category' => 'realtime',
                'value' => '',
                'type' => 'string',
                'description' => 'Laravel Reverb Application ID for real-time features',
                'is_sensitive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'reverb_enabled',
                'category' => 'realtime',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable real-time chat and notifications',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'chat_enabled',
                'category' => 'features',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable direct messaging system',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'blog_enabled',
                'category' => 'features',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable blog/articles system',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'radio_enabled',
                'category' => 'features',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable radio stations',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'podcasts_enabled',
                'category' => 'features',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable podcasts system',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'affiliate_enabled',
                'category' => 'features',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable affiliate/referral system',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'points_enabled',
                'category' => 'features',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable points/rewards system',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'import_enabled',
                'category' => 'features',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable audio import from YouTube/SoundCloud',
                'is_sensitive' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'youtube_api_key',
                'category' => 'import',
                'value' => '',
                'type' => 'string',
                'description' => 'YouTube Data API key for imports',
                'is_sensitive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('system_configurations');
    }
};
