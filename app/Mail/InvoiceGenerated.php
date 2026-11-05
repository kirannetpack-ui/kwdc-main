<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->invoice->order_type) {
                'dispatch' => "Delivery invoice {$this->invoice->invoice_number}",
                'equipment' => "Equipment invoice {$this->invoice->invoice_number}",
                default => "Warehouse invoice {$this->invoice->invoice_number}",
            },
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }
}