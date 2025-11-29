<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Song Battle Rewards
        Schema::create('song_battle_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_battle_id')->constrained()->onDelete('cascade');
            $table->foreignId('winner_version_id')->constrained('song_battle_versions')->onDelete('cascade');
            $table->foreignId('winner_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('reward_type', ['cash', 'points', 'premium_subscription', 'custom'])->default('cash');
            $table->decimal('cash_amount', 10, 2)->nullable();
            $table->integer('points_amount')->nullable();
            $table->integer('premium_days')->nullable(); // Days of premium subscription
            $table->text('custom_reward_description')->nullable();
            $table->enum('status', ['pending', 'awarded', 'claimed'])->default('pending');
            $table->foreignId('awarded_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who awarded
            $table->timestamp('awarded_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Artist Donations
        Schema::create('artist_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('donor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('donor_name')->nullable(); // For anonymous or guest donations
            $table->string('donor_email')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['one_time', 'monthly', 'yearly'])->default('one_time');
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable(); // stripe, paypal, etc.
            $table->string('transaction_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // Artist Tips
        Schema::create('artist_tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tipper_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('tipper_name')->nullable();
            $table->decimal('amount', 10, 2);
            $table->morphs('tippable'); // Can tip on tracks, albums, events, etc.
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // Gifts System (for sending gifts to artists from admin or users)
        Schema::create('artist_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('gift_type', ['cash', 'points', 'premium_subscription', 'custom'])->default('cash');
            $table->decimal('cash_amount', 10, 2)->nullable();
            $table->integer('points_amount')->nullable();
            $table->integer('premium_days')->nullable();
            $table->text('custom_gift_description')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'sent', 'claimed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
        });

        // Donation Settings (per artist)
        Schema::create('artist_donation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('donations_enabled')->default(true);
            $table->boolean('tips_enabled')->default(true);
            $table->decimal('minimum_donation', 10, 2)->default(1.00);
            $table->decimal('minimum_tip', 10, 2)->default(0.50);
            $table->json('suggested_amounts')->nullable(); // [5, 10, 20, 50]
            $table->text('donation_message')->nullable(); // Custom message to donors
            $table->string('paypal_email')->nullable();
            $table->string('stripe_account_id')->nullable();
            $table->decimal('platform_fee_percentage', 5, 2)->default(5.00); // Platform takes %
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_donation_settings');
        Schema::dropIfExists('artist_gifts');
        Schema::dropIfExists('artist_tips');
        Schema::dropIfExists('artist_donations');
        Schema::dropIfExists('song_battle_rewards');
    }
};
