<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., English
            $table->string('code')->unique(); // e.g., en
            $table->string('flag')->nullable(); // e.g., 🇺🇸
            $table->boolean('is_rtl')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('group')->default('messages'); // e.g., auth, validation, messages
            $table->string('key'); // e.g., welcome_message
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['language_id', 'group', 'key']);
        });

        // Seed default English language
        DB::table('languages')->insert([
            'name' => 'English',
            'code' => 'en',
            'flag' => '🇺🇸',
            'is_rtl' => false,
            'is_active' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('languages');
    }
};
