<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ThemeManager
{
    protected $themes = [
        'light' => [
            'name' => 'Light Mode',
            'class' => 'theme-light',
            'colors' => ['primary' => '#3b82f6', 'bg' => '#ffffff', 'text' => '#1f2937']
        ],
        'dark' => [
            'name' => 'Dark Mode',
            'class' => 'theme-dark',
            'colors' => ['primary' => '#60a5fa', 'bg' => '#111827', 'text' => '#f3f4f6']
        ],
        'cyber' => [
            'name' => 'Cyberpunk',
            'class' => 'theme-cyber',
            'colors' => ['primary' => '#f472b6', 'bg' => '#0f172a', 'text' => '#e2e8f0']
        ]
    ];

    public function getActiveTheme()
    {
        return Cache::get('admin_theme', 'light');
    }

    public function setActiveTheme($themeKey)
    {
        if (array_key_exists($themeKey, $this->themes)) {
            Cache::put('admin_theme', $themeKey);
            return true;
        }
        return false;
    }

    public function getThemes()
    {
        return $this->themes;
    }
}
