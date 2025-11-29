<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Theme/Branding Settings
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, image, color, boolean
            $table->string('group')->default('general'); // general, branding, pwa, colors
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // new_follower, new_comment, new_like, track_uploaded, etc.
            $table->text('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data (track_id, user_id, etc.)
            $table->string('icon')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
        });

        // Notification Preferences
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Email notifications
            $table->boolean('email_new_follower')->default(true);
            $table->boolean('email_new_comment')->default(true);
            $table->boolean('email_new_like')->default(true);
            $table->boolean('email_track_uploaded')->default(true);
            $table->boolean('email_new_message')->default(true);
            $table->boolean('email_event_reminder')->default(true);
            $table->boolean('email_weekly_digest')->default(true);

            // Push notifications
            $table->boolean('push_new_follower')->default(true);
            $table->boolean('push_new_comment')->default(true);
            $table->boolean('push_new_like')->default(true);
            $table->boolean('push_new_message')->default(true);

            // In-app notifications
            $table->boolean('app_new_follower')->default(true);
            $table->boolean('app_new_comment')->default(true);
            $table->boolean('app_new_like')->default(true);
            $table->boolean('app_new_message')->default(true);

            $table->timestamps();

            $table->unique('user_id');
        });

        // Push Notification Subscriptions
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('endpoint')->unique();
            $table->text('public_key')->nullable();
            $table->text('auth_token')->nullable();
            $table->string('device_type')->nullable(); // web, android, ios
            $table->timestamps();

            $table->index('user_id');
        });

        // User Privacy Settings
        Schema::create('user_privacy_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Profile visibility
            $table->string('profile_visibility')->default('public'); // public, followers, private
            $table->boolean('show_email')->default(false);
            $table->boolean('show_listening_activity')->default(true);
            $table->boolean('show_playlists')->default(true);
            $table->boolean('show_followers')->default(true);

            // Content preferences
            $table->boolean('hide_explicit_content')->default(false);
            $table->boolean('safe_mode')->default(false);
            $table->json('blocked_users')->nullable();
            $table->json('muted_words')->nullable();

            // Data & privacy
            $table->boolean('allow_personalized_ads')->default(true);
            $table->boolean('allow_analytics')->default(true);
            $table->boolean('allow_third_party_sharing')->default(false);

            $table->timestamps();

            $table->unique('user_id');
        });

        // Referral System
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('referral_code')->unique();
            $table->string('email')->nullable(); // Email of invited person
            $table->string('status')->default('pending'); // pending, registered, completed
            $table->decimal('reward_amount', 8, 2)->default(0);
            $table->boolean('reward_claimed')->default(false);
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('referrer_id');
            $table->index('referral_code');
        });

        // Social Logins
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // google, facebook, apple, twitter
            $table->string('provider_id');
            $table->string('provider_token')->nullable();
            $table->string('provider_refresh_token')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('user_privacy_settings');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('theme_settings');
    }
};
