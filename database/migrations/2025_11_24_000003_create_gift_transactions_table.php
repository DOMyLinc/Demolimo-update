<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('gift_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('track_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('gift_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0); // 20% platform fee
            $table->decimal('artist_earning', 10, 2)->default(0); // 80% to artist
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('payment_status')->default('completed'); // completed, pending, failed
            $table->timestamps();

            // Indexes
            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('track_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('gift_transactions');
    }
};
