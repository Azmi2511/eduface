<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage (Web): Route::middleware(['role:admin,teacher'])
     * Usage (API): Route::middleware(['jwt', 'role:admin,teacher'])
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if request is for API (has JWT payload)
        $jwtPayload = $request->attributes->get('jwt_payload');
        
        if ($jwtPayload) {
            // Check role from JWT token
            $userRole = $jwtPayload['role'] ?? null;
        } else {
            // Check role from session
            $userRole = session('role');
        }

        if (!$userRole) {
            return $this->unauthorized($request);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (!in_array($userRole, $roles)) {
            return $this->forbidden($request);
        }

        return $next($request);
    }

    /**
     * Return unauthorized response based on request type
     */
    private function unauthorized(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return redirect()->route('login.show');
    }

    /**
     * Return forbidden response based on request type
     */
    private function forbidden(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden - Insufficient permissions'], 403);
        }

        return redirect()->route('dashboard')->with('error', 'Insufficient permissions');
    }
}
