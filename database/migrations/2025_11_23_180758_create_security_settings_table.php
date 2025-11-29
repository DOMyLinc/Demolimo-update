<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();

            // Login Security
            $table->integer('max_login_attempts')->default(5);
            $table->integer('lockout_duration')->default(15); // minutes
            $table->boolean('enable_login_lockout')->default(true);

            // Password Policies
            $table->integer('min_password_length')->default(8);
            $table->boolean('require_uppercase')->default(true);
            $table->boolean('require_lowercase')->default(true);
            $table->boolean('require_numbers')->default(true);
            $table->boolean('require_special_chars')->default(false);
            $table->integer('password_expiry_days')->default(0); // 0 = never

            // Session Management
            $table->integer('session_timeout')->default(120); // minutes
            $table->boolean('force_logout_on_password_change')->default(true);
            $table->boolean('allow_concurrent_sessions')->default(true);
            $table->integer('max_concurrent_sessions')->default(3);

            // Two-Factor Authentication
            $table->boolean('enable_2fa')->default(false);
            $table->boolean('force_2fa_for_admins')->default(false);

            // Google reCAPTCHA
            $table->boolean('recaptcha_enabled')->default(false);
            $table->string('recaptcha_version')->default('v2'); // v2, v3
            $table->string('recaptcha_site_key')->nullable();
            $table->string('recaptcha_secret_key')->nullable();
            $table->decimal('recaptcha_score_threshold', 3, 2)->default(0.5); // For v3
            $table->boolean('recaptcha_on_login')->default(true);
            $table->boolean('recaptcha_on_register')->default(true);
            $table->boolean('recaptcha_on_forgot_password')->default(true);

            // IP Security
            $table->boolean('enable_ip_whitelist')->default(false);
            $table->json('ip_whitelist')->nullable();
            $table->boolean('enable_ip_blacklist')->default(false);
            $table->json('ip_blacklist')->nullable();

            $table->timestamps();
        });

        // Insert default settings
        DB::table('security_settings')->insert([
            'max_login_attempts' => 5,
            'lockout_duration' => 15,
            'enable_login_lockout' => true,
            'min_password_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special_chars' => false,
            'session_timeout' => 120,
            'recaptcha_enabled' => false,
            'recaptcha_version' => 'v2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('security_settings');
    }
};
