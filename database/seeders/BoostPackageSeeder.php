<?php

namespace Database\Seeders;

use App\Models\BoostPackage;
use Illuminate\Database\Seeder;

class BoostPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter Boost',
                'slug' => 'starter',
                'description' => 'Get your flash album noticed with basic promotion',
                'price' => 9.99,
                'duration_days' => 7,
                'target_views' => 1000,
                'target_impressions' => 5000,
                'features' => [
                    'Featured in category',
                    'Priority in search results',
                    'Boosted badge',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro Boost',
                'slug' => 'pro',
                'description' => 'Maximum visibility for your flash album',
                'price' => 29.99,
                'duration_days' => 14,
                'target_views' => 5000,
                'target_impressions' => 25000,
                'features' => [
                    'Homepage featured section',
                    'Priority in all search results',
                    'Email newsletter inclusion',
                    'Boosted badge',
                    'Social media mention',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium Boost',
                'slug' => 'premium',
                'description' => 'Ultimate promotion package for maximum exposure',
                'price' => 99.99,
                'duration_days' => 30,
                'target_views' => 20000,
                'target_impressions' => 100000,
                'features' => [
                    'Homepage hero placement',
                    'Social media promotion campaign',
                    'Email newsletter feature',
                    'Priority support',
                    'Boosted badge',
                    'Dedicated account manager',
                    'Performance analytics report',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($packages as $package) {
            BoostPackage::create($package);
        }
    }
}
