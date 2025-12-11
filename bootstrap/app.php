<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register route middleware aliases so route files can use short names
        // (this project uses the closure-based bootstrap, so `app/Http/Kernel.php`
        // may not be used to register aliases). Register aliases here to
        // ensure `session.auth` and `role` middleware are available.
        $middleware->alias([
            'session.auth' => \App\Http\Middleware\SessionAuth::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'jwt' => \App\Http\Middleware\JwtMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
