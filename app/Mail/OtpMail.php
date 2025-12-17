<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $purpose;

    /**
     * @param int|string $otp
     * @param string $purpose  'registration' (default) or 'reset'
     */
    public function __construct($otp, $purpose = 'registration')
    {
        $this->otp = $otp;
        $this->purpose = $purpose;
    }

    public function build()
    {
        $subject = $this->purpose === 'reset' ? 'Kode Verifikasi Reset Password Eduface' : 'Kode Verifikasi Pendaftaran Eduface';
        $title = $this->purpose === 'reset' ? 'Reset Password Anda' : 'Verifikasi Email Anda';
        $desc = $this->purpose === 'reset' ? 'Gunakan kode di bawah ini untuk mereset password Anda:' : 'Gunakan kode di bawah ini untuk menyelesaikan pendaftaran:';

        return $this->subject($subject)
                    ->html("
                        <div style='font-family: sans-serif; text-align: center; padding: 20px;'>
                            <h2>{$title}</h2>
                            <p>{$desc}</p>
                            <h1 style='color: #2F80ED; font-size: 32px; letter-spacing: 5px;'>{$this->otp}</h1>
                            <p>Kode ini berlaku selama 5 menit.</p>
                        </div>
                    ");
    }
}