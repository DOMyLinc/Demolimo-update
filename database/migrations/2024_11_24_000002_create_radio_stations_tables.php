<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('radio_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('type')->default('auto'); // auto, live, scheduled
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // Auto-DJ Settings
            $table->string('genre')->nullable(); // For auto-generated stations
            $table->string('mood')->nullable(); // chill, energetic, focus, etc.
            $table->integer('shuffle_interval')->default(3600); // Seconds between playlist shuffles

            // Live Stream Settings
            $table->string('stream_url')->nullable(); // For live streams
            $table->string('stream_type')->nullable(); // icecast, shoutcast, hls
            $table->integer('bitrate')->nullable();

            // Social Media & Links
            $table->json('social_links')->nullable(); // Facebook, Twitter, Instagram, etc.
            $table->string('website_url')->nullable();
            $table->string('embed_code')->nullable(); // For embedding on external sites

            // DJ Information
            $table->string('dj_name')->nullable();
            $table->string('dj_avatar')->nullable();
            $table->text('dj_bio')->nullable();

            // Statistics
            $table->integer('listeners_count')->default(0);
            $table->integer('total_plays')->default(0);

            $table->timestamps();
        });

        Schema::create('radio_playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radio_station_id')->constrained()->onDelete('cascade');
            $table->foreignId('track_id')->constrained()->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->integer('play_count')->default(0);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();
        });

        Schema::create('radio_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radio_station_id')->constrained()->onDelete('cascade');
            $table->string('day_of_week'); // monday, tuesday, etc.
            $table->time('start_time');
            $table->time('end_time');
            $table->string('show_name')->nullable();
            $table->string('host_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('radio_listeners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radio_station_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('connected_at');
            $table->timestamp('disconnected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('radio_listeners');
        Schema::dropIfExists('radio_schedules');
        Schema::dropIfExists('radio_playlists');
        Schema::dropIfExists('radio_stations');
    }
};
