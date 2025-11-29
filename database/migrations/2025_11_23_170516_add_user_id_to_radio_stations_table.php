<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('radio_stations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->boolean('is_user_created')->default(false)->after('is_featured');
            $table->boolean('requires_approval')->default(true)->after('is_user_created');
            $table->timestamp('approved_at')->nullable()->after('requires_approval');
        });
    }

    public function down()
    {
        Schema::table('radio_stations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_user_created', 'requires_approval', 'approved_at']);
        });
    }
};
