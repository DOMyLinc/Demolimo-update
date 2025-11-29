<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Audio Quality Settings
        Schema::create('audio_qualities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Low, Normal, High, Lossless
            $table->string('bitrate'); // 128kbps, 320kbps, FLAC
            $table->integer('file_size_multiplier')->default(1);
            $table->boolean('requires_pro')->default(false);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Offline Downloads
        Schema::create('offline_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('downloadable'); // Track, Album, Playlist
            $table->string('quality'); // 128kbps, 320kbps, FLAC
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->timestamp('downloaded_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'downloadable_type', 'downloadable_id']);
        });

        // Lyrics
        Schema::create('lyrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->onDelete('cascade');
            $table->text('content'); // Full lyrics text
            $table->text('synced_content')->nullable(); // LRC format
            $table->string('language')->default('en');
            $table->boolean('is_synced')->default(false);
            $table->foreignId('contributor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->unique('track_id');
        });

        // User Listening Queue
        Schema::create('user_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->json('queue_data'); // Array of track IDs with metadata
            $table->integer('current_index')->default(0);
            $table->string('repeat_mode')->default('off'); // off, one, all
            $table->boolean('shuffle_enabled')->default(false);
            $table->json('shuffle_order')->nullable();
            $table->timestamp('last_updated_at');
            $table->timestamps();

            $table->index('user_id');
        });

        // User Taste Profile (for recommendations)
        Schema::create('user_taste_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('favorite_genres')->nullable();
            $table->json('favorite_artists')->nullable();
            $table->json('listening_patterns')->nullable(); // time of day, duration
            $table->json('mood_preferences')->nullable();
            $table->decimal('diversity_score', 3, 2)->default(0.5); // 0-1
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        // Recommendations
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // discover_weekly, release_radar, daily_mix, similar_artists
            $table->json('track_ids');
            $table->string('algorithm')->default('collaborative'); // collaborative, content_based, hybrid
            $table->decimal('confidence_score', 3, 2)->default(0.5);
            $table->timestamp('generated_at');
            $table->timestamp('expires_at');
            $table->integer('plays_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });

        // Collaborative Playlists
        Schema::create('playlist_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('editor'); // editor, viewer
            $table->boolean('can_add')->default(true);
            $table->boolean('can_remove')->default(true);
            $table->boolean('can_reorder')->default(true);
            $table->timestamp('invited_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['playlist_id', 'user_id']);
        });

        // Direct Messages
        Schema::create('direct_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->morphs('attachable'); // Track, Album, Playlist
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['sender_id', 'recipient_id']);
            $table->index('created_at');
        });

        // Add columns to existing tables
        if (!Schema::hasColumn('tracks', 'audio_quality')) {
            Schema::table('tracks', function (Blueprint $table) {
                $table->string('audio_quality')->default('320kbps')->after('audio_file');
                $table->string('flac_file')->nullable()->after('audio_quality');
                $table->integer('bpm')->nullable()->after('duration');
                $table->string('key')->nullable()->after('bpm');
                $table->json('mood_tags')->nullable()->after('key');
            });
        }

        if (!Schema::hasColumn('users', 'audio_quality_preference')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('audio_quality_preference')->default('normal')->after('storage_limit');
                $table->boolean('offline_mode_enabled')->default(false)->after('audio_quality_preference');
                $table->integer('skip_count')->default(0)->after('offline_mode_enabled');
                $table->timestamp('skip_count_reset_at')->nullable()->after('skip_count');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('direct_messages');
        Schema::dropIfExists('playlist_collaborators');
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('user_taste_profiles');
        Schema::dropIfExists('user_queues');
        Schema::dropIfExists('lyrics');
        Schema::dropIfExists('offline_downloads');
        Schema::dropIfExists('audio_qualities');

        if (Schema::hasColumn('tracks', 'audio_quality')) {
            Schema::table('tracks', function (Blueprint $table) {
                $table->dropColumn(['audio_quality', 'flac_file', 'bpm', 'key', 'mood_tags']);
            });
        }

        if (Schema::hasColumn('users', 'audio_quality_preference')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['audio_quality_preference', 'offline_mode_enabled', 'skip_count', 'skip_count_reset_at']);
            });
        }
    }
};
