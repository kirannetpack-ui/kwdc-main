<?php

namespace App\Notifications;

use App\Models\PickupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PickupCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $pickup;

    public function __construct(PickupRequest $pickup)
    {
        $this->pickup = $pickup;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('📦 New Pickup Request #' . $this->pickup->id)
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new pickup request has been created.')
            ->line('Pickup Address: ' . $this->pickup->pickup_address)
            ->line('Status: ' . ucfirst($this->pickup->status))
            ->action('View Pickup', url('/pickup/' . $this->pickup->id))
            ->line('Thank you!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Pickup Request',
            'message' => 'Pickup #' . $this->pickup->id . ' has been created.',
            'url' => '/pickup/' . $this->pickup->id,
            'type' => 'pickup',
            'icon' => 'fa-box',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'New Pickup Request',
            'message' => 'Pickup #' . $this->pickup->id . ' has been created.',
            'url' => '/pickup/' . $this->pickup->id,
        ];
    }
}