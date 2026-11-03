<?php

namespace App\Mail;

use App\Models\WarehouseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractExpiryReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $request;
    public $daysLeft;

    public function __construct(WarehouseRequest $request, $daysLeft)
    {
        $this->request = $request;
        $this->daysLeft = $daysLeft;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Contract expires in {$this->daysLeft} days",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-expiry',
        );
    }
}