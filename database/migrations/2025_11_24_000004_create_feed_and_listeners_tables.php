<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Feed Posts Table
        Schema::create('feed_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('postable'); // Can be Track, Album, SongBattle, Event, etc.
            $table->text('content')->nullable();
            $table->string('type')->default('share'); // share, announcement, update
            $table->boolean('is_pinned')->default(false);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['postable_type', 'postable_id']);
        });

        // Feed Likes Table
        Schema::create('feed_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['feed_post_id', 'user_id']);
        });

        // Feed Comments Table
        Schema::create('feed_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->integer('likes_count')->default(0);
            $table->timestamps();
        });

        // Listeners Tracking Table
        Schema::create('listeners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->morphs('listenable'); // Track, Album, RadioStation
            $table->string('session_id')->unique();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->default(0); // seconds
            $table->timestamps();

            $table->index(['listenable_type', 'listenable_id']);
            $table->index('started_at');
        });

        // Song Battle Shares Table (for zipcode/feed sharing)
        Schema::create('song_battle_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_battle_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('share_type'); // feed, zipcode, social
            $table->foreignId('zipcode_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('views')->default(0);
            $table->integer('clicks')->default(0);
            $table->timestamps();

            $table->index(['song_battle_id', 'share_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('song_battle_shares');
        Schema::dropIfExists('listeners');
        Schema::dropIfExists('feed_comments');
        Schema::dropIfExists('feed_likes');
        Schema::dropIfExists('feed_posts');
    }
};
