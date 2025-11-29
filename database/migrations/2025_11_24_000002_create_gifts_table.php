<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable(); // Emoji or image path
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default gifts
        $gifts = [
            ['name' => 'Star', 'icon' => '⭐', 'price' => 0.99, 'description' => 'Show your appreciation', 'sort_order' => 1],
            ['name' => 'Heart', 'icon' => '❤️', 'price' => 1.99, 'description' => 'Spread the love', 'sort_order' => 2],
            ['name' => 'Fire', 'icon' => '🔥', 'price' => 2.99, 'description' => 'This track is fire!', 'sort_order' => 3],
            ['name' => 'Trophy', 'icon' => '🏆', 'price' => 4.99, 'description' => 'Award winning!', 'sort_order' => 4],
            ['name' => 'Diamond', 'icon' => '💎', 'price' => 9.99, 'description' => 'Premium support', 'sort_order' => 5],
            ['name' => 'Crown', 'icon' => '👑', 'price' => 19.99, 'description' => 'Royal treatment', 'sort_order' => 6],
        ];

        foreach ($gifts as $gift) {
            DB::table('gifts')->insert(array_merge($gift, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('gifts');
    }
};
