<?php
namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    /** @test */
    public function it_sends_otp_to_email()
    {
        Mail::fake();

        $this->post('/register/send-otp', [
            'name' => 'Budi',
            'email' => 'user@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'parent',
            'gender' => 'L'
        ])->assertOk();

        Mail::assertSent(OtpMail::class);
    }

    /** @test */
    public function it_verifies_otp_and_creates_user()
    {
        Cache::put('otp_user@example.com', '123456', 300);

        $this->post('/register/verify-create', [
            'email' => 'user@example.com',
            'otp_code' => '123456',
            'name' => 'Budi',
            'password' => 'secret123',
            'role' => 'parent',
            'gender' => 'L'
        ])->assertStatus(200);

        $this->assertDatabaseHas('users', ['email' => 'user@example.com']);
    }
}