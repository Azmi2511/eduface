<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    |
    | Configure JWT authentication settings
    |
    */

    'secret' => env('JWT_SECRET', env('APP_KEY')),
    'algorithm' => env('JWT_ALGORITHM', 'HS256'),
    'ttl' => env('JWT_TTL', 86400), // 24 hours in seconds
    'refresh_ttl' => env('JWT_REFRESH_TTL', 604800), // 7 days in seconds
];
