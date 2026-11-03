<?php

namespace App\Mail;

use App\Models\Warehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WarehouseRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $warehouse;

    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on your warehouse submission - KWDC',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.warehouse-rejected',
        );
    }
}