<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Feature Permissions Table
        Schema::create('feature_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('feature_name')->unique(); // upload_tracks, create_events, sell_tickets, etc.
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->boolean('free_plan')->default(false);
            $table->boolean('pro_plan')->default(true);
            $table->boolean('premium_plan')->default(true);
            $table->json('limits')->nullable(); // JSON for plan-specific limits
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User Feature Access Table (for custom permissions)
        Schema::create('user_feature_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('feature_permission_id')->constrained()->onDelete('cascade');
            $table->boolean('is_granted')->default(true);
            $table->json('custom_limits')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'feature_permission_id']);
        });

        // Platform Settings Table (Enhanced)
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // general, payment, storage, features, etc.
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, boolean, number, json
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can users see this?
            $table->timestamps();
        });

        // Commission Settings Table
        Schema::create('commission_settings', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type'); // ticket_sale, track_sale, subscription
            $table->decimal('platform_commission_percentage', 5, 2)->default(10.00);
            $table->decimal('minimum_payout', 10, 2)->default(50.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Payouts Table
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('payment_method'); // paypal, stripe, bank_transfer
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('commission_settings');
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('user_feature_access');
        Schema::dropIfExists('feature_permissions');
    }
};
