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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_key_id')->constrained()->onDelete('cascade');
            $table->string('locale', 5)->default('en'); // en, es, fr, de, etc.
            $table->text('value'); // The translated text
            $table->timestamps();

            // Unique constraint: one translation per key per locale
            $table->unique(['translation_key_id', 'locale']);

            // Indexes for performance
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
