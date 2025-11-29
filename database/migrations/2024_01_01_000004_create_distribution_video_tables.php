<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('album_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('status')->default('pending');
            $table->date('release_date')->nullable();
            $table->string('upc')->nullable();
            $table->string('isrc')->nullable();
            $table->decimal('earnings', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('video_path');
            $table->string('thumbnail_path')->nullable();
            $table->integer('duration')->default(0);
            $table->bigInteger('views')->default(0);
            $table->bigInteger('likes')->default(0);
            $table->boolean('is_public')->default(true);
            $table->string('status')->default('processing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
        Schema::dropIfExists('distributions');
    }
};
