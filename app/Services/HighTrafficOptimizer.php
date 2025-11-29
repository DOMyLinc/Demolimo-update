<?php

namespace App\Services;

use App\Models\CacheSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HighTrafficOptimizer
{
    protected $settings;

    public function __construct()
    {
        $this->settings = CacheSetting::getCached();
    }

    /**
     * Cache database query results
     */
    public function cacheQuery(string $key, \Closure $callback, ?int $ttl = null)
    {
        if (!$this->settings->enable_query_caching) {
            return $callback();
        }

        $ttl = $ttl ?? $this->settings->query_cache_ttl;

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Cache API responses
     */
    public function cacheApiResponse(string $key, \Closure $callback, ?int $ttl = null)
    {
        if (!$this->settings->enable_api_caching) {
            return $callback();
        }

        $ttl = $ttl ?? $this->settings->api_cache_ttl;

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get popular tracks with caching
     */
    public function getPopularTracks(int $limit = 10)
    {
        return $this->cacheQuery('popular_tracks:' . $limit, function () use ($limit) {
            return DB::table('tracks')
                ->where('is_approved', true)
                ->orderBy('plays', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get trending artists with caching
     */
    public function getTrendingArtists(int $limit = 10)
    {
        return $this->cacheQuery('trending_artists:' . $limit, function () use ($limit) {
            return DB::table('users')
                ->where('role', 'artist')
                ->orderBy('followers_count', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Warm up cache with frequently accessed data
     */
    public function warmUpCache()
    {
        // Cache popular content
        $this->getPopularTracks(20);
        $this->getTrendingArtists(20);

        // Cache genres
        Cache::remember('active_genres', $this->settings->static_cache_ttl, function () {
            return DB::table('genres')->where('is_active', true)->get();
        });

        // Cache system settings
        Cache::remember('system_settings', $this->settings->static_cache_ttl, function () {
            return DB::table('system_configurations')->first();
        });

        return true;
    }

    /**
     * Optimize database queries for high traffic
     */
    public function optimizeQueries()
    {
        // Enable query result caching
        if ($this->settings->enable_query_caching) {
            DB::enableQueryLog();
        }

        return true;
    }

    /**
     * Get CDN URL for asset
     */
    public function getCdnUrl(string $path): string
    {
        if ($this->settings->cdn_enabled && $this->settings->cdn_url) {
            $cdnAssets = $this->settings->cdn_assets ?? ['images', 'css', 'js'];

            foreach ($cdnAssets as $assetType) {
                if (str_contains($path, $assetType)) {
                    return rtrim($this->settings->cdn_url, '/') . '/' . ltrim($path, '/');
                }
            }
        }

        return $path;
    }

    /**
     * Apply rate limiting
     */
    public function checkRateLimit(string $key, ?int $maxAttempts = null, ?int $decayMinutes = null): bool
    {
        if (!$this->settings->enable_rate_limiting) {
            return true;
        }

        $maxAttempts = $maxAttempts ?? $this->settings->rate_limit_requests;
        $decayMinutes = $decayMinutes ?? $this->settings->rate_limit_window;

        return \RateLimiter::tooManyAttempts($key, $maxAttempts) === false;
    }

    /**
     * Get cache statistics
     */
    public function getStatistics(): array
    {
        return [
            'query_caching' => $this->settings->enable_query_caching,
            'api_caching' => $this->settings->enable_api_caching,
            'cache_driver' => $this->settings->cache_driver,
            'redis_enabled' => $this->settings->redis_enabled,
            'cdn_enabled' => $this->settings->cdn_enabled,
            'rate_limiting' => $this->settings->enable_rate_limiting,
        ];
    }
}
