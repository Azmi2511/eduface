<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    public function test_show_forgot_password_page()
    {
        $response = $this->get('/password/forgot');

        $response->assertStatus(200);
    }

    public function test_send_code()
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/password/send-code', [
            'email' => $user->email
        ]);

        $response->assertStatus(200);
    }

    public function test_verify_code()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        Cache::put('otp_' . $user->email, '123456', 300);

        $response = $this->post('/password/verify-code', [
            'email' => $user->email,
            'code' => '123456'
        ]);

        $response->assertStatus(200);
    }
}