<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * (This is a trimmed example. Merge into your Laravel app's Kernel.php.)
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustProxies::class,
        // ...
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            // \App\Http\Middleware\EncryptCookies::class,
            // \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            // ...
            \App\Http\Middleware\SetUserPreferences::class,
        ],

        'api' => [
            'throttle:api',
            // \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Route middleware that may be assigned to groups or used individually.
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        // add session-based middleware from scaffold
        'session.auth' => \App\Http\Middleware\SessionAuth::class,
        'role' => \App\Http\Middleware\CheckRole::class,
        'jwt' => \App\Http\Middleware\JwtMiddleware::class,
    ];
}
