<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Smart Playlists
        Schema::create('smart_playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->onDelete('cascade');
            $table->json('rules'); // Genre, BPM, mood, year, etc.
            $table->integer('max_tracks')->default(50);
            $table->string('sort_by')->default('created_at'); // plays, likes, created_at
            $table->string('sort_direction')->default('desc');
            $table->boolean('auto_update')->default(true);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique('playlist_id');
        });

        // Content Reports
        Schema::create('content_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->morphs('reportable'); // Track, Album, User, Comment
            $table->string('reason'); // copyright, spam, inappropriate, other
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, reviewing, resolved, dismissed
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id']);
            $table->index('status');
        });

        // DMCA Takedowns
        Schema::create('dmca_takedowns', function (Blueprint $table) {
            $table->id();
            $table->string('claimant_name');
            $table->string('claimant_email');
            $table->string('claimant_company')->nullable();
            $table->morphs('content'); // Track, Album
            $table->text('original_work_description');
            $table->text('infringement_description');
            $table->string('signature');
            $table->boolean('good_faith_statement')->default(false);
            $table->boolean('accuracy_statement')->default(false);
            $table->string('status')->default('pending'); // pending, processing, approved, rejected
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // Audio Fingerprints (for duplicate/copyright detection)
        Schema::create('audio_fingerprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->onDelete('cascade');
            $table->text('fingerprint'); // Chromaprint or similar
            $table->integer('duration_ms');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->unique('track_id');
        });

        // Artist Analytics
        Schema::create('artist_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('plays')->default(0);
            $table->integer('unique_listeners')->default(0);
            $table->integer('new_followers')->default(0);
            $table->integer('playlist_adds')->default(0);
            $table->decimal('revenue', 10, 2)->default(0);
            $table->json('top_countries')->nullable();
            $table->json('top_tracks')->nullable();
            $table->json('demographics')->nullable(); // age, gender
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index('date');
        });

        // Search History
        Schema::create('search_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('query');
            $table->json('filters')->nullable(); // genre, year, bpm, etc.
            $table->integer('results_count')->default(0);
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
        });

        // Playlist Folders
        Schema::create('playlist_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::table('playlists', function (Blueprint $table) {
            if (!Schema::hasColumn('playlists', 'folder_id')) {
                $table->foreignId('folder_id')->nullable()->after('user_id')->constrained('playlist_folders')->onDelete('set null');
            }
            if (!Schema::hasColumn('playlists', 'is_smart')) {
                $table->boolean('is_smart')->default(false)->after('is_public');
            }
        });

        // Friend Activity
        Schema::create('friend_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity_type'); // listening, liked, followed, created_playlist
            $table->morphs('activity_subject'); // Track, User, Playlist
            $table->timestamp('activity_at');
            $table->timestamps();

            $table->index(['user_id', 'activity_at']);
        });

        // Group Listening Sessions
        Schema::create('group_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
            $table->string('session_code')->unique();
            $table->string('name');
            $table->foreignId('current_track_id')->nullable()->constrained('tracks')->onDelete('set null');
            $table->integer('current_position')->default(0); // seconds
            $table->boolean('is_active')->default(true);
            $table->integer('max_participants')->default(10);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('group_session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('group_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('joined_at');
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_session_participants');
        Schema::dropIfExists('group_sessions');
        Schema::dropIfExists('friend_activities');

        if (Schema::hasColumn('playlists', 'folder_id')) {
            Schema::table('playlists', function (Blueprint $table) {
                $table->dropForeign(['folder_id']);
                $table->dropColumn(['folder_id', 'is_smart']);
            });
        }

        Schema::dropIfExists('playlist_folders');
        Schema::dropIfExists('search_history');
        Schema::dropIfExists('artist_analytics');
        Schema::dropIfExists('audio_fingerprints');
        Schema::dropIfExists('dmca_takedowns');
        Schema::dropIfExists('content_reports');
        Schema::dropIfExists('smart_playlists');
    }
};
