<?php
/**
 * Plugin Name: Demo Plugin
 * Plugin URI: https://demolimo.com/plugins/demo-plugin
 * Description: A sample plugin demonstrating the plugin system capabilities
 * Version: 1.0.0
 * Author: DemoLimo Team
 * Author URI: https://demolimo.com
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin initialization
 */
add_action('app.booted', function () {
    // Plugin is loaded and ready
    \Log::info('Demo Plugin: Initialized');
});

/**
 * Hook into user registration
 */
add_action('user.registered', function ($user) {
    $welcomeMessage = plugin_setting('demo-plugin', 'welcome_message', 'Welcome!');

    \Log::info("Demo Plugin: New user registered - {$user->name}");
    \Log::info("Demo Plugin: Welcome message - {$welcomeMessage}");

    // You could send a custom email, create a notification, etc.
});

/**
 * Hook into track upload
 */
add_action('track.uploaded', function ($track) {
    \Log::info("Demo Plugin: Track uploaded - {$track->title} by {$track->user->name}");

    // You could process the track, send notifications, etc.
});

/**
 * Add custom menu item to admin panel
 */
add_filter('admin.menu', function ($menu) {
    $menu[] = [
        'title' => 'Demo Plugin',
        'url' => route('admin.demo-plugin.index'),
        'icon' => 'fa-plug',
    ];

    return $menu;
});

/**
 * Modify track metadata before display
 */
add_filter('track.metadata', function ($metadata, $track) {
    // Add custom metadata
    $metadata['processed_by_demo_plugin'] = true;
    $metadata['plugin_version'] = '1.0.0';

    return $metadata;
}, 10);

/**
 * Enqueue plugin assets
 */
add_action('admin.enqueue_scripts', function () {
    enqueue_style(
        'demo-plugin-admin',
        plugin_url('demo-plugin', 'resources/css/admin.css'),
        [],
        '1.0.0'
    );

    enqueue_script(
        'demo-plugin-admin',
        plugin_url('demo-plugin', 'resources/js/admin.js'),
        ['jquery'],
        '1.0.0',
        true
    );
});

/**
 * Add inline styles
 */
add_action('wp_head', function () {
    add_inline_style('demo-plugin-custom', '
        .demo-plugin-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
        }
    ');
});
