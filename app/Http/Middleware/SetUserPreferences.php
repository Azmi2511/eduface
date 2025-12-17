<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class SetUserPreferences
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // 1. Set Bahasa / Locale
            $locale = $user->getPref('locale', 'id');
            App::setLocale($locale);

            // 2. Set Timezone (PHP Runtime)
            $timezone = $user->getPref('timezone', 'Asia/Jakarta');
            date_default_timezone_set($timezone);

            // 3. Share variabel visual ke View (agar bisa dipakai di Layout utama)
            // Ini mengurangi query berulang di Blade
            $theme = $user->getPref('theme', 'light');
            $accent = $user->getPref('accent_color', 'blue');
            $density = $user->getPref('layout_density', 'comfortable');

            View::share('user_theme', $theme);
            View::share('user_accent', $accent);
            View::share('user_density', $density);
        }

        return $next($request);
    }
}