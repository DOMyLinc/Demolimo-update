<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('boosts', function (Blueprint $table) {
            // Check if track_id exists before trying to drop it
            if (Schema::hasColumn('boosts', 'track_id')) {
                $table->dropForeign(['track_id']);
                $table->dropColumn('track_id');
            }

            // Add polymorphic columns
            $table->unsignedBigInteger('boostable_id')->after('user_id');
            $table->string('boostable_type')->after('boostable_id');

            // Add more boost fields
            $table->string('package')->nullable()->after('budget');
            $table->integer('target_impressions')->default(0)->after('target_views');
            $table->integer('current_impressions')->default(0)->after('current_views');
            $table->decimal('cost', 10, 2)->default(0)->after('budget');
            $table->boolean('is_active')->default(true)->after('status');

            // Indexes
            $table->index(['boostable_type', 'boostable_id']);
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boosts', function (Blueprint $table) {
            // Remove polymorphic columns
            $table->dropIndex(['boostable_type', 'boostable_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['is_active']);

            $table->dropColumn([
                'boostable_id',
                'boostable_type',
                'package',
                'target_impressions',
                'current_impressions',
                'cost',
                'is_active'
            ]);

            // Restore track_id
            $table->foreignId('track_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
