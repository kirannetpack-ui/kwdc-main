<?php

namespace App\Notifications;

use App\Models\PickupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PickupStatusUpdatedNotification extends Notification implements ShouldQueue
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
            ->subject('📋 Pickup #' . $this->pickup->id . ' Status Updated')
            ->greeting('Hello ' . $notifiable->name)
            ->line("Pickup #{$this->pickup->id} status: **" . ucfirst($this->pickup->status) . '**')
            ->action('View Pickup', url('/pickup/' . $this->pickup->id))
            ->line('Thank you!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Pickup Status Updated',
            'message' => 'Pickup #' . $this->pickup->id . ' status: ' . ucfirst($this->pickup->status),
            'url' => '/pickup/' . $this->pickup->id,
            'type' => 'pickup',
            'icon' => 'fa-exchange-alt',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'Pickup Status Updated',
            'message' => 'Pickup #' . $this->pickup->id . ' status: ' . ucfirst($this->pickup->status),
            'url' => '/pickup/' . $this->pickup->id,
        ];
    }
}