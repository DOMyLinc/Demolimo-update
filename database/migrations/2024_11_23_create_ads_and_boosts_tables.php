<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Advertisements (Like Facebook Ads)
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('target_url');
            $table->string('placement')->default('sidebar'); // sidebar, feed_banner
            $table->decimal('budget', 10, 2);
            $table->integer('clicks')->default(0);
            $table->integer('views')->default(0);
            $table->enum('status', ['pending', 'active', 'rejected', 'completed'])->default('pending');
            $table->timestamps();
        });

        // Boosts (Promoting specific tracks)
        Schema::create('boosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('track_id')->constrained()->onDelete('cascade'); // Assuming you have a tracks table
            $table->decimal('budget', 10, 2);
            $table->integer('target_views');
            $table->integer('current_views')->default(0);
            $table->enum('status', ['pending', 'active', 'completed'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boosts');
        Schema::dropIfExists('advertisements');
    }
};
