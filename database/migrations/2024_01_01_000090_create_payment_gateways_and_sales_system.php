<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Payment Gateways Configuration
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Stripe, PayPal, Bank Transfer, etc.
            $table->string('slug')->unique(); // stripe, paypal, bank_transfer
            $table->enum('type', ['automatic', 'manual'])->default('automatic');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->json('credentials')->nullable(); // API keys, secrets, etc.
            $table->json('settings')->nullable(); // Additional settings
            $table->decimal('fixed_fee', 10, 2)->default(0); // Fixed fee per transaction
            $table->decimal('percentage_fee', 5, 2)->default(0); // Percentage fee
            $table->decimal('min_amount', 10, 2)->default(0);
            $table->decimal('max_amount', 10, 2)->nullable();
            $table->json('supported_currencies')->nullable();
            $table->text('instructions')->nullable(); // For manual gateways
            $table->integer('processing_time')->default(0); // In hours
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Payment Transactions
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_gateway_id')->constrained()->onDelete('cascade');
            $table->morphs('payable'); // Track, Album, Donation, Tip, etc.
            $table->enum('type', ['track_purchase', 'album_purchase', 'donation', 'tip', 'subscription', 'points', 'other']);
            $table->decimal('amount', 10, 2);
            $table->decimal('gateway_fee', 10, 2)->default(0);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('artist_amount', 10, 2); // Amount artist receives
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->text('payment_details')->nullable(); // Gateway response
            $table->text('manual_payment_proof')->nullable(); // For manual gateways
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['payable_type', 'payable_id']);
        });

        // Track Sales
        Schema::create('track_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payment_transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('price', 10, 2);
            $table->decimal('platform_fee', 10, 2);
            $table->decimal('gateway_fee', 10, 2);
            $table->decimal('seller_earnings', 10, 2);
            $table->string('license_type')->default('standard'); // standard, exclusive, etc.
            $table->text('license_terms')->nullable();
            $table->string('download_token')->unique();
            $table->integer('download_count')->default(0);
            $table->integer('max_downloads')->default(3);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['buyer_id', 'track_id']);
        });

        // Album Sales
        Schema::create('album_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payment_transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('price', 10, 2);
            $table->decimal('platform_fee', 10, 2);
            $table->decimal('gateway_fee', 10, 2);
            $table->decimal('seller_earnings', 10, 2);
            $table->string('license_type')->default('standard');
            $table->text('license_terms')->nullable();
            $table->string('download_token')->unique();
            $table->integer('download_count')->default(0);
            $table->integer('max_downloads')->default(3);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['buyer_id', 'album_id']);
        });

        // Fee Settings
        Schema::create('fee_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // track_sale, album_sale, donation, tip, etc.
            $table->decimal('platform_fee_percentage', 5, 2)->default(10.00);
            $table->decimal('platform_fee_fixed', 10, 2)->default(0);
            $table->decimal('min_platform_fee', 10, 2)->default(0);
            $table->decimal('max_platform_fee', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique('type');
        });

        // Manual Payment Verifications
        Schema::create('manual_payment_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('proof_image')->nullable();
            $table->text('proof_document')->nullable();
            $table->text('transaction_reference')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // Add price fields to tracks table if not exists
        if (!Schema::hasColumn('tracks', 'is_for_sale')) {
            Schema::table('tracks', function (Blueprint $table) {
                $table->boolean('is_for_sale')->default(false)->after('file_path');
                $table->decimal('price', 10, 2)->nullable()->after('is_for_sale');
                $table->boolean('allow_free_download')->default(true)->after('price');
                $table->integer('total_sales')->default(0)->after('allow_free_download');
                $table->decimal('total_revenue', 10, 2)->default(0)->after('total_sales');
            });
        }

        // Add price fields to albums table if not exists
        if (!Schema::hasColumn('albums', 'is_for_sale')) {
            Schema::table('albums', function (Blueprint $table) {
                $table->boolean('is_for_sale')->default(false)->after('cover');
                $table->decimal('price', 10, 2)->nullable()->after('is_for_sale');
                $table->integer('total_sales')->default(0)->after('price');
                $table->decimal('total_revenue', 10, 2)->default(0)->after('total_sales');
            });
        }
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn(['is_for_sale', 'price', 'total_sales', 'total_revenue']);
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn(['is_for_sale', 'price', 'allow_free_download', 'total_sales', 'total_revenue']);
        });

        Schema::dropIfExists('manual_payment_verifications');
        Schema::dropIfExists('fee_settings');
        Schema::dropIfExists('album_sales');
        Schema::dropIfExists('track_sales');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_gateways');
    }
};
