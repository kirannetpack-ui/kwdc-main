<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DelayedDispatchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $dispatches;

    public function __construct($dispatches)
    {
        $this->dispatches = $dispatches;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // also can broadcast
    }

    public function toMail($notifiable)
    {
        $count = $this->dispatches->count();
        $ids = $this->dispatches->pluck('id')->join(', ');

        return (new MailMessage)
            ->subject('⚠️ Delayed Dispatches Alert')
            ->line("There are {$count} dispatches that haven't updated location for more than 15 minutes.")
            ->line("Dispatch IDs: {$ids}")
            ->action('View Tracking Dashboard', url('/tracking'));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "{$this->dispatches->count()} dispatches are delayed.",
            'dispatch_ids' => $this->dispatches->pluck('id')->toArray(),
        ];
    }
}