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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['image', 'audio', 'text']);
            $table->text('content'); // Image URL, Audio URL, or Text content
            $table->string('link')->nullable(); // Click-through URL
            $table->json('placement_zones'); // ['landing_hero', 'player', 'global']
            $table->enum('target_audience', ['all', 'free', 'pro'])->default('all');
            $table->enum('status', ['draft', 'scheduled', 'published', 'expired'])->default('draft');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('priority')->default(0); // Higher = shown first
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
