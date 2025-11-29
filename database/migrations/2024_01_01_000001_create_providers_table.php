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
        Schema::create('storage_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver');
            $table->text('key')->nullable();
            $table->text('secret')->nullable();
            $table->string('region')->nullable();
            $table->string('bucket')->nullable();
            $table->string('endpoint')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('payment_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('public_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->string('merchant_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_test_mode')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_providers');
        Schema::dropIfExists('storage_providers');
    }
};
