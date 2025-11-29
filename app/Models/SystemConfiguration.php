<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemConfiguration extends Model
{
    protected $fillable = [
        'key',
        'category',
        'value',
        'type',
        'description',
        'is_sensitive',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
    ];

    /**
     * Get a configuration value
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("config.{$key}", function () use ($key, $default) {
            $config = self::where('key', $key)->first();

            if (!$config) {
                return $default;
            }

            return self::castValue($config->value, $config->type);
        });
    }

    /**
     * Set a configuration value
     */
    public static function set(string $key, $value): void
    {
        $config = self::where('key', $key)->first();

        if ($config) {
            $config->update(['value' => $value]);
        } else {
            self::create([
                'key' => $key,
                'value' => $value,
                'category' => 'custom',
                'type' => 'string',
            ]);
        }

        Cache::forget("config.{$key}");
    }

    /**
     * Cast value to appropriate type
     */
    private static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Get all configurations by category
     */
    public static function getByCategory(string $category)
    {
        return self::where('category', $category)->get()->mapWithKeys(function ($config) {
            return [$config->key => self::castValue($config->value, $config->type)];
        });
    }

    /**
     * Check if FFMPEG is available
     */
    public static function isFFMPEGAvailable(): bool
    {
        if (!self::get('ffmpeg_enabled', false)) {
            return false;
        }

        $path = self::get('ffmpeg_path');

        if (!$path || !file_exists($path)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a feature is enabled
     */
    public static function isFeatureEnabled(string $feature, bool $default = false): bool
    {
        return (bool) self::get("{$feature}_enabled", $default);
    }
}
