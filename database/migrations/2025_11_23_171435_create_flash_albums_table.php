<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('flash_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Design & Branding
            $table->string('cover_image'); // Album artwork
            $table->string('label_design')->nullable(); // Custom flash drive label design
            $table->string('packaging_design')->nullable(); // Custom packaging design
            $table->json('color_scheme')->nullable(); // Brand colors

            // Product Details
            $table->string('flash_drive_capacity')->default('8GB'); // 8GB, 16GB, 32GB, 64GB
            $table->string('flash_drive_type')->default('standard'); // standard, premium, custom
            $table->decimal('base_price', 10, 2); // Artist's selling price
            $table->decimal('production_cost', 10, 2)->default(0); // Platform's cost
            $table->decimal('artist_profit', 10, 2)->default(0); // Calculated profit per unit

            // Content
            $table->json('track_ids'); // Array of track IDs included
            $table->json('bonus_content')->nullable(); // Videos, images, PDFs, etc.
            $table->boolean('include_digital_copy')->default(true); // Include download code

            // Inventory & Sales
            $table->integer('stock_quantity')->default(0);
            $table->integer('units_sold')->default(0);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_pre_order')->default(false);
            $table->timestamp('release_date')->nullable();
            $table->timestamp('pre_order_end_date')->nullable();

            // Shipping
            $table->boolean('free_shipping')->default(false);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->json('shipping_regions')->nullable(); // Countries/regions where shipping is available

            // Platform Features
            $table->boolean('is_featured')->default(false);
            $table->boolean('requires_approval')->default(true);
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });

        Schema::create('flash_album_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_album_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();

            // Pricing
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);

            // Shipping Address
            $table->string('shipping_name');
            $table->string('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_state')->nullable();
            $table->string('shipping_postal_code');
            $table->string('shipping_country');
            $table->string('shipping_phone')->nullable();

            // Order Status
            $table->string('status')->default('pending'); // pending, processing, shipped, delivered, cancelled
            $table->string('payment_status')->default('pending'); // pending, paid, refunded
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();

            // Tracking
            $table->string('tracking_number')->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Digital Copy
            $table->string('download_code')->nullable();
            $table->timestamp('download_code_expires_at')->nullable();

            $table->timestamps();
        });

        Schema::create('flash_drive_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('preview_image');
            $table->string('capacity'); // 8GB, 16GB, etc.
            $table->string('type'); // standard, premium, custom
            $table->decimal('base_cost', 10, 2); // Platform's cost
            $table->decimal('suggested_price', 10, 2); // Suggested retail price
            $table->json('customization_options')->nullable(); // Colors, engraving, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('flash_album_orders');
        Schema::dropIfExists('flash_albums');
        Schema::dropIfExists('flash_drive_templates');
    }
};
