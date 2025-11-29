<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CacheSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CacheSettingsController extends Controller
{
    public function index()
    {
        $settings = CacheSetting::first() ?? new CacheSetting();
        $stats = $settings->getStatistics();

        return view('admin.cache.index', compact('settings', 'stats'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'cache_driver' => 'required|in:file,redis,memcached',
            'cache_enabled' => 'boolean',

            // Redis
            'redis_enabled' => 'boolean',
            'redis_host' => 'nullable|string',
            'redis_port' => 'nullable|integer',
            'redis_password' => 'nullable|string',
            'redis_database' => 'nullable|integer',

            // Memcached
            'memcached_enabled' => 'boolean',
            'memcached_host' => 'nullable|string',
            'memcached_port' => 'nullable|integer',

            // TTL Settings
            'default_ttl' => 'required|integer|min:60',
            'query_cache_ttl' => 'required|integer|min:60',
            'api_cache_ttl' => 'required|integer|min:30',
            'static_cache_ttl' => 'required|integer|min:3600',

            // Optimization
            'enable_query_caching' => 'boolean',
            'enable_api_caching' => 'boolean',
            'enable_view_caching' => 'boolean',
            'enable_route_caching' => 'boolean',
            'enable_config_caching' => 'boolean',

            // CDN
            'cdn_enabled' => 'boolean',
            'cdn_url' => 'nullable|url',
            'cdn_assets' => 'nullable|array',

            // Performance
            'enable_compression' => 'boolean',
            'enable_minification' => 'boolean',
            'enable_lazy_loading' => 'boolean',

            // Rate Limiting
            'enable_rate_limiting' => 'boolean',
            'rate_limit_requests' => 'required|integer|min:10',
            'rate_limit_window' => 'required|integer|min:1',
        ]);

        $settings = CacheSetting::first() ?? new CacheSetting();
        $settings->fill($validated);
        $settings->save();

        return back()->with('success', 'Cache settings updated successfully!');
    }

    public function testRedis()
    {
        try {
            $settings = CacheSetting::first();

            if (!$settings->redis_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Redis is not enabled in settings.',
                ]);
            }

            $connected = $settings->testRedisConnection();

            if ($connected) {
                return response()->json([
                    'success' => true,
                    'message' => 'Redis connection successful!',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Could not connect to Redis. Please check your configuration.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Redis connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function clearCache(Request $request)
    {
        try {
            $type = $request->input('type', 'all');

            switch ($type) {
                case 'application':
                    Cache::flush();
                    $message = 'Application cache cleared!';
                    break;

                case 'route':
                    Artisan::call('route:clear');
                    $message = 'Route cache cleared!';
                    break;

                case 'config':
                    Artisan::call('config:clear');
                    $message = 'Configuration cache cleared!';
                    break;

                case 'view':
                    Artisan::call('view:clear');
                    $message = 'View cache cleared!';
                    break;

                case 'all':
                default:
                    $settings = CacheSetting::first();
                    $settings->clearAllCaches();
                    $message = 'All caches cleared!';
                    break;
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function optimizeCache()
    {
        try {
            $settings = CacheSetting::first();

            if ($settings->enable_route_caching) {
                Artisan::call('route:cache');
            }

            if ($settings->enable_config_caching) {
                Artisan::call('config:cache');
            }

            if ($settings->enable_view_caching) {
                Artisan::call('view:cache');
            }

            return back()->with('success', 'Cache optimization completed!');
        } catch (\Exception $e) {
            return back()->with('error', 'Cache optimization failed: ' . $e->getMessage());
        }
    }

    public function getStats()
    {
        try {
            $settings = CacheSetting::first();
            $stats = $settings->getStatistics();

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
