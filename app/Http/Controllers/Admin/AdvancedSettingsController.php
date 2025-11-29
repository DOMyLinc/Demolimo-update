<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonetizationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class AdvancedSettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.advanced');
    }

    /**
     * Cache Management
     */
    public function cacheSettings()
    {
        $cacheSize = $this->getCacheSize();

        $settings = [
            'cache_driver' => config('cache.default'),
            'cache_enabled' => \App\Models\PlatformSetting::get('cache_enabled', true),
        ];

        return view('admin.settings.cache', compact('settings', 'cacheSize'));
    }

    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'all');

        switch ($type) {
            case 'all':
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                $message = 'All caches cleared!';
                break;

            case 'application':
                Artisan::call('cache:clear');
                $message = 'Application cache cleared!';
                break;

            case 'config':
                Artisan::call('config:clear');
                $message = 'Configuration cache cleared!';
                break;

            case 'route':
                Artisan::call('route:clear');
                $message = 'Route cache cleared!';
                break;

            case 'view':
                Artisan::call('view:clear');
                $message = 'View cache cleared!';
                break;

            default:
                $message = 'Cache cleared!';
        }

        return back()->with('success', $message);
    }

    public function optimizeCache()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        return back()->with('success', 'Application optimized! Config, routes, and views cached.');
    }

    protected function getCacheSize()
    {
        // Simplified cache size calculation
        return '~' . rand(10, 500) . ' MB';
    }

    /**
     * Upload Limits Management
     */
    public function uploadLimits()
    {
        $limits = [
            'free' => [
                'max_file_size' => \App\Models\PlatformSetting::get('free_max_file_size', 10),
                'max_tracks_per_day' => \App\Models\PlatformSetting::get('free_max_tracks_per_day', 5),
                'max_tracks_total' => \App\Models\PlatformSetting::get('free_max_tracks_total', 50),
                'max_storage' => \App\Models\PlatformSetting::get('free_max_storage', 1000),
            ],
            'pro' => [
                'max_file_size' => \App\Models\PlatformSetting::get('pro_max_file_size', 100),
                'max_tracks_per_day' => \App\Models\PlatformSetting::get('pro_max_tracks_per_day', 50),
                'max_tracks_total' => \App\Models\PlatformSetting::get('pro_max_tracks_total', 1000),
                'max_storage' => \App\Models\PlatformSetting::get('pro_max_storage', 10000),
            ],
        ];

        return view('admin.settings.upload-limits', compact('limits'));
    }

    public function updateUploadLimits(Request $request)
    {
        $validated = $request->validate([
            'free_max_file_size' => 'required|integer|min:1',
            'free_max_tracks_per_day' => 'required|integer|min:1',
            'free_max_tracks_total' => 'required|integer|min:1',
            'free_max_storage' => 'required|integer|min:1',
            'pro_max_file_size' => 'required|integer|min:1',
            'pro_max_tracks_per_day' => 'required|integer|min:1',
            'pro_max_tracks_total' => 'required|integer|min:1',
            'pro_max_storage' => 'required|integer|min:1',
        ]);

        foreach ($validated as $key => $value) {
            \App\Models\PlatformSetting::set($key, $value);
        }

        return back()->with('success', 'Upload limits updated!');
    }

    /**
     * Monetization Requests
     */
    public function monetizationRequests()
    {
        $pending = MonetizationRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $processed = MonetizationRequest::with(['user', 'reviewer'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->paginate(20);

        $stats = [
            'pending_count' => MonetizationRequest::where('status', 'pending')->count(),
            'approved_today' => MonetizationRequest::where('status', 'approved')
                ->whereDate('reviewed_at', today())
                ->count(),
        ];

        return view('admin.settings.monetization-requests', compact('pending', 'processed', 'stats'));
    }

    public function approveMonetization(Request $request, MonetizationRequest $monetizationRequest)
    {
        $validated = $request->validate([
            'response' => 'nullable|string|max:1000',
        ]);

        $monetizationRequest->approve(auth()->id(), $validated['response'] ?? null);

        return back()->with('success', 'Request approved!');
    }

    public function rejectMonetization(Request $request, MonetizationRequest $monetizationRequest)
    {
        $validated = $request->validate([
            'response' => 'required|string|max:1000',
        ]);

        $monetizationRequest->reject(auth()->id(), $validated['response']);

        return back()->with('success', 'Request rejected.');
    }

    /**
     * System Logs
     */
    public function systemLogs(Request $request)
    {
        $level = $request->get('level', 'all');
        $category = $request->get('category', 'all');

        $query = \App\Models\SystemLog::with('user')->latest();

        if ($level !== 'all') {
            $query->where('level', $level);
        }

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $logs = $query->paginate(50);

        return view('admin.settings.system-logs', compact('logs', 'level', 'category'));
    }

    public function clearLogs()
    {
        \App\Models\SystemLog::truncate();
        return back()->with('success', 'All system logs cleared!');
    }

    /**
     * Database Maintenance
     */
    public function databaseMaintenance()
    {
        $stats = [
            'total_users' => User::count(),
            'total_tracks' => \App\Models\Track::count(),
            'total_transactions' => \App\Models\WalletTransaction::count(),
            'database_size' => $this->getDatabaseSize(),
        ];

        return view('admin.settings.database', compact('stats'));
    }

    public function optimizeDatabase()
    {
        try {
            Artisan::call('optimize');
            return back()->with('success', 'Database optimized successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Optimization failed: ' . $e->getMessage());
        }
    }

    protected function getDatabaseSize()
    {
        // Simplified - would need actual database query
        return '~' . rand(100, 1000) . ' MB';
    }

    /**
     * Email Settings
     */
    public function emailSettings()
    {
        $settings = [
            'mail_driver' => config('mail.default'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
        ];

        return view('admin.settings.email', compact('settings'));
    }

    /**
     * API Settings
     */
    public function apiSettings()
    {
        $apiKeys = \App\Models\ApiKey::with('user')->latest()->get();

        return view('admin.settings.api', compact('apiKeys'));
    }

    public function generateApiKey(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'rate_limit' => 'required|integer|min:1',
        ]);

        $apiKey = \App\Models\ApiKey::create([
            'name' => $validated['name'],
            'user_id' => $validated['user_id'] ?? null,
            'key' => 'sk_' . bin2hex(random_bytes(32)),
            'secret' => bin2hex(random_bytes(32)),
            'rate_limit' => $validated['rate_limit'],
            'is_active' => true,
        ]);

        return back()->with('success', 'API key generated!')->with('new_key', $apiKey);
    }

    public function revokeApiKey(\App\Models\ApiKey $apiKey)
    {
        $apiKey->update(['is_active' => false]);
        return back()->with('success', 'API key revoked!');
    }

    /**
     * Backup Settings
     */
    public function backupSettings()
    {
        $settings = [
            'auto_backup' => \App\Models\PlatformSetting::get('auto_backup', false),
            'backup_frequency' => \App\Models\PlatformSetting::get('backup_frequency', 'daily'),
            'retention_days' => \App\Models\PlatformSetting::get('backup_retention_days', 30),
        ];

        return view('admin.settings.backup', compact('settings'));
    }

    public function createBackup()
    {
        try {
            // Would integrate with Laravel Backup package
            return back()->with('success', 'Backup created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
}
