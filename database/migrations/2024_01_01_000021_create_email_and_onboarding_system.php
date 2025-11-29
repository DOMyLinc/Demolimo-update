<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Email Templates (Admin manageable)
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // welcome, verification, password_reset, etc.
            $table->string('slug')->unique();
            $table->string('subject');
            $table->text('body'); // HTML content
            $table->json('variables')->nullable(); // Available variables like {user_name}, {link}
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('category')->default('general'); // general, transactional, marketing
            $table->timestamps();
        });

        // Email Queue
        Schema::create('email_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->string('template_slug')->nullable();
            $table->json('variables')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Email Settings
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('driver')->default('smtp'); // smtp, sendmail, mailgun, ses, etc.
            $table->string('host')->nullable();
            $table->integer('port')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('encryption')->nullable(); // tls, ssl
            $table->string('from_address');
            $table->string('from_name');
            $table->string('logo_url')->nullable();
            $table->boolean('use_queue')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Onboarding Tutorials (Admin manageable)
        Schema::create('onboarding_tutorials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('content'); // HTML or markdown
            $table->string('target_element')->nullable(); // CSS selector for highlighting
            $table->string('position')->default('bottom'); // top, bottom, left, right
            $table->integer('step_order')->default(0);
            $table->string('user_type')->default('all'); // all, free, pro, artist
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable();
            $table->string('video_url')->nullable();
            $table->timestamps();
        });

        // User Tutorial Progress
        Schema::create('user_tutorial_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tutorial_id')->constrained('onboarding_tutorials')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_skipped')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'tutorial_id']);
        });

        // Welcome Messages (Admin manageable)
        Schema::create('welcome_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('user_type')->default('all'); // all, free, pro, artist
            $table->string('display_type')->default('modal'); // modal, banner, notification
            $table->boolean('show_once')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(10);
            $table->string('button_text')->default('Get Started');
            $table->string('button_link')->nullable();
            $table->timestamps();
        });

        // User Verification Tracking
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->change();
            $table->string('verification_token')->nullable()->after('email_verified_at');
            $table->timestamp('verification_sent_at')->nullable()->after('verification_token');
            $table->integer('verification_attempts')->default(0)->after('verification_sent_at');
        });

        // AI Music Generation Settings
        Schema::create('ai_music_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Suno, Udio, MusicGen, etc.
            $table->string('slug')->unique();
            $table->string('api_endpoint');
            $table->string('api_key')->nullable();
            $table->decimal('cost_per_generation', 10, 4)->default(0); // Cost in USD
            $table->integer('max_duration')->default(180); // seconds
            $table->json('supported_styles')->nullable();
            $table->json('settings')->nullable(); // Provider-specific settings
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(10);
            $table->timestamps();
        });

        // AI Music Generation Models
        Schema::create('ai_music_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('ai_music_providers')->onDelete('cascade');
            $table->string('name');
            $table->string('model_id'); // Provider's model identifier
            $table->text('description')->nullable();
            $table->decimal('price_per_generation', 10, 4); // Price in USD or points
            $table->string('currency')->default('usd'); // usd, points
            $table->integer('max_duration')->default(60); // seconds
            $table->json('capabilities')->nullable(); // vocals, instrumental, etc.
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // User AI Generations
        Schema::create('ai_music_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('model_id')->constrained('ai_music_models')->onDelete('cascade');
            $table->string('prompt');
            $table->json('parameters')->nullable(); // style, mood, tempo, etc.
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('generation_id')->nullable(); // Provider's generation ID
            $table->string('audio_url')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('duration')->nullable();
            $table->decimal('cost', 10, 4)->default(0);
            $table->string('payment_method')->nullable(); // points, cash
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // Storage Settings
        Schema::create('storage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('driver')->default('local'); // local, s3, spaces, wasabi, etc.
            $table->string('name');
            $table->json('credentials')->nullable(); // API keys, secrets
            $table->string('bucket')->nullable();
            $table->string('region')->nullable();
            $table->string('endpoint')->nullable();
            $table->string('url')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->string('use_for')->default('all'); // all, tracks, images, videos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_settings');
        Schema::dropIfExists('ai_music_generations');
        Schema::dropIfExists('ai_music_models');
        Schema::dropIfExists('ai_music_providers');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['verification_token', 'verification_sent_at', 'verification_attempts']);
        });

        Schema::dropIfExists('welcome_messages');
        Schema::dropIfExists('user_tutorial_progress');
        Schema::dropIfExists('onboarding_tutorials');
        Schema::dropIfExists('email_settings');
        Schema::dropIfExists('email_queue');
        Schema::dropIfExists('email_templates');
    }
};
