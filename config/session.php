<?php

return [
    'driver' => env('SESSION_DRIVER', 'redis'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'connection' => env('SESSION_CONNECTION', 'default'),
    'table' => 'sessions',
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', 'laravel_erp_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE'),
    'http_only' => true,
    'same_site' => 'lax',
];
