<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('version')->default('1.0.0');
            $table->string('author')->nullable();

            // Theme Configuration
            $table->string('screenshot')->nullable();
            $table->json('color_scheme')->nullable();
            $table->json('features')->nullable();

            // Status
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('supports_landing_page')->default(true);
            $table->boolean('supports_admin')->default(false);

            // Paths
            $table->string('views_path')->nullable();
            $table->string('assets_path')->nullable();

            $table->timestamps();
        });

        // Insert default themes
        $themes = [
            [
                'name' => 'lava',
                'display_name' => 'Lava',
                'description' => 'Modern and vibrant theme with gradient effects',
                'version' => '1.0.0',
                'author' => 'DemoLimo',
                'is_active' => true,
                'is_default' => true,
                'supports_landing_page' => true,
                'supports_admin' => true,
                'views_path' => 'themes/lava',
                'assets_path' => 'themes/lava/assets',
                'color_scheme' => json_encode([
                    'primary' => '#FF6B6B',
                    'secondary' => '#4ECDC4',
                    'accent' => '#FFE66D',
                    'background' => '#1A1A2E',
                    'text' => '#FFFFFF',
                ]),
                'features' => json_encode(['animations', 'gradients', 'dark_mode']),
            ],
            [
                'name' => 'modern',
                'display_name' => 'Modern',
                'description' => 'Clean and minimal modern design',
                'version' => '1.0.0',
                'author' => 'DemoLimo',
                'is_active' => false,
                'is_default' => false,
                'supports_landing_page' => true,
                'supports_admin' => false,
                'views_path' => 'themes/modern',
                'assets_path' => 'themes/modern/assets',
                'color_scheme' => json_encode([
                    'primary' => '#2563EB',
                    'secondary' => '#10B981',
                    'accent' => '#F59E0B',
                    'background' => '#FFFFFF',
                    'text' => '#1F2937',
                ]),
                'features' => json_encode(['responsive', 'minimal', 'light_mode']),
            ],
            [
                'name' => 'classic',
                'display_name' => 'Classic',
                'description' => 'Traditional and professional design',
                'version' => '1.0.0',
                'author' => 'DemoLimo',
                'is_active' => false,
                'is_default' => false,
                'supports_landing_page' => true,
                'supports_admin' => false,
                'views_path' => 'themes/classic',
                'assets_path' => 'themes/classic/assets',
                'color_scheme' => json_encode([
                    'primary' => '#1E40AF',
                    'secondary' => '#059669',
                    'accent' => '#DC2626',
                    'background' => '#F9FAFB',
                    'text' => '#111827',
                ]),
                'features' => json_encode(['professional', 'stable', 'accessible']),
            ],
            [
                'name' => 'dark',
                'display_name' => 'Dark',
                'description' => 'Sleek dark theme for night owls',
                'version' => '1.0.0',
                'author' => 'DemoLimo',
                'is_active' => false,
                'is_default' => false,
                'supports_landing_page' => true,
                'supports_admin' => false,
                'views_path' => 'themes/dark',
                'assets_path' => 'themes/dark/assets',
                'color_scheme' => json_encode([
                    'primary' => '#8B5CF6',
                    'secondary' => '#EC4899',
                    'accent' => '#14B8A6',
                    'background' => '#0F172A',
                    'text' => '#F1F5F9',
                ]),
                'features' => json_encode(['dark_mode', 'high_contrast', 'modern']),
            ],
        ];

        foreach ($themes as $theme) {
            DB::table('themes')->insert(array_merge($theme, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('themes');
    }
};
