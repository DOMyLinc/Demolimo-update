<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('song_battle_versions', function (Blueprint $table) {
            $table->unsignedBigInteger('play_count')->default(0);
        });
    }

    public function down()
    {
        Schema::table('song_battle_versions', function (Blueprint $table) {
            $table->dropColumn('play_count');
        });
    }
};
