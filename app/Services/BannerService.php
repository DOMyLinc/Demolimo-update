<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\BannerImpression;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class BannerService
{
    /**
     * Get banners for a specific zone
     *
     * @param string $zone
     * @param \App\Models\User|null $user
     * @return Collection
     */
    public function getBannersForZone(string $zone, $user = null): Collection
    {
        $cacheKey = "banners_zone_{$zone}_" . ($user ? $user->id : 'guest');

        return Cache::remember($cacheKey, 300, function () use ($zone, $user) {
            $audienceType = $this->getUserAudienceType($user);

            return Banner::active()
                ->published()
                ->scheduled()
                ->forZone($zone)
                ->forAudience($audienceType)
                ->orderByDesc('priority')
                ->get()
                ->filter(function ($banner) use ($user) {
                    return $banner->canShow($user);
                });
        });
    }

    /**
     * Record a banner impression
     *
     * @param int $bannerId
     * @param \App\Models\User|null $user
     * @param string $ipAddress
     * @param string|null $userAgent
     * @return void
     */
    public function recordImpression(int $bannerId, $user = null, string $ipAddress, ?string $userAgent = null): void
    {
        $banner = Banner::find($bannerId);

        if (!$banner) {
            return;
        }

        // Create impression record
        BannerImpression::create([
            'banner_id' => $bannerId,
            'user_id' => $user?->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'action' => 'impression',
        ]);

        // Increment banner impressions count
        $banner->incrementImpressions();
    }

    /**
     * Record a banner click
     *
     * @param int $bannerId
     * @param \App\Models\User|null $user
     * @param string $ipAddress
     * @param string|null $userAgent
     * @return void
     */
    public function recordClick(int $bannerId, $user = null, string $ipAddress, ?string $userAgent = null): void
    {
        $banner = Banner::find($bannerId);

        if (!$banner) {
            return;
        }

        // Create click record
        BannerImpression::create([
            'banner_id' => $bannerId,
            'user_id' => $user?->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'action' => 'click',
        ]);

        // Increment banner clicks count
        $banner->incrementClicks();
    }

    /**
     * Get user audience type
     *
     * @param \App\Models\User|null $user
     * @return string
     */
    protected function getUserAudienceType($user): string
    {
        if (!$user) {
            return 'free';
        }

        // Check if user has active subscription (Pro)
        // Assuming hasActiveSubscription() method exists on User model
        return method_exists($user, 'hasActiveSubscription') && $user->hasActiveSubscription()
            ? 'pro'
            : 'free';
    }

    /**
     * Clear cache for a specific zone
     *
     * @param string $zone
     * @return void
     */
    public function clearZoneCache(string $zone): void
    {
        Cache::forget("banners_zone_{$zone}");
    }

    /**
     * Clear all banner caches
     *
     * @return void
     */
    public function clearAllCaches(): void
    {
        $zones = [
            'landing_hero',
            'landing_sidebar',
            'landing_footer',
            'player_top',
            'player_inline',
            'player_bottom',
            'track_page_top',
            'track_page_bottom',
            'dashboard_notification',
            'global_top',
            'global_bottom'
        ];

        foreach ($zones as $zone) {
            $this->clearZoneCache($zone);
        }
    }
}
