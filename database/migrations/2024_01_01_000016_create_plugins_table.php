<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Plugins Table
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('version')->default('1.0.0');
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->string('plugin_url')->nullable();
            $table->string('main_file'); // Path to main plugin file
            $table->json('requires')->nullable(); // Required dependencies
            $table->json('settings')->nullable(); // Plugin settings
            $table->boolean('is_active')->default(false);
            $table->boolean('is_installed')->default(false);
            $table->integer('priority')->default(10); // Load order
            $table->timestamps();
        });

        // Plugin Hooks Table
        Schema::create('plugin_hooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->onDelete('cascade');
            $table->string('hook_name'); // e.g., 'track_uploaded', 'user_registered'
            $table->string('callback_class');
            $table->string('callback_method');
            $table->integer('priority')->default(10);
            $table->timestamps();
        });

        // Plugin Routes Table
        Schema::create('plugin_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->onDelete('cascade');
            $table->string('method'); // GET, POST, PUT, DELETE
            $table->string('uri');
            $table->string('controller');
            $table->string('action');
            $table->string('middleware')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        // Plugin Migrations Table
        Schema::create('plugin_migrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->onDelete('cascade');
            $table->string('migration');
            $table->integer('batch');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_migrations');
        Schema::dropIfExists('plugin_routes');
        Schema::dropIfExists('plugin_hooks');
        Schema::dropIfExists('plugins');
    }
};
