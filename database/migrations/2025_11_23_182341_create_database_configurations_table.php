<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('database_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // mysql, pgsql
            $table->string('display_name');
            $table->string('driver'); // mysql, pgsql

            // Connection Details
            $table->string('host')->default('127.0.0.1');
            $table->integer('port')->nullable();
            $table->string('database');
            $table->string('username');
            $table->text('password')->nullable(); // Encrypted
            $table->string('charset')->nullable();
            $table->string('collation')->nullable();
            $table->string('prefix')->nullable();

            // Status & Priority
            $table->boolean('is_active')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->integer('priority')->default(0); // Higher = higher priority
            $table->boolean('auto_failover')->default(true);

            // Health Monitoring
            $table->timestamp('last_health_check')->nullable();
            $table->boolean('is_healthy')->default(true);
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('last_failure')->nullable();

            // SSL Configuration
            $table->boolean('ssl_enabled')->default(false);
            $table->text('ssl_ca')->nullable();
            $table->text('ssl_cert')->nullable();
            $table->text('ssl_key')->nullable();

            $table->timestamps();
        });

        // Insert default MySQL configuration
        DB::table('database_configurations')->insert([
            [
                'name' => 'mysql',
                'display_name' => 'MySQL',
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', 3306),
                'database' => env('DB_DATABASE', 'demolimo'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'is_active' => true,
                'is_primary' => true,
                'priority' => 100,
                'auto_failover' => true,
                'is_healthy' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'pgsql',
                'display_name' => 'PostgreSQL',
                'driver' => 'pgsql',
                'host' => env('PGSQL_HOST', '127.0.0.1'),
                'port' => env('PGSQL_PORT', 5432),
                'database' => env('PGSQL_DATABASE', 'demolimo'),
                'username' => env('PGSQL_USERNAME', 'postgres'),
                'password' => env('PGSQL_PASSWORD', ''),
                'charset' => 'utf8',
                'is_active' => false,
                'is_primary' => false,
                'priority' => 50,
                'auto_failover' => true,
                'is_healthy' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('database_configurations');
    }
};
