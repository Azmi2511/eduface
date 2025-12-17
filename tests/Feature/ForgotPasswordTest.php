<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_code_and_verify_and_reset()
    {
        /** @var User $user */
        $user = User::factory()->create(['email' => 'testuser@example.com']);

        // fake mail and send code
        \Illuminate\Support\Facades\Mail::fake();
        $res = $this->postJson(route('password.sendCode'), ['email' => $user->email]);
        $res->assertStatus(200)->assertJson(['status' => 'success']);

        // mail assertion: subject should indicate reset password
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\OtpMail::class, function ($mail) {
            // build to ensure subject is set
            $mail->build();
            return $mail->subject === 'Kode Verifikasi Reset Password Eduface';
        });

        // get cached OTP
        $otp = Cache::get('otp_' . $user->email);
        $this->assertNotNull($otp);

        // verify
        $ver = $this->postJson(route('password.verifyCode'), ['email' => $user->email, 'code' => $otp]);
        $ver->assertStatus(200)->assertJson(['status' => 'success']);

        // reset
        $reset = $this->postJson(route('password.reset'), ['email' => $user->email, 'password' => 'newpass123']);
        $reset->assertStatus(200)->assertJson(['status' => 'success']);

        $user->refresh();
        $this->assertTrue(password_verify('newpass123', $user->password));
    }
}
