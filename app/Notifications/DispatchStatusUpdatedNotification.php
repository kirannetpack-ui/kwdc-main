<?php

namespace App\Notifications;

use App\Models\DispatchOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DispatchStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $dispatch;

    public function __construct(DispatchOrder $dispatch)
    {
        $this->dispatch = $dispatch;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        $status = ucfirst($this->dispatch->status);
        return (new MailMessage)
            ->subject('📋 Dispatch #' . $this->dispatch->id . ' Status Updated')
            ->greeting('Hello ' . $notifiable->name)
            ->line("Dispatch #{$this->dispatch->id} status has been updated to: **{$status}**.")
            ->line('Pickup: ' . $this->dispatch->pickup_address)
            ->line('Delivery: ' . $this->dispatch->delivery_address)
            ->action('View Dispatch', url('/dispatch/' . $this->dispatch->id))
            ->line('Thank you for using KTM-WDC!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Dispatch Status Updated',
            'message' => 'Dispatch #' . $this->dispatch->id . ' status: ' . ucfirst($this->dispatch->status),
            'url' => '/dispatch/' . $this->dispatch->id,
            'type' => 'dispatch',
            'icon' => 'fa-exchange-alt',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'Dispatch Status Updated',
            'message' => 'Dispatch #' . $this->dispatch->id . ' status: ' . ucfirst($this->dispatch->status),
            'url' => '/dispatch/' . $this->dispatch->id,
        ];
    }
}