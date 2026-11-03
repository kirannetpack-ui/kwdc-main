<?php

namespace App\Notifications;

use App\Models\DispatchOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DispatchAssignedNotification extends Notification implements ShouldQueue
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
            ->subject('🚛 Dispatch #' . $this->dispatch->id . ' Assigned')
            ->greeting('Hello ' . $notifiable->name)
            ->line('A dispatch has been assigned to you.')
            ->line('Pickup: ' . $this->dispatch->pickup_address)
            ->line('Delivery: ' . $this->dispatch->delivery_address)
            ->line('Base Price: NPR ' . number_format($this->dispatch->base_price))
            ->action('View Dispatch', url('/dispatch/' . $this->dispatch->id))
            ->line('Please confirm your availability.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Dispatch Assigned',
            'message' => 'Dispatch #' . $this->dispatch->id . ' has been assigned to you.',
            'url' => '/dispatch/' . $this->dispatch->id,
            'type' => 'dispatch',
            'icon' => 'fa-truck-loading',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'Dispatch Assigned',
            'message' => 'Dispatch #' . $this->dispatch->id . ' has been assigned to you.',
            'url' => '/dispatch/' . $this->dispatch->id,
        ];
    }
}