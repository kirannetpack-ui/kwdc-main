<?php

namespace App\Mail;

use App\Models\WarehouseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExtendedStorageReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $request;
    public $daysOverdue;

    public function __construct(WarehouseRequest $request, $daysOverdue)
    {
        $this->request = $request;
        $this->daysOverdue = $daysOverdue;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Extended storage reminder – {$this->daysOverdue} days overdue",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.extended-storage',
        );
    }
}