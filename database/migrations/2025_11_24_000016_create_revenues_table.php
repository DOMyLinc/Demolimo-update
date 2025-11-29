<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The user who earned the revenue (artist/admin)
            $table->decimal('amount', 10, 2);
            $table->decimal('commission', 10, 2)->default(0); // Platform commission
            $table->string('source_type'); // track, album, event, product, ad, tip, gift
            $table->unsignedBigInteger('source_id'); // ID of the source item
            $table->string('currency')->default('USD');
            $table->string('status')->default('pending'); // pending, available, paid
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['source_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
