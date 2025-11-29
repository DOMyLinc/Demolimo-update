<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class StorageSetting extends Model
{
    protected $fillable = [
        'default_disk',
        'local_path',
        's3_enabled',
        's3_key',
        's3_secret',
        's3_region',
        's3_bucket',
        's3_url',
        's3_endpoint',
        'spaces_enabled',
        'spaces_key',
        'spaces_secret',
        'spaces_region',
        'spaces_bucket',
        'spaces_endpoint',
        'wasabi_enabled',
        'wasabi_key',
        'wasabi_secret',
        'wasabi_region',
        'wasabi_bucket',
        'wasabi_endpoint',
        'backblaze_enabled',
        'backblaze_key_id',
        'backblaze_app_key',
        'backblaze_bucket',
        'backblaze_region',
        'cdn_enabled',
        'cdn_url',
        'cdn_provider',
        'max_file_size',
        'max_image_size',
        'max_audio_size',
        'max_video_size',
    ];

    protected $casts = [
        's3_enabled' => 'boolean',
        'spaces_enabled' => 'boolean',
        'wasabi_enabled' => 'boolean',
        'backblaze_enabled' => 'boolean',
        'cdn_enabled' => 'boolean',
    ];

    /**
     * Get cached storage settings
     */
    public static function getCached()
    {
        return Cache::remember('storage_settings', 3600, function () {
            return self::first() ?? self::create([]);
        });
    }

    /**
     * Clear cache and update Laravel config when saved
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($settings) {
            Cache::forget('storage_settings');
            $settings->updateLaravelConfig();
        });
    }

    /**
     * Update Laravel filesystem configuration dynamically
     */
    public function updateLaravelConfig()
    {
        // Update S3 configuration
        if ($this->s3_enabled) {
            Config::set('filesystems.disks.s3', [
                'driver' => 's3',
                'key' => $this->s3_key,
                'secret' => $this->s3_secret,
                'region' => $this->s3_region,
                'bucket' => $this->s3_bucket,
                'url' => $this->s3_url,
                'endpoint' => $this->s3_endpoint,
            ]);
        }

        // Update DigitalOcean Spaces configuration
        if ($this->spaces_enabled) {
            Config::set('filesystems.disks.spaces', [
                'driver' => 's3',
                'key' => $this->spaces_key,
                'secret' => $this->spaces_secret,
                'region' => $this->spaces_region,
                'bucket' => $this->spaces_bucket,
                'endpoint' => $this->spaces_endpoint,
            ]);
        }

        // Update Wasabi configuration
        if ($this->wasabi_enabled) {
            Config::set('filesystems.disks.wasabi', [
                'driver' => 's3',
                'key' => $this->wasabi_key,
                'secret' => $this->wasabi_secret,
                'region' => $this->wasabi_region,
                'bucket' => $this->wasabi_bucket,
                'endpoint' => $this->wasabi_endpoint,
            ]);
        }

        // Update Backblaze configuration
        if ($this->backblaze_enabled) {
            Config::set('filesystems.disks.backblaze', [
                'driver' => 's3',
                'key' => $this->backblaze_key_id,
                'secret' => $this->backblaze_app_key,
                'region' => $this->backblaze_region,
                'bucket' => $this->backblaze_bucket,
                'endpoint' => "https://s3.{$this->backblaze_region}.backblazeb2.com",
            ]);
        }

        // Set default disk
        Config::set('filesystems.default', $this->default_disk);
    }

    /**
     * Get the active storage disk
     */
    public function getActiveDisk(): string
    {
        return $this->default_disk;
    }

    /**
     * Get CDN URL for an asset
     */
    public function getCdnUrl(string $path): string
    {
        if ($this->cdn_enabled && $this->cdn_url) {
            return rtrim($this->cdn_url, '/') . '/' . ltrim($path, '/');
        }

        return $path;
    }
}
