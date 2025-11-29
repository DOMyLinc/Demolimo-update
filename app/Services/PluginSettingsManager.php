<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\Cache;

class PluginSettingsManager
{
    /**
     * Get a plugin setting value
     *
     * @param string $slug Plugin slug
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $slug, string $key, $default = null)
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            return $default;
        }

        $settings = $plugin->settings ?? [];

        return $settings[$key] ?? $default;
    }

    /**
     * Set a plugin setting value
     *
     * @param string $slug Plugin slug
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public function set(string $slug, string $key, $value): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            return false;
        }

        $settings = $plugin->settings ?? [];
        $settings[$key] = $value;

        $plugin->update(['settings' => $settings]);

        // Clear cache
        Cache::forget("plugin_settings_{$slug}");

        return true;
    }

    /**
     * Get all settings for a plugin
     *
     * @param string $slug Plugin slug
     * @return array
     */
    public function all(string $slug): array
    {
        $plugin = Plugin::where('slug', $slug)->first();

        return $plugin->settings ?? [];
    }

    /**
     * Delete a plugin setting
     *
     * @param string $slug Plugin slug
     * @param string $key Setting key
     * @return bool
     */
    public function delete(string $slug, string $key): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            return false;
        }

        $settings = $plugin->settings ?? [];
        unset($settings[$key]);

        $plugin->update(['settings' => $settings]);

        Cache::forget("plugin_settings_{$slug}");

        return true;
    }

    /**
     * Clear all settings for a plugin
     *
     * @param string $slug Plugin slug
     * @return bool
     */
    public function clear(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            return false;
        }

        $plugin->update(['settings' => []]);

        Cache::forget("plugin_settings_{$slug}");

        return true;
    }
}
