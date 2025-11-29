<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class CacheSetting extends Model
{
    protected $fillable = [
        'cache_driver',
        'cache_enabled',
        'redis_enabled',
        'redis_host',
        'redis_port',
        'redis_password',
        'redis_database',
        'memcached_enabled',
        'memcached_host',
        'memcached_port',
        'default_ttl',
        'query_cache_ttl',
        'api_cache_ttl',
        'static_cache_ttl',
        'enable_query_caching',
        'enable_api_caching',
        'enable_view_caching',
        'enable_route_caching',
        'enable_config_caching',
        'cdn_enabled',
        'cdn_url',
        'cdn_assets',
        'enable_compression',
        'enable_minification',
        'enable_lazy_loading',
        'enable_rate_limiting',
        'rate_limit_requests',
        'rate_limit_window',
        'load_balancing_enabled',
        'load_balancer_nodes',
    ];

    protected $casts = [
        'cache_enabled' => 'boolean',
        'redis_enabled' => 'boolean',
        'memcached_enabled' => 'boolean',
        'enable_query_caching' => 'boolean',
        'enable_api_caching' => 'boolean',
        'enable_view_caching' => 'boolean',
        'enable_route_caching' => 'boolean',
        'enable_config_caching' => 'boolean',
        'cdn_enabled' => 'boolean',
        'cdn_assets' => 'array',
        'enable_compression' => 'boolean',
        'enable_minification' => 'boolean',
        'enable_lazy_loading' => 'boolean',
        'enable_rate_limiting' => 'boolean',
        'load_balancing_enabled' => 'boolean',
        'load_balancer_nodes' => 'array',
    ];

    /**
     * Get cached settings
     */
    public static function getCached()
    {
        return Cache::remember('cache_settings', 3600, function () {
            return self::first() ?? self::create([]);
        });
    }

    /**
     * Update Laravel cache configuration
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($settings) {
            Cache::forget('cache_settings');
            $settings->updateLaravelConfig();
        });
    }

    /**
     * Update Laravel configuration dynamically
     */
    public function updateLaravelConfig()
    {
        // Update cache driver
        Config::set('cache.default', $this->cache_driver);

        // Update Redis configuration
        if ($this->redis_enabled) {
            Config::set('database.redis.default', [
                'host' => $this->redis_host,
                'password' => $this->redis_password,
                'port' => $this->redis_port,
                'database' => $this->redis_database,
            ]);
        }

        // Update Memcached configuration
        if ($this->memcached_enabled) {
            Config::set('cache.stores.memcached.servers', [
                [
                    'host' => $this->memcached_host,
                    'port' => $this->memcached_port,
                    'weight' => 100,
                ],
            ]);
        }
    }

    /**
     * Test Redis connection
     */
    public function testRedisConnection(): bool
    {
        try {
            if (!$this->redis_enabled) {
                return false;
            }

            $redis = new \Redis();
            $connected = $redis->connect($this->redis_host, $this->redis_port, 2);

            if ($connected && $this->redis_password) {
                $redis->auth($this->redis_password);
            }

            if ($connected) {
                $redis->ping();
                $redis->close();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public function getStatistics(): array
    {
        $stats = [
            'driver' => $this->cache_driver,
            'enabled' => $this->cache_enabled,
            'redis_connected' => false,
            'memory_usage' => 0,
            'hit_rate' => 0,
        ];

        if ($this->redis_enabled) {
            try {
                $stats['redis_connected'] = $this->testRedisConnection();

                if ($stats['redis_connected']) {
                    $redis = Redis::connection();
                    $info = $redis->info();
                    $stats['memory_usage'] = $info['used_memory_human'] ?? 'N/A';
                    $stats['hit_rate'] = $this->calculateHitRate($info);
                }
            } catch (\Exception $e) {
                $stats['redis_connected'] = false;
            }
        }

        return $stats;
    }

    /**
     * Calculate cache hit rate
     */
    protected function calculateHitRate(array $info): float
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    /**
     * Clear all caches
     */
    public function clearAllCaches(): bool
    {
        try {
            Cache::flush();

            if ($this->enable_route_caching) {
                \Artisan::call('route:clear');
            }

            if ($this->enable_config_caching) {
                \Artisan::call('config:clear');
            }

            if ($this->enable_view_caching) {
                \Artisan::call('view:clear');
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
