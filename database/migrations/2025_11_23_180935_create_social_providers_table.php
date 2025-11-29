<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('social_providers', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique(); // facebook, google, github, qq, pinterest, twitter, linkedin, apple
            $table->string('name'); // Display name
            $table->boolean('enabled')->default(false);

            // OAuth Credentials (encrypted)
            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();

            // Configuration
            $table->string('redirect_url')->nullable();
            $table->json('scopes')->nullable();
            $table->json('additional_config')->nullable();

            // Icon & Styling
            $table->string('icon')->nullable();
            $table->string('button_color')->nullable();

            $table->timestamps();
        });

        // Insert default providers
        $providers = [
            [
                'provider' => 'facebook',
                'name' => 'Facebook',
                'icon' => 'fab fa-facebook',
                'button_color' => '#1877F2',
                'scopes' => json_encode(['email', 'public_profile']),
            ],
            [
                'provider' => 'google',
                'name' => 'Google',
                'icon' => 'fab fa-google',
                'button_color' => '#DB4437',
                'scopes' => json_encode(['email', 'profile']),
            ],
            [
                'provider' => 'github',
                'name' => 'GitHub',
                'icon' => 'fab fa-github',
                'button_color' => '#333333',
                'scopes' => json_encode(['user:email']),
            ],
            [
                'provider' => 'qq',
                'name' => 'QQ',
                'icon' => 'fab fa-qq',
                'button_color' => '#12B7F5',
                'scopes' => json_encode(['get_user_info']),
            ],
            [
                'provider' => 'pinterest',
                'name' => 'Pinterest',
                'icon' => 'fab fa-pinterest',
                'button_color' => '#E60023',
                'scopes' => json_encode(['read_public']),
            ],
            [
                'provider' => 'twitter',
                'name' => 'Twitter',
                'icon' => 'fab fa-twitter',
                'button_color' => '#1DA1F2',
                'scopes' => json_encode(['tweet.read', 'users.read']),
            ],
            [
                'provider' => 'linkedin',
                'name' => 'LinkedIn',
                'icon' => 'fab fa-linkedin',
                'button_color' => '#0A66C2',
                'scopes' => json_encode(['r_liteprofile', 'r_emailaddress']),
            ],
            [
                'provider' => 'apple',
                'name' => 'Apple',
                'icon' => 'fab fa-apple',
                'button_color' => '#000000',
                'scopes' => json_encode(['name', 'email']),
            ],
        ];

        foreach ($providers as $provider) {
            DB::table('social_providers')->insert(array_merge($provider, [
                'enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('social_providers');
    }
};
