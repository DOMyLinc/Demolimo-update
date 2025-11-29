<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        'vultr' => [
            'driver' => 's3',
            'key' => env('VULTR_ACCESS_KEY'),
            'secret' => env('VULTR_SECRET_KEY'),
            'region' => env('VULTR_REGION'),
            'bucket' => env('VULTR_BUCKET'),
            'endpoint' => env('VULTR_ENDPOINT'),
            'use_path_style_endpoint' => true,
        ],

        'spaces' => [
            'driver' => 's3',
            'key' => env('DO_ACCESS_KEY'),
            'secret' => env('DO_SECRET_KEY'),
            'region' => env('DO_REGION'),
            'bucket' => env('DO_BUCKET'),
            'endpoint' => env('DO_ENDPOINT'),
            'use_path_style_endpoint' => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
