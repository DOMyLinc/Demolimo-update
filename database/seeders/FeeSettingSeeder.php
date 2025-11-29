<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeeSetting;

class FeeSettingSeeder extends Seeder
{
    public function run(): void
    {
        $feeSettings = [
            [
                'type' => 'track_sale',
                'platform_fee_percentage' => 10.00,
                'platform_fee_fixed' => 0.00,
                'min_platform_fee' => 0.10,
                'max_platform_fee' => null,
                'is_active' => true,
                'description' => 'Platform fee for track sales - 10% of sale price',
            ],
            [
                'type' => 'album_sale',
                'platform_fee_percentage' => 10.00,
                'platform_fee_fixed' => 0.00,
                'min_platform_fee' => 0.50,
                'max_platform_fee' => null,
                'is_active' => true,
                'description' => 'Platform fee for album sales - 10% of sale price',
            ],
            [
                'type' => 'donation',
                'platform_fee_percentage' => 5.00,
                'platform_fee_fixed' => 0.00,
                'min_platform_fee' => 0.00,
                'max_platform_fee' => null,
                'is_active' => true,
                'description' => 'Platform fee for donations - 5% to cover processing costs',
            ],
            [
                'type' => 'tip',
                'platform_fee_percentage' => 5.00,
                'platform_fee_fixed' => 0.00,
                'min_platform_fee' => 0.00,
                'max_platform_fee' => null,
                'is_active' => true,
                'description' => 'Platform fee for tips - 5% to cover processing costs',
            ],
            [
                'type' => 'subscription',
                'platform_fee_percentage' => 15.00,
                'platform_fee_fixed' => 0.00,
                'min_platform_fee' => 0.50,
                'max_platform_fee' => 50.00,
                'is_active' => true,
                'description' => 'Platform fee for subscription payments - 15% with max $50',
            ],
            [
                'type' => 'points',
                'platform_fee_percentage' => 0.00,
                'platform_fee_fixed' => 0.00,
                'min_platform_fee' => 0.00,
                'max_platform_fee' => 0.00,
                'is_active' => true,
                'description' => 'No platform fee for points purchases - revenue from markup',
            ],
            [
                'type' => 'event_ticket',
                'platform_fee_percentage' => 8.00,
                'platform_fee_fixed' => 1.00,
                'min_platform_fee' => 1.00,
                'max_platform_fee' => 25.00,
                'is_active' => true,
                'description' => 'Platform fee for event tickets - 8% + $1.00 per ticket',
            ],
            [
                'type' => 'merchandise',
                'platform_fee_percentage' => 12.00,
                'platform_fee_fixed' => 0.00,
                'min_platform_fee' => 0.50,
                'max_platform_fee' => null,
                'is_active' => false,
                'description' => 'Platform fee for merchandise sales - 12% (inactive)',
            ],
        ];

        foreach ($feeSettings as $setting) {
            FeeSetting::create($setting);
        }

        $this->command->info('Fee settings seeded successfully!');
    }
}
