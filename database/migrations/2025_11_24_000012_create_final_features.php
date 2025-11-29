<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Mobile App Settings
        Schema::create('mobile_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('car_mode_enabled')->default(false);
            $table->integer('sleep_timer_minutes')->nullable();
            $table->boolean('data_saver_mode')->default(false);
            $table->string('audio_quality_mobile')->default('normal'); // low, normal, high
            $table->boolean('download_over_wifi_only')->default(true);
            $table->boolean('shake_to_skip')->default(false);
            $table->integer('shake_sensitivity')->default(5); // 1-10
            $table->timestamps();

            $table->unique('user_id');
        });

        // API Tokens (OAuth)
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('token', 80)->unique();
            $table->text('abilities')->nullable(); // JSON array of permissions
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        // API Applications (for third-party developers)
        Schema::create('api_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('client_id')->unique();
            $table->string('client_secret');
            $table->text('redirect_uris')->nullable(); // JSON array
            $table->string('website_url')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('rate_limit')->default(1000); // requests per hour
            $table->timestamps();
        });

        // Webhooks
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('url');
            $table->string('secret')->nullable();
            $table->json('events'); // Array of events to listen to
            $table->boolean('is_active')->default(true);
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });

        // Webhook Logs
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->onDelete('cascade');
            $table->string('event');
            $table->text('payload')->nullable();
            $table->integer('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->boolean('success')->default(false);
            $table->timestamps();

            $table->index(['webhook_id', 'created_at']);
        });

        // Blog Posts
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('status')->default('draft'); // draft, published, scheduled
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->onDelete('set null');
            $table->json('tags')->nullable();
            $table->integer('views')->default(0);
            $table->integer('reading_time')->default(0); // minutes
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index('slug');
        });

        // Blog Categories
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Blog Comments
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('blog_posts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->text('content');
            $table->boolean('is_approved')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('blog_comments')->onDelete('cascade');
            $table->timestamps();

            $table->index(['post_id', 'is_approved']);
        });

        // Import/Export History
        Schema::create('import_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('source'); // spotify, apple_music, youtube_music
            $table->string('type'); // playlists, tracks, favorites
            $table->integer('total_items')->default(0);
            $table->integer('imported_items')->default(0);
            $table->integer('failed_items')->default(0);
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('error_log')->nullable();
            $table->timestamps();
        });

        // User Activity Log
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // login, logout, upload, purchase, etc.
            $table->string('description');
            $table->morphs('subject'); // The model being acted upon
            $table->json('properties')->nullable(); // Additional data
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('import_history');
        Schema::dropIfExists('blog_comments');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('api_applications');
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('mobile_settings');
    }
};
