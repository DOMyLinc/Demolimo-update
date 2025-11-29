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
        Schema::create('translation_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'profile', 'dashboard', 'upload_track'
            $table->string('group')->index(); // e.g., 'navigation', 'buttons', 'messages'
            $table->text('description')->nullable(); // What this key is used for
            $table->timestamps();

            // Indexes for performance
            $table->index(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_keys');
    }
};
