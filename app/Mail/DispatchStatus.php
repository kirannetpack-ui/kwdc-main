<?php

namespace App\Mail;

use App\Models\DispatchOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DispatchStatus extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(DispatchOrder $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Dispatch order #{$this->order->id} status update - KWDC",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dispatch-status',
        );
    }
}