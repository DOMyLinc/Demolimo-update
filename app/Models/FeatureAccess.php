<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureAccess extends Model
{
    protected $table = 'feature_access';

    protected $fillable = [
        'feature_key',
        'feature_name',
        'description',
        'access_level',
        'is_beta',
        'is_enabled',
        'free_user_limit',
        'pro_user_limit',
        'additional_settings',
    ];

    protected $casts = [
        'is_beta' => 'boolean',
        'is_enabled' => 'boolean',
        'additional_settings' => 'array',
    ];

    /**
     * Check if user has access to a feature
     */
    public static function userHasAccess(User $user, string $featureKey): bool
    {
        $feature = self::where('feature_key', $featureKey)->first();

        if (!$feature || !$feature->is_enabled) {
            return false;
        }

        // Admin always has access
        if ($user->is_admin) {
            return true;
        }

        // Check access level
        if ($feature->access_level === 'admin') {
            return false;
        }

        if ($feature->access_level === 'pro') {
            return $user->isPro();
        }

        // Free access
        return true;
    }

    /**
     * Check if user has reached their limit for a feature
     */
    public static function userHasReachedLimit(User $user, string $featureKey): bool
    {
        $feature = self::where('feature_key', $featureKey)->first();

        if (!$feature) {
            return false;
        }

        $isPro = $user->isPro();
        $limit = $isPro ? $feature->pro_user_limit : $feature->free_user_limit;

        // Null means unlimited
        if ($limit === null) {
            return false;
        }

        // Get current count based on feature
        $currentCount = self::getUserFeatureCount($user, $featureKey);

        return $currentCount >= $limit;
    }

    /**
     * Get user's current usage count for a feature
     */
    protected static function getUserFeatureCount(User $user, string $featureKey): int
    {
        switch ($featureKey) {
            case 'track_upload':
                return $user->tracks()->count();
            case 'album_creation':
                return $user->albums()->count();
            case 'playlist_creation':
                return $user->playlists()->count();
            case 'radio_station':
                return $user->radioStations()->count();
            case 'podcast':
                return $user->podcasts()->count();
            case 'flash_album':
                return $user->flashAlbums()->count();
            default:
                return 0;
        }
    }

    /**
     * Get remaining quota for user
     */
    public static function getRemainingQuota(User $user, string $featureKey): ?int
    {
        $feature = self::where('feature_key', $featureKey)->first();

        if (!$feature) {
            return null;
        }

        $isPro = $user->isPro();
        $limit = $isPro ? $feature->pro_user_limit : $feature->free_user_limit;

        // Null means unlimited
        if ($limit === null) {
            return null;
        }

        $currentCount = self::getUserFeatureCount($user, $featureKey);

        return max(0, $limit - $currentCount);
    }

    /**
     * Get all beta features
     */
    public static function getBetaFeatures()
    {
        return self::where('is_beta', true)
            ->where('is_enabled', true)
            ->get();
    }

    /**
     * Get all pro features
     */
    public static function getProFeatures()
    {
        return self::where('access_level', 'pro')
            ->where('is_enabled', true)
            ->get();
    }
}
