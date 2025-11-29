<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Add API access control to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('api_access_enabled')->default(true)->after('email_verified_at');
        });

        // Create API logs table
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('api_applications')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('method'); // GET, POST, PUT, DELETE
            $table->string('endpoint');
            $table->integer('status_code');
            $table->integer('response_time')->nullable(); // milliseconds
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'created_at']);
            $table->index('created_at');
        });

        // Seed default API feature flags
        DB::table('feature_flags')->insert([
            [
                'key' => 'enable_api',
                'name' => 'Enable API',
                'description' => 'Enable API access for developers',
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_public_api_docs',
                'name' => 'Public API Documentation',
                'description' => 'Allow public access to API documentation',
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('api_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('api_access_enabled');
        });

        DB::table('feature_flags')
            ->whereIn('key', ['enable_api', 'enable_public_api_docs'])
            ->delete();
    }
};
