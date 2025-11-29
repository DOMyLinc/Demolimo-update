<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageSettingsController extends Controller
{
    public function index()
    {
        $settings = StorageSetting::first() ?? new StorageSetting();

        return view('admin.storage.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_disk' => 'required|in:local,s3,spaces,wasabi,backblaze',

            // S3
            's3_enabled' => 'boolean',
            's3_key' => 'nullable|string',
            's3_secret' => 'nullable|string',
            's3_region' => 'nullable|string',
            's3_bucket' => 'nullable|string',
            's3_url' => 'nullable|url',
            's3_endpoint' => 'nullable|url',

            // Spaces
            'spaces_enabled' => 'boolean',
            'spaces_key' => 'nullable|string',
            'spaces_secret' => 'nullable|string',
            'spaces_region' => 'nullable|string',
            'spaces_bucket' => 'nullable|string',
            'spaces_endpoint' => 'nullable|url',

            // Wasabi
            'wasabi_enabled' => 'boolean',
            'wasabi_key' => 'nullable|string',
            'wasabi_secret' => 'nullable|string',
            'wasabi_region' => 'nullable|string',
            'wasabi_bucket' => 'nullable|string',
            'wasabi_endpoint' => 'nullable|url',

            // Backblaze
            'backblaze_enabled' => 'boolean',
            'backblaze_key_id' => 'nullable|string',
            'backblaze_app_key' => 'nullable|string',
            'backblaze_bucket' => 'nullable|string',
            'backblaze_region' => 'nullable|string',

            // CDN
            'cdn_enabled' => 'boolean',
            'cdn_url' => 'nullable|url',
            'cdn_provider' => 'nullable|string',

            // Upload Limits
            'max_file_size' => 'required|integer|min:1',
            'max_image_size' => 'required|integer|min:1',
            'max_audio_size' => 'required|integer|min:1',
            'max_video_size' => 'required|integer|min:1',
        ]);

        $settings = StorageSetting::first() ?? new StorageSetting();
        $settings->fill($validated);
        $settings->save();

        return back()->with('success', 'Storage settings updated successfully!');
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'disk' => 'required|in:s3,spaces,wasabi,backblaze',
        ]);

        try {
            $disk = $request->disk;

            // Temporarily configure the disk
            $settings = StorageSetting::first();
            $settings->updateLaravelConfig();

            // Try to write a test file
            $testContent = 'Storage test file - ' . now();
            $testPath = 'test-' . time() . '.txt';

            Storage::disk($disk)->put($testPath, $testContent);

            // Try to read it back
            $content = Storage::disk($disk)->get($testPath);

            // Delete the test file
            Storage::disk($disk)->delete($testPath);

            if ($content === $testContent) {
                return response()->json([
                    'success' => true,
                    'message' => ucfirst($disk) . ' connection successful!',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: Could not verify file content.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function getStorageStats()
    {
        try {
            $settings = StorageSetting::first();
            $disk = $settings->default_disk;

            $stats = [
                'disk' => $disk,
                'files_count' => 0,
                'total_size' => 0,
                'formatted_size' => '0 B',
            ];

            if ($disk === 'local') {
                $path = storage_path('app/public');
                if (is_dir($path)) {
                    $stats['files_count'] = count(glob($path . '/*.*'));
                    $stats['total_size'] = $this->getDirSize($path);
                    $stats['formatted_size'] = $this->formatBytes($stats['total_size']);
                }
            }

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function getDirSize($path)
    {
        $size = 0;
        foreach (glob(rtrim($path, '/') . '/*', GLOB_NOSORT) as $file) {
            $size += is_file($file) ? filesize($file) : $this->getDirSize($file);
        }
        return $size;
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
