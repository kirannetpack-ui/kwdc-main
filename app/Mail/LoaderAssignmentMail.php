<?php

namespace App\Mail;

use App\Models\LoaderAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoaderAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $assignment;

    public function __construct(LoaderAssignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 New Loader Assignment',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loader-assignment',
        );
    }
}