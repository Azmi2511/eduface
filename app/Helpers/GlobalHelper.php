<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

if (!function_exists('user_date')) {
    /**
     * Format tanggal sesuai settingan user
     */
    function user_date($date) {
        if (!$date) return '-';
        
        // Ambil format user, default d/m/Y
        $format = Auth::check() ? Auth::user()->getPref('date_format', 'd/m/Y') : 'd/m/Y';
        
        return Carbon::parse($date)->translatedFormat($format);
    }
}