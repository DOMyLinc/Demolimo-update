<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('zipcode_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zipcode_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('creator_id')->constrained('users');
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });

        Schema::create('zipcode_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zipcode_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['admin', 'moderator', 'member'])->default('member');
            $table->timestamps();

            $table->unique(['zipcode_group_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('zipcode_group_members');
        Schema::dropIfExists('zipcode_groups');
    }
};
