<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('daw_sounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('daw_sound_categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->bigInteger('file_size')->default(0); // in bytes
            $table->integer('duration')->nullable(); // in seconds
            $table->string('format', 10); // mp3, wav, ogg, etc.
            $table->integer('bpm')->nullable();
            $table->string('key')->nullable(); // Musical key (C, D, E, etc.)
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->integer('download_count')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('category_id');
            $table->index('is_active');
            $table->index('format');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daw_sounds');
    }
};
