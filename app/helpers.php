<?php

// Base path should point to the project root (one level up from the app directory)
if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        $base = realpath(__DIR__ . '/..');
        return $base . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

// Simple placeholder for the application container
if (!function_exists('app')) {
    function app($abstract = null)
    {
        static $appInstance = null;
        if ($abstract === null) {
            return $appInstance;
        }
        // In a full Laravel app this would resolve from the container.
        return null;
    }
}

// Helper for SQLite database path
if (!function_exists('database_path')) {
    function database_path($path = '')
    {
        return base_path('database' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

// Helper for storage path (used by cache and view compilation)
if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

// Helper for resources path
if (!function_exists('resource_path')) {
    function resource_path($path = '')
    {
        return base_path('resources' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}
