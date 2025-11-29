<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ThemeSetting extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'is_active',
        'colors',
        'icon',
        'preview_image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'colors' => 'array',
    ];

    /**
     * Get the active theme
     */
    public static function getActiveTheme(): ?self
    {
        return Cache::remember('active_theme', 3600, function () {
            return self::where('is_active', true)->first();
        });
    }

    /**
     * Activate this theme
     */
    public function activate(): void
    {
        // Deactivate all themes
        self::query()->update(['is_active' => false]);

        // Activate this theme
        $this->update(['is_active' => true]);

        // Clear cache
        Cache::forget('active_theme');
        Cache::forget('theme_colors');
    }

    /**
     * Get theme colors merged with config
     */
    public function getColors(): array
    {
        $configColors = config("themes.themes.{$this->key}.colors", []);
        return array_merge($configColors, $this->colors ?? []);
    }

    /**
     * Get all theme colors for CSS generation
     */
    public static function getActiveColors(): array
    {
        return Cache::remember('theme_colors', 3600, function () {
            $theme = self::getActiveTheme();
            if (!$theme) {
                return config('themes.themes.default.colors', []);
            }
            return $theme->getColors();
        });
    }
}
