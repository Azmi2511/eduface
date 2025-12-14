<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SessionAuth
{
    public function handle($request, Closure $next)
    {
        if (!session('user_id')) {
            return redirect('login');
        }

        if (!Auth::check()) {
            Auth::loginUsingId(session('user_id')); 
        }

        return $next($request);
    }
}
