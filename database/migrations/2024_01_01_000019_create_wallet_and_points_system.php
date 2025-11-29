<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Point Packages (Admin can create/edit)
        Schema::create('point_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('points');
            $table->decimal('price', 10, 2); // USD price
            $table->decimal('bonus_points', 10, 2)->default(0); // Extra bonus points
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->json('features')->nullable(); // Extra features
            $table->timestamps();
        });

        // User Wallets
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('balance', 15, 2)->default(0); // Cash balance in USD
            $table->integer('points')->default(0); // Points balance
            $table->decimal('pending_balance', 15, 2)->default(0); // Pending withdrawals
            $table->decimal('total_earned', 15, 2)->default(0); // Lifetime earnings
            $table->decimal('total_withdrawn', 15, 2)->default(0); // Lifetime withdrawals
            $table->decimal('total_spent', 15, 2)->default(0); // Lifetime spending
            $table->timestamps();
        });

        // Wallet Transactions
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // credit, debit, withdrawal, purchase, earning, refund
            $table->decimal('amount', 15, 2);
            $table->integer('points')->default(0); // If points transaction
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('description');
            $table->string('reference_type')->nullable(); // Track, Event, Subscription, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('status')->default('completed'); // pending, completed, failed, cancelled
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Withdrawal Requests
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('method'); // paypal, bank_transfer, stripe, etc.
            $table->json('payment_details'); // Account info
            $table->string('status')->default('pending'); // pending, processing, completed, rejected
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // Point Transactions
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // earn, spend, purchase, bonus, admin_add, admin_remove
            $table->integer('points');
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->string('description');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });

        // Monetization Requests (Artist verification, monetization enable)
        Schema::create('monetization_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // verification, monetization, pro_upgrade
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->json('documents')->nullable(); // ID, proof, etc.
            $table->text('message')->nullable();
            $table->text('admin_response')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // Earnings (Track plays, downloads, etc.)
        Schema::create('earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('earnable_type'); // Track, Event, etc.
            $table->unsignedBigInteger('earnable_id');
            $table->decimal('amount', 10, 4); // Small amounts per play
            $table->string('type'); // play, download, subscription, tip, etc.
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_paid']);
            $table->index(['earnable_type', 'earnable_id']);
        });

        // Add monetization columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_monetization_enabled')->default(false)->after('is_verified');
            $table->boolean('is_pro')->default(false)->after('is_monetization_enabled');
            $table->timestamp('pro_expires_at')->nullable()->after('is_pro');
            $table->decimal('earning_rate', 5, 4)->default(0.0001)->after('pro_expires_at'); // Per play
            $table->integer('minimum_withdrawal')->default(50)->after('earning_rate'); // Minimum $50
        });

        // Upgrade Prompts (For free users)
        Schema::create('upgrade_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('trigger'); // upload_limit, storage_limit, feature_access, etc.
            $table->string('title');
            $table->text('message');
            $table->string('cta_text')->default('Upgrade to Pro');
            $table->string('cta_link')->default('/pricing');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upgrade_prompts');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_monetization_enabled',
                'is_pro',
                'pro_expires_at',
                'earning_rate',
                'minimum_withdrawal'
            ]);
        });
        Schema::dropIfExists('earnings');
        Schema::dropIfExists('monetization_requests');
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('point_packages');
    }
};
