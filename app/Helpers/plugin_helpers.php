<?php

/**
 * Plugin Helper Functions
 * 
 * WordPress-style helper functions for the plugin system.
 * These functions provide a simple API for plugins to interact with the application.
 */

if (!function_exists('do_action')) {
    /**
     * Execute functions hooked on a specific action hook.
     *
     * @param string $hook The name of the action to be executed.
     * @param mixed ...$args Optional additional arguments which are passed on to the functions hooked to the action.
     * @return void
     */
    function do_action(string $hook, ...$args): void
    {
        app('plugin.manager')->doAction($hook, ...$args);
    }
}

if (!function_exists('apply_filters')) {
    /**
     * Call the functions added to a filter hook.
     *
     * @param string $hook The name of the filter hook.
     * @param mixed $value The value to filter.
     * @param mixed ...$args Optional additional arguments to pass to the callback functions.
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    function apply_filters(string $hook, $value, ...$args)
    {
        return app('plugin.manager')->applyFilters($hook, $value, ...$args);
    }
}

if (!function_exists('add_action')) {
    /**
     * Add a function to an action hook.
     *
     * @param string $hook The name of the action to which the $callback is hooked.
     * @param callable $callback The callback to be run when the action is called.
     * @param int $priority Optional. Used to specify the order in which the functions are executed. Default 10.
     * @return void
     */
    function add_action(string $hook, callable $callback, int $priority = 10): void
    {
        app('plugin.manager')->addAction($hook, $callback, $priority);
    }
}

if (!function_exists('add_filter')) {
    /**
     * Add a function to a filter hook.
     *
     * @param string $hook The name of the filter to hook the $callback to.
     * @param callable $callback The callback to be run when the filter is applied.
     * @param int $priority Optional. Used to specify the order in which the functions are executed. Default 10.
     * @return void
     */
    function add_filter(string $hook, callable $callback, int $priority = 10): void
    {
        app('plugin.manager')->addFilter($hook, $callback, $priority);
    }
}

if (!function_exists('plugin_url')) {
    /**
     * Get the URL to a plugin file.
     *
     * @param string $slug Plugin slug
     * @param string $path Path relative to plugin directory
     * @return string
     */
    function plugin_url(string $slug, string $path = ''): string
    {
        return asset('plugins/' . $slug . '/' . ltrim($path, '/'));
    }
}

if (!function_exists('plugin_path')) {
    /**
     * Get the absolute path to a plugin file.
     *
     * @param string $slug Plugin slug
     * @param string $path Path relative to plugin directory
     * @return string
     */
    function plugin_path(string $slug, string $path = ''): string
    {
        return base_path('plugins/' . $slug . '/' . ltrim($path, '/'));
    }
}

if (!function_exists('get_plugin_data')) {
    /**
     * Get plugin data from plugin.json
     *
     * @param string $slug Plugin slug
     * @return array|null
     */
    function get_plugin_data(string $slug): ?array
    {
        $jsonPath = plugin_path($slug, 'plugin.json');

        if (!file_exists($jsonPath)) {
            return null;
        }

        return json_decode(file_get_contents($jsonPath), true);
    }
}

if (!function_exists('plugin_setting')) {
    /**
     * Get a plugin setting value.
     *
     * @param string $slug Plugin slug
     * @param string $key Setting key
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed
     */
    function plugin_setting(string $slug, string $key, $default = null)
    {
        return app('plugin.settings')->get($slug, $key, $default);
    }
}

if (!function_exists('update_plugin_setting')) {
    /**
     * Update a plugin setting value.
     *
     * @param string $slug Plugin slug
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    function update_plugin_setting(string $slug, string $key, $value): bool
    {
        return app('plugin.settings')->set($slug, $key, $value);
    }
}

if (!function_exists('enqueue_style')) {
    /**
     * Enqueue a stylesheet.
     *
     * @param string $handle Unique identifier
     * @param string $src URL to the stylesheet
     * @param array $deps Dependencies
     * @param string|null $version Version number
     * @param string $media Media type
     * @return void
     */
    function enqueue_style(string $handle, string $src, array $deps = [], ?string $version = null, string $media = 'all'): void
    {
        app('plugin.assets')->enqueueStyle($handle, $src, $deps, $version, $media);
    }
}

if (!function_exists('enqueue_script')) {
    /**
     * Enqueue a script.
     *
     * @param string $handle Unique identifier
     * @param string $src URL to the script
     * @param array $deps Dependencies
     * @param string|null $version Version number
     * @param bool $inFooter Load in footer
     * @return void
     */
    function enqueue_script(string $handle, string $src, array $deps = [], ?string $version = null, bool $inFooter = true): void
    {
        app('plugin.assets')->enqueueScript($handle, $src, $deps, $version, $inFooter);
    }
}

if (!function_exists('add_inline_style')) {
    /**
     * Add inline CSS.
     *
     * @param string $handle Unique identifier
     * @param string $css CSS code
     * @return void
     */
    function add_inline_style(string $handle, string $css): void
    {
        app('plugin.assets')->addInlineStyle($handle, $css);
    }
}

if (!function_exists('add_inline_script')) {
    /**
     * Add inline JavaScript.
     *
     * @param string $handle Unique identifier
     * @param string $js JavaScript code
     * @return void
     */
    function add_inline_script(string $handle, string $js): void
    {
        app('plugin.assets')->addInlineScript($handle, $js);
    }
}
