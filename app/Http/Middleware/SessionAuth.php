<?php

namespace App\Http\Middleware;

use Closure;

class SessionAuth
{
    public function handle($request, Closure $next)
    {
        if (!session('user_id')) {
            return redirect()->route('login.show');
        }
        return $next($request);
    }
}
