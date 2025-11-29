<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Events Table
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('venue');
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('cover_image')->nullable();
            $table->string('event_type'); // concert, festival, workshop, meetup
            $table->string('status')->default('draft'); // draft, published, cancelled, completed
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_online')->default(false);
            $table->string('stream_url')->nullable();
            $table->integer('capacity')->nullable();
            $table->integer('tickets_sold')->default(0);
            $table->timestamps();
        });

        // Ticket Types Table
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name'); // VIP, General Admission, Early Bird
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->integer('quantity_sold')->default(0);
            $table->dateTime('sale_start')->nullable();
            $table->dateTime('sale_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Ticket Purchases Table
        Schema::create('ticket_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained()->onDelete('cascade');
            $table->string('ticket_code')->unique();
            $table->string('qr_code')->nullable();
            $table->decimal('price_paid', 10, 2);
            $table->string('payment_status')->default('pending'); // pending, completed, refunded
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->boolean('is_checked_in')->default(false);
            $table->dateTime('checked_in_at')->nullable();
            $table->timestamps();
        });

        // Event Performers Table
        Schema::create('event_performers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->time('performance_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_performers');
        Schema::dropIfExists('ticket_purchases');
        Schema::dropIfExists('ticket_types');
        Schema::dropIfExists('events');
    }
};
