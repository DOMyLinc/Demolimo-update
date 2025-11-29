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
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->date('release_date')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('album_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->text('lyrics')->nullable();
            $table->string('audio_path');
            $table->json('waveform_data')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('duration')->default(0); // seconds
            $table->integer('file_size')->default(0); // bytes
            $table->integer('bitrate')->default(0); // kbps
            $table->bigInteger('plays')->default(0);
            $table->bigInteger('downloads')->default(0);
            $table->bigInteger('likes')->default(0);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_downloadable')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->json('tags')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('playlist_track', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_track');
        Schema::dropIfExists('playlists');
        Schema::dropIfExists('tracks');
        Schema::dropIfExists('albums');
    }
};
