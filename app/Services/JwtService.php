<?php

namespace App\Services;

use Exception;

class JwtService
{
    private $secret;
    private $algo;
    private $ttl;

    public function __construct()
    {
        $this->secret = config('jwt.secret');
        $this->algo = config('jwt.algorithm', 'HS256');
        $this->ttl = config('jwt.ttl', 86400);
    }

    /**
     * Generate JWT Token
     */
    public function generate($payload)
    {
        $header = $this->base64UrlEncode(json_encode([
            'typ' => 'JWT',
            'alg' => $this->algo
        ]));

        $payload['iat'] = $payload['iat'] ?? time();
        $payload['exp'] = $payload['exp'] ?? (time() + $this->ttl);

        $payload = $this->base64UrlEncode(json_encode($payload));

        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $this->secret, true)
        );

        return "$header.$payload.$signature";
    }

    /**
     * Verify and decode JWT Token
     */
    public function verify($token)
    {
        try {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                throw new Exception('Invalid token format');
            }

            list($header, $payload, $signature) = $parts;

            // Verify signature
            $expectedSignature = $this->base64UrlEncode(
                hash_hmac('sha256', "$header.$payload", $this->secret, true)
            );

            if (!hash_equals($signature, $expectedSignature)) {
                throw new Exception('Invalid signature');
            }

            // Decode payload
            $decodedPayload = json_decode($this->base64UrlDecode($payload), true);

            // Check expiration
            if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
                throw new Exception('Token expired');
            }

            return $decodedPayload;
        } catch (Exception $e) {
            throw new Exception('Token verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Base64 URL Encode
     */
    private function base64UrlEncode($data)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($data)
        );
    }

    /**
     * Base64 URL Decode
     */
    private function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
