<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('colors')->nullable();
            $table->string('icon')->nullable();
            $table->string('preview_image')->nullable();
            $table->timestamps();
        });

        // Seed default themes
        DB::table('theme_settings')->insert([
            [
                'key' => 'lava',
                'name' => 'Lava',
                'description' => 'Fiery red and orange theme with volcanic energy',
                'is_active' => false,
                'icon' => '🔥',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default',
                'name' => 'Purple Dream',
                'description' => 'Modern purple and blue gradient theme',
                'is_active' => true,
                'icon' => '💜',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ocean',
                'name' => 'Ocean Blue',
                'description' => 'Cool and calming ocean-inspired theme',
                'is_active' => false,
                'icon' => '🌊',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'forest',
                'name' => 'Forest Green',
                'description' => 'Natural and earthy forest theme',
                'is_active' => false,
                'icon' => '🌲',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sunset',
                'name' => 'Sunset Glow',
                'description' => 'Warm sunset colors with orange and pink',
                'is_active' => false,
                'icon' => '🌅',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
