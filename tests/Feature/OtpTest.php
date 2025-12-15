<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class OtpTest extends TestCase
{
    public function test_cannot_resend_otp_within_resend_ttl()
    {
        Cache::flush();

        $payload = [
            'name' => 'Test User',
            'email' => 'test-otp@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'teacher',
            'gender' => 'L',
            'id_number' => 'T-001'
        ];

        $res1 = $this->post('/register/send-otp', $payload);
        $res1->assertStatus(200)->assertJson(['status' => 'success']);

        $res2 = $this->post('/register/send-otp', $payload);
        $res2->assertStatus(429)->assertJson(['status' => 'error']);
    }

    public function test_attempts_limit_and_lock()
    {
        Cache::flush();

        $email = 'limit-otp@example.com';
        // send first
        $payload = [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'teacher',
            'gender' => 'L',
            'id_number' => 'T-002'
        ];

        $this->post('/register/send-otp', $payload)->assertStatus(200);

        // Try wrong code three times
        for ($i = 0; $i < config('otp.max_attempts', 3); $i++) {
            $res = $this->post('/register/verify-create', array_merge($payload, ['otp_code' => '000000']));
            if ($i < config('otp.max_attempts', 3) -1) {
                $res->assertStatus(400);
            } else {
                $res->assertStatus(423);
            }
        }

        // Now it's locked
        $locked = $this->post('/register/verify-create', array_merge($payload, ['otp_code' => '000000']));
        $locked->assertStatus(423);
    }
}
