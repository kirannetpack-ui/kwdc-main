<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $activationCode
    ) {
    }

    public function build()
    {
        return $this->subject('Your KTM-WDC verification code')
            ->view('emails.activation-code');
    }
}
