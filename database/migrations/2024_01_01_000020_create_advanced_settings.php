<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Advanced Platform Settings
        Schema::create('advanced_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // cache, performance, limits, monetization, etc.
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, number, boolean, json
            $table->string('label');
            $table->text('description')->nullable();
            $table->text('options')->nullable(); // For select/radio inputs
            $table->boolean('is_public')->default(false); // Can users see this?
            $table->timestamps();
        });

        // Cache Settings
        Schema::create('cache_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enable_cache')->default(true);
            $table->string('cache_driver')->default('file'); // file, redis, memcached
            $table->integer('cache_ttl')->default(3600); // seconds
            $table->boolean('cache_views')->default(true);
            $table->boolean('cache_queries')->default(true);
            $table->boolean('cache_routes')->default(true);
            $table->boolean('cache_config')->default(true);
            $table->timestamps();
        });

        // Upload Limits & Restrictions
        Schema::create('upload_limits', function (Blueprint $table) {
            $table->id();
            $table->string('user_type'); // free, pro, premium, admin
            $table->integer('max_file_size')->default(10); // MB
            $table->integer('max_tracks_per_day')->default(5);
            $table->integer('max_tracks_total')->default(50);
            $table->integer('max_storage')->default(1000); // MB
            $table->string('allowed_formats')->default('mp3,wav,flac');
            $table->integer('max_track_duration')->default(600); // seconds
            $table->boolean('can_upload_video')->default(false);
            $table->boolean('can_sell_tracks')->default(false);
            $table->boolean('can_create_events')->default(false);
            $table->timestamps();
        });

        // Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('subject');
            $table->text('body');
            $table->json('variables')->nullable(); // Available variables
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // System Logs
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level'); // info, warning, error, critical
            $table->string('category'); // auth, payment, upload, etc.
            $table->text('message');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['level', 'created_at']);
            $table->index(['category', 'created_at']);
        });

        // API Keys (For integrations)
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('key')->unique();
            $table->string('secret')->nullable();
            $table->json('permissions')->nullable(); // What can this key do?
            $table->integer('rate_limit')->default(1000); // Requests per hour
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Scheduled Tasks
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('command');
            $table->string('schedule'); // cron expression
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->text('last_output')->nullable();
            $table->timestamps();
        });

        // Backup Settings
        Schema::create('backup_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('auto_backup')->default(false);
            $table->string('frequency')->default('daily'); // daily, weekly, monthly
            $table->integer('retention_days')->default(30);
            $table->string('storage_location')->default('local'); // local, s3, dropbox
            $table->json('backup_items')->nullable(); // database, files, etc.
            $table->timestamp('last_backup_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_settings');
        Schema::dropIfExists('scheduled_tasks');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('upload_limits');
        Schema::dropIfExists('cache_settings');
        Schema::dropIfExists('advanced_settings');
    }
};
