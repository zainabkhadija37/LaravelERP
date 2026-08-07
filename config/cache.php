<?php

return [
    'default' => env('CACHE_DRIVER', 'redis'),

    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'laravel_erp_cache_'),
];
