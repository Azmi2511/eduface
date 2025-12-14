<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Kode Verifikasi Pendaftaran Eduface')
                    ->html("
                        <div style='font-family: sans-serif; text-align: center; padding: 20px;'>
                            <h2>Verifikasi Email Anda</h2>
                            <p>Gunakan kode di bawah ini untuk menyelesaikan pendaftaran:</p>
                            <h1 style='color: #2F80ED; font-size: 32px; letter-spacing: 5px;'>{$this->otp}</h1>
                            <p>Kode ini berlaku selama 5 menit.</p>
                        </div>
                    ");
    }
}