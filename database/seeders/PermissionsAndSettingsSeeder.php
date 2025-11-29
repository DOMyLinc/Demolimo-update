<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeaturePermission;
use App\Models\PlatformSetting;

class PermissionsAndSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Feature Permissions
        $this->seedFeaturePermissions();

        // Platform Settings
        $this->seedPlatformSettings();
    }

    protected function seedFeaturePermissions()
    {
        $permissions = [
            [
                'feature_name' => 'upload_tracks',
                'display_name' => 'Upload Tracks',
                'description' => 'Ability to upload music tracks to the platform',
                'free_plan' => true,
                'pro_plan' => true,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['max_tracks_per_month' => 5, 'max_file_size_mb' => 50],
                    'pro' => ['max_tracks_per_month' => 50, 'max_file_size_mb' => 200],
                    'premium' => ['max_tracks_per_month' => -1, 'max_file_size_mb' => 500],
                ],
            ],
            [
                'feature_name' => 'create_events',
                'display_name' => 'Create Events',
                'description' => 'Ability to create and manage events',
                'free_plan' => false,
                'pro_plan' => true,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['max_events_per_month' => 0],
                    'pro' => ['max_events_per_month' => 5, 'max_attendees' => 100],
                    'premium' => ['max_events_per_month' => -1, 'max_attendees' => -1],
                ],
            ],
            [
                'feature_name' => 'sell_tickets',
                'display_name' => 'Sell Tickets',
                'description' => 'Ability to sell tickets for events',
                'free_plan' => false,
                'pro_plan' => true,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['platform_commission' => 20],
                    'pro' => ['platform_commission' => 10, 'max_ticket_types' => 5],
                    'premium' => ['platform_commission' => 5, 'max_ticket_types' => -1],
                ],
            ],
            [
                'feature_name' => 'create_albums',
                'display_name' => 'Create Albums',
                'description' => 'Ability to organize tracks into albums',
                'free_plan' => true,
                'pro_plan' => true,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['max_albums' => 2, 'max_tracks_per_album' => 10],
                    'pro' => ['max_albums' => 20, 'max_tracks_per_album' => 50],
                    'premium' => ['max_albums' => -1, 'max_tracks_per_album' => -1],
                ],
            ],
            [
                'feature_name' => 'upload_videos',
                'display_name' => 'Upload Videos',
                'description' => 'Ability to upload video content',
                'free_plan' => false,
                'pro_plan' => true,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['max_videos_per_month' => 0],
                    'pro' => ['max_videos_per_month' => 10, 'max_video_size_mb' => 500],
                    'premium' => ['max_videos_per_month' => -1, 'max_video_size_mb' => 2000],
                ],
            ],
            [
                'feature_name' => 'distribute_music',
                'display_name' => 'Distribute Music',
                'description' => 'Distribute music to streaming platforms',
                'free_plan' => false,
                'pro_plan' => false,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['platforms' => []],
                    'pro' => ['platforms' => []],
                    'premium' => ['platforms' => ['spotify', 'apple_music', 'youtube_music', 'amazon_music']],
                ],
            ],
            [
                'feature_name' => 'advanced_analytics',
                'display_name' => 'Advanced Analytics',
                'description' => 'Access to detailed analytics and insights',
                'free_plan' => false,
                'pro_plan' => true,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['data_retention_days' => 30],
                    'pro' => ['data_retention_days' => 365],
                    'premium' => ['data_retention_days' => -1],
                ],
            ],
            [
                'feature_name' => 'custom_branding',
                'display_name' => 'Custom Branding',
                'description' => 'Customize your profile and remove watermarks',
                'free_plan' => false,
                'pro_plan' => false,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['can_remove_watermark' => false, 'custom_domain' => false],
                    'pro' => ['can_remove_watermark' => false, 'custom_domain' => false],
                    'premium' => ['can_remove_watermark' => true, 'custom_domain' => true],
                ],
            ],
            [
                'feature_name' => 'priority_support',
                'display_name' => 'Priority Support',
                'description' => 'Get priority customer support',
                'free_plan' => false,
                'pro_plan' => true,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['response_time_hours' => 72],
                    'pro' => ['response_time_hours' => 24],
                    'premium' => ['response_time_hours' => 4],
                ],
            ],
            [
                'feature_name' => 'api_access',
                'display_name' => 'API Access',
                'description' => 'Access to platform API for integrations',
                'free_plan' => false,
                'pro_plan' => true,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['requests_per_day' => 0],
                    'pro' => ['requests_per_day' => 1000],
                    'premium' => ['requests_per_day' => 10000],
                ],
            ],
            [
                'feature_name' => 'collaborate',
                'display_name' => 'Collaboration',
                'description' => 'Collaborate with other artists on tracks',
                'free_plan' => false,
                'pro_plan' => true,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['max_collaborators' => 0],
                    'pro' => ['max_collaborators' => 5],
                    'premium' => ['max_collaborators' => -1],
                ],
            ],
            [
                'feature_name' => 'studio_access',
                'display_name' => 'Studio Access',
                'description' => 'Access to browser-based music studio',
                'free_plan' => true,
                'pro_plan' => true,
                'premium_plan' => true,
                'limits' => [
                    'free' => ['max_projects' => 3, 'max_tracks_per_project' => 4],
                    'pro' => ['max_projects' => 50, 'max_tracks_per_project' => 16],
                    'premium' => ['max_projects' => -1, 'max_tracks_per_project' => 64],
                ],
            ],
        ];

        foreach ($permissions as $permission) {
            FeaturePermission::updateOrCreate(
                ['feature_name' => $permission['feature_name']],
                $permission
            );
        }

        $this->command->info('Feature permissions seeded successfully!');
    }

    protected function seedPlatformSettings()
    {
        $settings = [
            // General Settings
            ['category' => 'general', 'key' => 'site_name', 'value' => 'DemoLimo', 'type' => 'text', 'description' => 'Platform name'],
            ['category' => 'general', 'key' => 'site_description', 'value' => 'The ultimate music platform for creators', 'type' => 'text', 'description' => 'Platform description'],
            ['category' => 'general', 'key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'description' => 'Enable maintenance mode'],
            ['category' => 'general', 'key' => 'allow_registration', 'value' => 'true', 'type' => 'boolean', 'description' => 'Allow new user registrations'],
            ['category' => 'general', 'key' => 'require_email_verification', 'value' => 'true', 'type' => 'boolean', 'description' => 'Require email verification'],

            // Upload Settings
            ['category' => 'uploads', 'key' => 'max_upload_size', 'value' => '100', 'type' => 'number', 'description' => 'Maximum upload size in MB'],
            ['category' => 'uploads', 'key' => 'allowed_audio_types', 'value' => '["mp3","wav","flac","aac","ogg"]', 'type' => 'json', 'description' => 'Allowed audio file types'],
            ['category' => 'uploads', 'key' => 'allowed_image_types', 'value' => '["jpg","jpeg","png","gif","webp"]', 'type' => 'json', 'description' => 'Allowed image file types'],

            // Storage Settings
            ['category' => 'storage', 'key' => 'default_storage_limit', 'value' => '1073741824', 'type' => 'number', 'description' => 'Default storage limit in bytes (1GB)'],
            ['category' => 'storage', 'key' => 'storage_provider', 'value' => 'local', 'type' => 'text', 'description' => 'Storage provider (local, s3, gcs)'],

            // Payment Settings
            ['category' => 'payment', 'key' => 'stripe_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable Stripe payments'],
            ['category' => 'payment', 'key' => 'paypal_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable PayPal payments'],
            ['category' => 'payment', 'key' => 'currency', 'value' => 'USD', 'type' => 'text', 'description' => 'Platform currency'],

            // Commission Settings
            ['category' => 'commission', 'key' => 'ticket_commission', 'value' => '10', 'type' => 'number', 'description' => 'Platform commission on ticket sales (%)'],
            ['category' => 'commission', 'key' => 'track_commission', 'value' => '15', 'type' => 'number', 'description' => 'Platform commission on track sales (%)'],
            ['category' => 'commission', 'key' => 'minimum_payout', 'value' => '50', 'type' => 'number', 'description' => 'Minimum payout amount'],

            // Feature Toggles
            ['category' => 'features', 'key' => 'feature_events_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable events feature'],
            ['category' => 'features', 'key' => 'feature_tickets_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable ticket sales'],
            ['category' => 'features', 'key' => 'feature_studio_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable music studio'],
            ['category' => 'features', 'key' => 'feature_distribution_enabled', 'value' => 'false', 'type' => 'boolean', 'description' => 'Enable music distribution'],
            ['category' => 'features', 'key' => 'feature_videos_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable video uploads'],

            // Social Settings
            ['category' => 'social', 'key' => 'enable_comments', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable comments'],
            ['category' => 'social', 'key' => 'enable_likes', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable likes'],
            ['category' => 'social', 'key' => 'enable_sharing', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable social sharing'],
        ];

        foreach ($settings as $setting) {
            PlatformSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Platform settings seeded successfully!');
    }
}
