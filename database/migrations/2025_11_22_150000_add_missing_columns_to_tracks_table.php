<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('tracks', function (Blueprint $table) {
            if (!Schema::hasColumn('tracks', 'genre')) {
                $table->string('genre')->nullable()->after('description');
            }
            if (!Schema::hasColumn('tracks', 'duration')) {
                $table->integer('duration')->default(0)->after('audio_path');
            }
            if (!Schema::hasColumn('tracks', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('downloads');
            }
            if (!Schema::hasColumn('tracks', 'price')) {
                $table->decimal('price', 8, 2)->default(0)->after('is_public');
            }
            if (!Schema::hasColumn('tracks', 'tags')) {
                $table->json('tags')->nullable()->after('price');
            }
            if (!Schema::hasColumn('tracks', 'image_path')) {
                $table->string('image_path')->nullable()->after('audio_path');
            }
        });
    }

    public function down()
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn(['genre', 'duration', 'is_public', 'price', 'tags', 'image_path']);
        });
    }
};
