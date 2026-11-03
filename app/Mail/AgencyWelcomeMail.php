<?php

namespace App\Mail;

use App\Models\SecurityAgency;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgencyWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $agency;

    public function __construct(SecurityAgency $agency)
    {
        $this->agency = $agency;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to KTM-WDC Security Partner Program',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.agency-welcome',
        );
    }
}