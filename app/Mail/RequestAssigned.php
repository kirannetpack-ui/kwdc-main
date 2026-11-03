<?php

namespace App\Mail;

use App\Models\WarehouseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public $request;

    public function __construct(WarehouseRequest $request)
    {
        $this->request = $request;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your warehouse request has been assigned - KWDC',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-assigned',
        );
    }
}