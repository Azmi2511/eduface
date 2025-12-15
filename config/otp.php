<?php

return [
    // seconds
    'resend_ttl' => env('OTP_RESEND_TTL', 60),
    'max_attempts' => env('OTP_MAX_ATTEMPTS', 3),
    'lock_ttl' => env('OTP_LOCK_TTL', 900), // 15 minutes
    'otp_ttl' => env('OTP_TTL', 300), // 5 minutes default for otp code
];
