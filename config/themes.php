<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Available Themes
    |--------------------------------------------------------------------------
    */
    'themes' => [
        'lava' => [
            'name' => 'Lava',
            'description' => 'Fiery red and orange theme with volcanic energy',
            'colors' => [
                'primary' => '#DC2626',      // Red-600
                'secondary' => '#EA580C',    // Orange-600
                'accent' => '#F59E0B',       // Amber-500
                'background' => '#1C0A0A',   // Very dark red
                'surface' => '#2D0F0F',      // Dark red
                'text' => '#FFFFFF',
                'text_secondary' => '#FCA5A5', // Red-300
                'border' => '#7F1D1D',       // Red-900
                'success' => '#F59E0B',      // Amber-500
                'warning' => '#FB923C',      // Orange-400
                'error' => '#DC2626',        // Red-600
                'gradient_start' => '#DC2626',
                'gradient_end' => '#EA580C',
            ],
            'icon' => '🔥',
            'preview_image' => '/themes/lava-preview.jpg',
        ],

        'default' => [
            'name' => 'Purple Dream',
            'description' => 'Modern purple and blue gradient theme',
            'colors' => [
                'primary' => '#667eea',
                'secondary' => '#764ba2',
                'accent' => '#f093fb',
                'background' => '#0a0a0f',
                'surface' => '#1a1a2e',
                'text' => '#FFFFFF',
                'text_secondary' => '#a0aec0',
                'border' => '#2d3748',
                'success' => '#10b981',
                'warning' => '#f59e0b',
                'error' => '#ef4444',
                'gradient_start' => '#667eea',
                'gradient_end' => '#764ba2',
            ],
            'icon' => '💜',
            'preview_image' => '/themes/default-preview.jpg',
        ],

        'ocean' => [
            'name' => 'Ocean Blue',
            'description' => 'Cool and calming ocean-inspired theme',
            'colors' => [
                'primary' => '#0ea5e9',      // Sky-500
                'secondary' => '#06b6d4',    // Cyan-500
                'accent' => '#22d3ee',       // Cyan-400
                'background' => '#020617',   // Slate-950
                'surface' => '#0f172a',      // Slate-900
                'text' => '#FFFFFF',
                'text_secondary' => '#94a3b8', // Slate-400
                'border' => '#1e293b',       // Slate-800
                'success' => '#10b981',
                'warning' => '#f59e0b',
                'error' => '#ef4444',
                'gradient_start' => '#0ea5e9',
                'gradient_end' => '#06b6d4',
            ],
            'icon' => '🌊',
            'preview_image' => '/themes/ocean-preview.jpg',
        ],

        'forest' => [
            'name' => 'Forest Green',
            'description' => 'Natural and earthy forest theme',
            'colors' => [
                'primary' => '#10b981',      // Emerald-500
                'secondary' => '#059669',    // Emerald-600
                'accent' => '#34d399',       // Emerald-400
                'background' => '#022c22',   // Very dark green
                'surface' => '#064e3b',      // Emerald-900
                'text' => '#FFFFFF',
                'text_secondary' => '#6ee7b7', // Emerald-300
                'border' => '#065f46',       // Emerald-800
                'success' => '#10b981',
                'warning' => '#f59e0b',
                'error' => '#ef4444',
                'gradient_start' => '#10b981',
                'gradient_end' => '#059669',
            ],
            'icon' => '🌲',
            'preview_image' => '/themes/forest-preview.jpg',
        ],

        'sunset' => [
            'name' => 'Sunset Glow',
            'description' => 'Warm sunset colors with orange and pink',
            'colors' => [
                'primary' => '#f97316',      // Orange-500
                'secondary' => '#ec4899',    // Pink-500
                'accent' => '#fbbf24',       // Amber-400
                'background' => '#1c1917',   // Stone-900
                'surface' => '#292524',      // Stone-800
                'text' => '#FFFFFF',
                'text_secondary' => '#fca5a5', // Red-300
                'border' => '#44403c',       // Stone-700
                'success' => '#10b981',
                'warning' => '#f59e0b',
                'error' => '#ef4444',
                'gradient_start' => '#f97316',
                'gradient_end' => '#ec4899',
            ],
            'icon' => '🌅',
            'preview_image' => '/themes/sunset-preview.jpg',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Theme
    |--------------------------------------------------------------------------
    */
    'default' => env('APP_THEME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Theme Cache
    |--------------------------------------------------------------------------
    */
    'cache_enabled' => env('THEME_CACHE_ENABLED', true),
    'cache_duration' => 3600, // 1 hour
];
