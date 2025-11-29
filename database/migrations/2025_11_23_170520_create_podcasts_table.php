<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_explicit')->default(false);
            $table->string('language')->default('en');
            $table->string('author_name');
            $table->string('author_email')->nullable();
            $table->string('website_url')->nullable();
            $table->string('rss_feed_url')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->timestamp('approved_at')->nullable();
            $table->integer('total_episodes')->default(0);
            $table->integer('total_plays')->default(0);
            $table->integer('subscribers_count')->default(0);
            $table->timestamps();
        });

        Schema::create('podcast_episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('podcast_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('audio_file');
            $table->integer('duration')->default(0); // in seconds
            $table->integer('file_size')->default(0); // in bytes
            $table->integer('episode_number')->nullable();
            $table->integer('season_number')->nullable();
            $table->boolean('is_explicit')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->integer('play_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->timestamps();

            $table->unique(['podcast_id', 'slug']);
        });

        Schema::create('podcast_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('podcast_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['podcast_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('podcast_subscribers');
        Schema::dropIfExists('podcast_episodes');
        Schema::dropIfExists('podcasts');
    }
};
