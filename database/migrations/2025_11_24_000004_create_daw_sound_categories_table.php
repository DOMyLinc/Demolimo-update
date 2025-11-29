<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('daw_sound_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default categories
        $categories = [
            ['name' => 'Drums', 'slug' => 'drums', 'icon' => '🥁', 'description' => 'Drum samples and loops', 'sort_order' => 1],
            ['name' => 'Bass', 'slug' => 'bass', 'icon' => '🎸', 'description' => 'Bass sounds and loops', 'sort_order' => 2],
            ['name' => 'Synth', 'slug' => 'synth', 'icon' => '🎹', 'description' => 'Synthesizer sounds', 'sort_order' => 3],
            ['name' => 'FX', 'slug' => 'fx', 'icon' => '✨', 'description' => 'Sound effects', 'sort_order' => 4],
            ['name' => 'Vocals', 'slug' => 'vocals', 'icon' => '🎤', 'description' => 'Vocal samples', 'sort_order' => 5],
            ['name' => 'Loops', 'slug' => 'loops', 'icon' => '🔁', 'description' => 'Musical loops', 'sort_order' => 6],
            ['name' => 'Percussion', 'slug' => 'percussion', 'icon' => '🎵', 'description' => 'Percussion sounds', 'sort_order' => 7],
            ['name' => 'Ambient', 'slug' => 'ambient', 'icon' => '🌊', 'description' => 'Ambient sounds', 'sort_order' => 8],
        ];

        foreach ($categories as $category) {
            DB::table('daw_sound_categories')->insert(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('daw_sound_categories');
    }
};
