<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:admin,teacher')
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $userRole = session('role');

        if (!$userRole) {
            return redirect()->route('login.show');
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (!in_array($userRole, $roles)) {
            // unauthorized - simple redirect to dashboard or 403
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
