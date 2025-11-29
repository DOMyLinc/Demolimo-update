<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    */
    'allowed_types' => [
        'audio' => explode(',', env('ALLOWED_AUDIO_TYPES', 'mp3,wav,flac,aac,ogg,m4a')),
        'image' => explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,webp,gif')),
        'video' => explode(',', env('ALLOWED_VIDEO_TYPES', 'mp4,webm,ogg')),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Size Limits (in bytes)
    |--------------------------------------------------------------------------
    */
    'max_sizes' => [
        'audio' => env('MAX_AUDIO_SIZE', 52428800), // 50MB
        'image' => env('MAX_IMAGE_SIZE', 5242880),  // 5MB
        'video' => env('MAX_VIDEO_SIZE', 104857600), // 100MB
    ],

    /*
    |--------------------------------------------------------------------------
    | MIME Type Validation
    |--------------------------------------------------------------------------
    */
    'mime_types' => [
        'audio' => [
            'audio/mpeg',
            'audio/wav',
            'audio/wave',
            'audio/x-wav',
            'audio/flac',
            'audio/aac',
            'audio/ogg',
            'audio/mp4',
        ],
        'image' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ],
        'video' => [
            'video/mp4',
            'video/webm',
            'video/ogg',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Validation
    |--------------------------------------------------------------------------
    */
    'image' => [
        'max_width' => 4000,
        'max_height' => 4000,
        'min_width' => 100,
        'min_height' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Virus Scanning
    |--------------------------------------------------------------------------
    */
    'virus_scan' => [
        'enabled' => env('VIRUS_SCAN_ENABLED', false),
        'clamav_socket' => env('CLAMAV_SOCKET', '/var/run/clamav/clamd.ctl'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk' => env('UPLOAD_DISK', 'public'),
        'path' => 'uploads',
        'generate_random_names' => true,
    ],
];
