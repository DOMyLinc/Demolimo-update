<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Track Blockchain Valuation
        Schema::create('track_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->onDelete('cascade');
            $table->decimal('current_value', 10, 2)->default(0.00);
            $table->decimal('initial_value', 10, 2)->default(1.00);
            $table->decimal('peak_value', 10, 2)->default(1.00);
            $table->decimal('lowest_value', 10, 2)->default(1.00);
            $table->integer('total_views')->default(0);
            $table->integer('total_plays')->default(0);
            $table->integer('total_likes')->default(0);
            $table->integer('total_shares')->default(0);
            $table->integer('total_downloads')->default(0);
            $table->decimal('engagement_score', 8, 4)->default(0.0000);
            $table->decimal('trending_score', 8, 4)->default(0.0000);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
        });

        // Valuation History
        Schema::create('valuation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->onDelete('cascade');
            $table->decimal('value', 10, 2);
            $table->decimal('change_percentage', 8, 4);
            $table->string('change_reason')->nullable(); // views, plays, likes, shares
            $table->json('metrics')->nullable(); // Snapshot of metrics at this time
            $table->timestamps();
        });

        // Track Investments (Users can invest in tracks)
        Schema::create('track_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('track_id')->constrained()->onDelete('cascade');
            $table->decimal('invested_amount', 10, 2);
            $table->decimal('purchase_value', 10, 2); // Value at time of investment
            $table->integer('shares')->default(1);
            $table->decimal('current_value', 10, 2)->default(0.00);
            $table->decimal('profit_loss', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
        });

        // Blockchain Settings
        Schema::create('blockchain_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blockchain_settings');
        Schema::dropIfExists('track_investments');
        Schema::dropIfExists('valuation_history');
        Schema::dropIfExists('track_valuations');
    }
};
