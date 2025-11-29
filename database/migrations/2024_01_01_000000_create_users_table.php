<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Custom Fields
            $table->string('role')->default('user'); // admin, artist, user
            $table->string('avatar')->nullable();
            $table->string('cover')->nullable();
            $table->bigInteger('points')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->bigInteger('storage_limit')->default(1073741824); // 1GB default
            $table->bigInteger('used_storage')->default(0);
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
