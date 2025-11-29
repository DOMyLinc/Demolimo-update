<?php

namespace App\Services;

use App\Models\User;
use App\Models\SystemConfiguration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InstallationService
{
    protected $log = [];

    /**
     * Run all database migrations
     */
    public function runMigrations(): void
    {
        $this->log('Running database migrations...');

        try {
            Artisan::call('migrate', ['--force' => true]);
            $this->log('✓ Migrations completed successfully');
        } catch (\Exception $e) {
            $this->log('✗ Migration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Seed all necessary database tables
     */
    public function seedDatabase(): void
    {
        $this->log('Seeding database with default data...');

        // Seed boost packages
        $this->seedBoostPackages();

        // Seed translations
        $this->seedTranslations();

        // Seed genres
        $this->seedGenres();

        // Seed default system configurations
        $this->seedSystemConfigurations();

        // Seed subscription plans
        $this->seedSubscriptionPlans();

        // Seed flash drive templates
        $this->seedFlashDriveTemplates();

        // Seed default pages
        $this->seedDefaultPages();

        $this->log('✓ Database seeding completed');
    }

    /**
     * Seed boost packages
     */
    protected function seedBoostPackages(): void
    {
        $this->log('Seeding boost packages...');

        try {
            Artisan::call('db:seed', [
                '--class' => 'BoostPackageSeeder',
                '--force' => true
            ]);
            $this->log('✓ Boost packages seeded (3 packages)');
        } catch (\Exception $e) {
            $this->log('✗ Boost package seeding failed: ' . $e->getMessage());
        }
    }

    /**
     * Seed translations
     */
    protected function seedTranslations(): void
    {
        $this->log('Seeding translations...');

        try {
            Artisan::call('db:seed', [
                '--class' => 'TranslationSeeder',
                '--force' => true
            ]);
            $this->log('✓ Translations seeded (40+ keys)');
        } catch (\Exception $e) {
            $this->log('✗ Translation seeding failed: ' . $e->getMessage());
        }
    }

    /**
     * Seed music genres
     */
    protected function seedGenres(): void
    {
        $this->log('Seeding music genres...');

        $genres = [
            'Pop',
            'Rock',
            'Hip Hop',
            'R&B',
            'Jazz',
            'Classical',
            'Electronic',
            'Country',
            'Reggae',
            'Blues',
            'Metal',
            'Folk',
            'Indie',
            'Soul',
            'Funk',
            'Disco',
            'House',
            'Techno',
            'Dubstep',
            'Trap',
            'Lo-Fi',
            'Ambient'
        ];

        try {
            foreach ($genres as $index => $genre) {
                DB::table('genres')->insertOrIgnore([
                    'name' => $genre,
                    'slug' => \Illuminate\Support\Str::slug($genre),
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->log('✓ Genres seeded (' . count($genres) . ' genres)');
        } catch (\Exception $e) {
            $this->log('✗ Genre seeding failed: ' . $e->getMessage());
        }
    }

    /**
     * Seed system configurations
     */
    protected function seedSystemConfigurations(): void
    {
        $this->log('Seeding system configurations...');

        $configs = [
            // General Settings
            'site_name' => 'DemoLimo',
            'site_description' => 'Professional Music Streaming Platform',
            'site_keywords' => 'music, streaming, artists, albums, tracks',
            'site_logo' => '',
            'site_favicon' => '',

            // Upload Settings
            'max_upload_size' => 100, // MB
            'allowed_audio_formats' => 'mp3,wav,flac,aac,ogg,m4a',
            'allowed_image_formats' => 'jpg,jpeg,png,gif,webp',
            'max_track_duration' => 600, // seconds (10 minutes)

            // User Settings
            'registration_enabled' => true,
            'email_verification_required' => false,
            'default_user_role' => 'user',
            'free_user_upload_limit' => 10,
            'free_user_storage_limit' => 500, // MB

            // Media Settings
            'ffmpeg_enabled' => false,
            'ffmpeg_path' => '/usr/bin/ffmpeg',
            'waveform_generation' => true,
            'auto_transcode' => false,

            // Monetization Settings
            'commission_rate' => 15, // percentage
            'minimum_withdrawal' => 50, // USD
            'currency' => 'USD',
            'currency_symbol' => '$',

            // Social Settings
            'facebook_login' => false,
            'google_login' => false,
            'twitter_login' => false,

            // Email Settings
            'mail_driver' => 'smtp',
            'mail_host' => 'smtp.mailtrap.io',
            'mail_port' => 2525,
            'mail_from_address' => 'noreply@demolimo.com',
            'mail_from_name' => 'DemoLimo',

            // Storage Settings
            'storage_driver' => 'local',
            's3_key' => '',
            's3_secret' => '',
            's3_region' => 'us-east-1',
            's3_bucket' => '',

            // Analytics
            'google_analytics_id' => '',
            'facebook_pixel_id' => '',

            // Features
            'enable_podcasts' => true,
            'enable_radio_stations' => true,
            'enable_events' => true,
            'enable_song_battles' => true,
            'enable_flash_albums' => true,
            'enable_zipcode_studios' => true,
        ];

        try {
            foreach ($configs as $key => $value) {
                DB::table('system_configurations')->insertOrIgnore([
                    'key' => $key,
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->log('✓ System configurations seeded (' . count($configs) . ' settings)');
        } catch (\Exception $e) {
            $this->log('✗ System configuration seeding failed: ' . $e->getMessage());
        }
    }

    /**
     * Seed subscription plans
     */
    protected function seedSubscriptionPlans(): void
    {
        $this->log('Seeding subscription plans...');

        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0,
                'duration_days' => 0,
                'features' => json_encode([
                    'Upload up to 10 tracks',
                    '500MB storage',
                    'Basic analytics',
                    'Standard support',
                ]),
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 9.99,
                'duration_days' => 30,
                'features' => json_encode([
                    'Unlimited uploads',
                    '50GB storage',
                    'Advanced analytics',
                    'Priority support',
                    'Remove ads',
                    'Custom branding',
                ]),
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'price' => 29.99,
                'duration_days' => 30,
                'features' => json_encode([
                    'Everything in Pro',
                    '200GB storage',
                    'Dedicated account manager',
                    'API access',
                    'White-label options',
                    'Revenue sharing',
                ]),
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        try {
            foreach ($plans as $plan) {
                DB::table('subscription_plans')->insertOrIgnore(array_merge($plan, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->log('✓ Subscription plans seeded (3 plans)');
        } catch (\Exception $e) {
            $this->log('✗ Subscription plan seeding failed: ' . $e->getMessage());
        }
    }

    /**
     * Seed flash drive templates
     */
    protected function seedFlashDriveTemplates(): void
    {
        $this->log('Seeding flash drive templates...');

        $templates = [
            [
                'name' => 'Standard USB 2.0',
                'capacity_gb' => 8,
                'base_price' => 5.99,
                'production_cost' => 2.50,
                'is_active' => true,
            ],
            [
                'name' => 'Premium USB 3.0',
                'capacity_gb' => 16,
                'base_price' => 12.99,
                'production_cost' => 5.00,
                'is_active' => true,
            ],
            [
                'name' => 'Deluxe USB 3.1',
                'capacity_gb' => 32,
                'base_price' => 24.99,
                'production_cost' => 10.00,
                'is_active' => true,
            ],
        ];

        try {
            foreach ($templates as $template) {
                DB::table('flash_drive_templates')->insertOrIgnore(array_merge($template, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->log('✓ Flash drive templates seeded (3 templates)');
        } catch (\Exception $e) {
            $this->log('✗ Flash drive template seeding failed: ' . $e->getMessage());
        }
    }

    /**
     * Seed default pages
     */
    protected function seedDefaultPages(): void
    {
        $this->log('Seeding default pages...');

        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about',
                'content' => '<h1>About DemoLimo</h1><p>Welcome to DemoLimo, your premier music streaming platform.</p>',
                'is_published' => true,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms',
                'content' => '<h1>Terms of Service</h1><p>Please read these terms carefully before using our service.</p>',
                'is_published' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'content' => '<h1>Privacy Policy</h1><p>Your privacy is important to us.</p>',
                'is_published' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'content' => '<h1>Contact Us</h1><p>Get in touch with our team.</p>',
                'is_published' => true,
            ],
        ];

        try {
            foreach ($pages as $page) {
                DB::table('pages')->insertOrIgnore(array_merge($page, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->log('✓ Default pages seeded (4 pages)');
        } catch (\Exception $e) {
            $this->log('✗ Default page seeding failed: ' . $e->getMessage());
        }
    }

    /**
     * Create admin user account
     */
    public function createAdminUser(array $config): void
    {
        $this->log('Creating admin account...');

        try {
            User::create([
                'name' => 'Administrator',
                'email' => $config['admin_email'],
                'username' => $config['admin_username'] ?? 'admin',
                'password' => Hash::make($config['admin_password']),
                'role' => 'admin',
                'is_verified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->log('✓ Admin account created');
        } catch (\Exception $e) {
            $this->log('✗ Admin account creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Configure site settings
     */
    public function configureSettings(array $config): void
    {
        $this->log('Configuring site settings...');

        try {
            $settings = [
                'site_name' => $config['site_name'] ?? 'DemoLimo',
                'site_url' => $config['site_url'] ?? url('/'),
                'admin_email' => $config['admin_email'],
                'timezone' => $config['timezone'] ?? 'UTC',
                'default_language' => $config['language'] ?? 'en',
            ];

            foreach ($settings as $key => $value) {
                DB::table('system_configurations')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
            }

            $this->log('✓ Site settings configured');
        } catch (\Exception $e) {
            $this->log('✗ Settings configuration failed: ' . $e->getMessage());
        }
    }

    /**
     * Create storage symbolic links
     */
    public function createStorageLinks(): void
    {
        $this->log('Creating storage links...');

        try {
            Artisan::call('storage:link');
            $this->log('✓ Storage links created');
        } catch (\Exception $e) {
            $this->log('⚠ Storage link creation skipped (may already exist)');
        }
    }

    /**
     * Clear all caches
     */
    public function clearCaches(): void
    {
        $this->log('Clearing caches...');

        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            $this->log('✓ Caches cleared');
        } catch (\Exception $e) {
            $this->log('⚠ Cache clearing skipped');
        }
    }

    /**
     * Optimize application
     */
    public function optimizeApplication(): void
    {
        $this->log('Optimizing application...');

        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $this->log('✓ Application optimized');
        } catch (\Exception $e) {
            $this->log('⚠ Optimization skipped');
        }
    }

    /**
     * Create installation lock file
     */
    public function createLockFile(): void
    {
        $this->log('Creating installation lock file...');

        try {
            $lockData = [
                'installed_at' => now()->toDateTimeString(),
                'version' => '1.0.0',
            ];

            file_put_contents(
                public_path('.installed'),
                json_encode($lockData, JSON_PRETTY_PRINT)
            );

            $this->log('✓ Installation lock file created');
        } catch (\Exception $e) {
            $this->log('✗ Lock file creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add log entry
     */
    protected function log(string $message): void
    {
        $this->log[] = $message;
    }

    /**
     * Get installation log
     */
    public function getLog(): array
    {
        return $this->log;
    }
}
