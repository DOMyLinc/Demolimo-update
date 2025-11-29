<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('source_type'); // track_sale, album_sale, event_ticket, product_sale, ad_revenue, donation, tip
            $table->unsignedBigInteger('source_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('platform_fee', 10, 2);
            $table->decimal('artist_amount', 10, 2);
            $table->string('status')->default('completed'); // pending, completed, refunded
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'source_type']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('revenues');
    }
};
