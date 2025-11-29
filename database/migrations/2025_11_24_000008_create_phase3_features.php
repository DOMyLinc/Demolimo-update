<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Podcasts
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('cover_image')->nullable();
            $table->string('category');
            $table->json('tags')->nullable();
            $table->string('language')->default('en');
            $table->boolean('is_explicit')->default(false);
            $table->string('rss_feed_url')->nullable();
            $table->integer('total_episodes')->default(0);
            $table->integer('total_plays')->default(0);
            $table->integer('subscribers')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });

        // Podcast Episodes
        Schema::create('podcast_episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('podcast_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('audio_file');
            $table->integer('duration')->default(0); // seconds
            $table->integer('file_size')->default(0);
            $table->integer('episode_number')->nullable();
            $table->integer('season_number')->nullable();
            $table->json('chapters')->nullable(); // Timestamps with titles
            $table->string('transcript_file')->nullable();
            $table->integer('plays')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['podcast_id', 'published_at']);
        });

        // Live Streams
        Schema::create('live_streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('stream_key')->unique();
            $table->string('stream_url')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, live, ended
            $table->integer('current_viewers')->default(0);
            $table->integer('peak_viewers')->default(0);
            $table->integer('total_views')->default(0);
            $table->boolean('enable_chat')->default(true);
            $table->boolean('enable_donations')->default(true);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // Live Stream Chat
        Schema::create('live_stream_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->constrained('live_streams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['stream_id', 'created_at']);
        });

        // Music Videos
        Schema::create('music_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->onDelete('cascade');
            $table->string('video_file');
            $table->string('thumbnail')->nullable();
            $table->integer('duration')->default(0);
            $table->integer('file_size')->default(0);
            $table->string('quality')->default('1080p'); // 720p, 1080p, 4k
            $table->integer('views')->default(0);
            $table->timestamps();

            $table->unique('track_id');
        });

        // Pre-Save Campaigns
        Schema::create('presave_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('releasable'); // Track, Album
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->timestamp('release_date');
            $table->integer('presaves_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('presave_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('presave_campaigns')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('notified')->default(false);
            $table->timestamps();

            $table->unique(['campaign_id', 'user_id']);
        });

        // Fan Clubs
        Schema::create('fan_clubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('monthly_price', 8, 2);
            $table->json('benefits')->nullable(); // List of perks
            $table->integer('members_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('artist_id');
        });

        Schema::create('fan_club_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fan_club_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('active'); // active, cancelled, expired
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['fan_club_id', 'user_id']);
        });

        // Exclusive Content (for fan clubs)
        Schema::create('exclusive_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fan_club_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // track, video, post, livestream
            $table->morphs('content'); // Track, Video, Post
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // Voice Search Logs
        Schema::create('voice_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('audio_file')->nullable();
            $table->text('transcription');
            $table->text('search_query');
            $table->integer('results_count')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });

        // Add columns to existing tables
        if (!Schema::hasColumn('tracks', 'has_video')) {
            Schema::table('tracks', function (Blueprint $table) {
                $table->boolean('has_video')->default(false)->after('audio_quality');
                $table->boolean('is_podcast_episode')->default(false)->after('has_video');
            });
        }

        if (!Schema::hasColumn('users', 'can_livestream')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('can_livestream')->default(false)->after('is_verified');
                $table->boolean('has_fan_club')->default(false)->after('can_livestream');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('voice_searches');
        Schema::dropIfExists('exclusive_content');
        Schema::dropIfExists('fan_club_memberships');
        Schema::dropIfExists('fan_clubs');
        Schema::dropIfExists('presave_users');
        Schema::dropIfExists('presave_campaigns');
        Schema::dropIfExists('music_videos');
        Schema::dropIfExists('live_stream_messages');
        Schema::dropIfExists('live_streams');
        Schema::dropIfExists('podcast_episodes');
        Schema::dropIfExists('podcasts');

        if (Schema::hasColumn('tracks', 'has_video')) {
            Schema::table('tracks', function (Blueprint $table) {
                $table->dropColumn(['has_video', 'is_podcast_episode']);
            });
        }

        if (Schema::hasColumn('users', 'can_livestream')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['can_livestream', 'has_fan_club']);
            });
        }
    }
};
