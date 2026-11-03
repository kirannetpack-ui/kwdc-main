<?php

namespace App\Notifications;

use App\Models\DispatchOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DispatchCreatedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('📦 New Dispatch Order #' . $this->dispatch->id)
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new dispatch order has been created.')
            ->line('Pickup: ' . $this->dispatch->pickup_address)
            ->line('Delivery: ' . $this->dispatch->delivery_address)
            ->line('Status: ' . ucfirst($this->dispatch->status))
            ->action('View Dispatch', url('/dispatch/' . $this->dispatch->id))
            ->line('Thank you for using KTM-WDC!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Dispatch Created',
            'message' => 'Dispatch #' . $this->dispatch->id . ' has been created.',
            'url' => '/dispatch/' . $this->dispatch->id,
            'type' => 'dispatch',
            'icon' => 'fa-truck',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'New Dispatch Created',
            'message' => 'Dispatch #' . $this->dispatch->id . ' has been created.',
            'url' => '/dispatch/' . $this->dispatch->id,
        ];
    }
}