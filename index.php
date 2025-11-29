<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * This file serves as a fallback entry point when the web server's
 * document root cannot be configured to point to the public directory.
 * 
 * For security reasons, it's recommended to configure your web server
 * to point directly to the public directory instead of using this file.
 */

// Define the public path
define('LARAVEL_PUBLIC_PATH', __DIR__ . '/public');

// Forward the request to the public directory
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Remove leading slash
$uri = ltrim($uri, '/');

// Check if it's a request for a static file in the public directory
if ($uri && file_exists(LARAVEL_PUBLIC_PATH . '/' . $uri)) {
    // Serve static files directly
    $path = LARAVEL_PUBLIC_PATH . '/' . $uri;

    // Get the file extension
    $extension = pathinfo($path, PATHINFO_EXTENSION);

    // Set appropriate content type
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'webp' => 'image/webp',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'audio/ogg',
    ];

    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
    }

    readfile($path);
    exit;
}

// Otherwise, load the Laravel application
require_once LARAVEL_PUBLIC_PATH . '/index.php';
