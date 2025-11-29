<?php

if (!function_exists('active_theme')) {
    /**
     * Get the active theme
     */
    function active_theme(): ?\App\Models\ThemeSetting
    {
        return \App\Models\ThemeSetting::getActiveTheme();
    }
}

if (!function_exists('theme_color')) {
    /**
     * Get a theme color
     */
    function theme_color(string $key, string $default = '#667eea'): string
    {
        $colors = \App\Models\ThemeSetting::getActiveColors();
        return $colors[$key] ?? $default;
    }
}

if (!function_exists('theme_gradient')) {
    /**
     * Get theme gradient CSS
     */
    function theme_gradient(): string
    {
        $start = theme_color('gradient_start');
        $end = theme_color('gradient_end');
        return "linear-gradient(135deg, {$start} 0%, {$end} 100%)";
    }
}

if (!function_exists('theme_class')) {
    /**
     * Get theme-specific class
     */
    function theme_class(string $base = ''): string
    {
        $theme = active_theme();
        $themeKey = $theme ? $theme->key : 'default';
        return $base ? "{$base}-{$themeKey}" : $themeKey;
    }
}
