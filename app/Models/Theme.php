<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'version',
        'author',
        'screenshot',
        'color_scheme',
        'features',
        'is_active',
        'is_default',
        'supports_landing_page',
        'supports_admin',
        'views_path',
        'assets_path',
    ];

    protected $casts = [
        'color_scheme' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'supports_landing_page' => 'boolean',
        'supports_admin' => 'boolean',
    ];

    /**
     * Get active theme
     */
    public static function getActive()
    {
        return Cache::remember('active_theme', 3600, function () {
            return self::where('is_active', true)->first() ?? self::where('is_default', true)->first();
        });
    }

    /**
     * Get default theme
     */
    public static function getDefault()
    {
        return self::where('is_default', true)->first();
    }

    /**
     * Activate this theme
     */
    public function activate()
    {
        // Deactivate all themes
        self::where('is_active', true)->update(['is_active' => false]);

        // Activate this theme
        $this->update(['is_active' => true]);

        // Clear cache
        Cache::forget('active_theme');

        return $this;
    }

    /**
     * Set as default theme
     */
    public function setAsDefault()
    {
        // Remove default from all themes
        self::where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);

        return $this;
    }

    /**
     * Get theme view path
     */
    public function getViewPath(string $view): string
    {
        return $this->views_path . '.' . $view;
    }

    /**
     * Get theme asset URL
     */
    public function getAssetUrl(string $asset): string
    {
        return asset($this->assets_path . '/' . $asset);
    }

    /**
     * Check if theme has feature
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get primary color
     */
    public function getPrimaryColorAttribute()
    {
        return $this->color_scheme['primary'] ?? '#FF6B6B';
    }

    /**
     * Get all landing page themes
     */
    public static function getLandingPageThemes()
    {
        return self::where('supports_landing_page', true)->get();
    }
}
