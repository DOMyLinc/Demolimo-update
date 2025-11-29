<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('cache_settings', function (Blueprint $table) {
            $table->id();

            // Cache Driver
            $table->string('cache_driver')->default('file'); // file, redis, memcached
            $table->boolean('cache_enabled')->default(true);

            // Redis Configuration
            $table->boolean('redis_enabled')->default(false);
            $table->string('redis_host')->default('127.0.0.1');
            $table->integer('redis_port')->default(6379);
            $table->string('redis_password')->nullable();
            $table->integer('redis_database')->default(0);

            // Memcached Configuration
            $table->boolean('memcached_enabled')->default(false);
            $table->string('memcached_host')->default('127.0.0.1');
            $table->integer('memcached_port')->default(11211);

            // Cache TTL Settings (in seconds)
            $table->integer('default_ttl')->default(3600); // 1 hour
            $table->integer('query_cache_ttl')->default(300); // 5 minutes
            $table->integer('api_cache_ttl')->default(60); // 1 minute
            $table->integer('static_cache_ttl')->default(86400); // 24 hours

            // High-Traffic Optimization
            $table->boolean('enable_query_caching')->default(true);
            $table->boolean('enable_api_caching')->default(true);
            $table->boolean('enable_view_caching')->default(true);
            $table->boolean('enable_route_caching')->default(false);
            $table->boolean('enable_config_caching')->default(false);

            // CDN Integration
            $table->boolean('cdn_enabled')->default(false);
            $table->string('cdn_url')->nullable();
            $table->json('cdn_assets')->nullable(); // Which assets to serve via CDN

            // Performance Settings
            $table->boolean('enable_compression')->default(true);
            $table->boolean('enable_minification')->default(true);
            $table->boolean('enable_lazy_loading')->default(true);

            // Rate Limiting
            $table->boolean('enable_rate_limiting')->default(true);
            $table->integer('rate_limit_requests')->default(60); // requests per minute
            $table->integer('rate_limit_window')->default(1); // minutes

            // Load Balancing
            $table->boolean('load_balancing_enabled')->default(false);
            $table->json('load_balancer_nodes')->nullable();

            $table->timestamps();
        });

        // Insert default settings
        DB::table('cache_settings')->insert([
            'cache_driver' => 'file',
            'cache_enabled' => true,
            'redis_enabled' => false,
            'redis_host' => '127.0.0.1',
            'redis_port' => 6379,
            'redis_database' => 0,
            'default_ttl' => 3600,
            'query_cache_ttl' => 300,
            'api_cache_ttl' => 60,
            'static_cache_ttl' => 86400,
            'enable_query_caching' => true,
            'enable_api_caching' => true,
            'enable_compression' => true,
            'enable_rate_limiting' => true,
            'rate_limit_requests' => 60,
            'rate_limit_window' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('cache_settings');
    }
};
