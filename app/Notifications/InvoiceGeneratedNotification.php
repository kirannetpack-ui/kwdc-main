<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🧾 New Invoice #' . $this->invoice->invoice_number)
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new invoice has been generated for your order.')
            ->line('Invoice #: ' . $this->invoice->invoice_number)
            ->line('Amount: NPR ' . number_format($this->invoice->amount))
            ->line('Status: ' . ucfirst($this->invoice->status))
            ->action('View Invoice', url('/invoices/' . $this->invoice->id))
            ->line('Thank you for your business.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Invoice Generated',
            'message' => 'Invoice #' . $this->invoice->invoice_number . ' generated.',
            'url' => '/invoices/' . $this->invoice->id,
            'type' => 'invoice',
            'icon' => 'fa-file-invoice',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'New Invoice Generated',
            'message' => 'Invoice #' . $this->invoice->invoice_number . ' generated.',
            'url' => '/invoices/' . $this->invoice->id,
        ];
    }
}