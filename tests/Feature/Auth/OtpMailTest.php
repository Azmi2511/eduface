<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;
use App\Mail\OtpMail;

class OtpMailTest extends TestCase
{
    public function test_build_email()
    {
        $mail = new OtpMail('123456');

        $this->assertNotNull($mail->build());
    }
}