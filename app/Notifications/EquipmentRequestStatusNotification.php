<?php

namespace App\Notifications;

use App\Models\EquipmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $request;

    public function __construct(EquipmentRequest $request)
    {
        $this->request = $request;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🔧 Equipment Request Status Updated')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your equipment request #' . $this->request->id . ' status is now: **' . ucfirst($this->request->status) . '**')
            ->action('View Request', url('/equipment-requests/' . $this->request->id))
            ->line('Thank you!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Equipment Request Updated',
            'message' => 'Equipment request #' . $this->request->id . ' status: ' . ucfirst($this->request->status),
            'url' => '/equipment-requests/' . $this->request->id,
            'type' => 'equipment',
            'icon' => 'fa-tools',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'Equipment Request Updated',
            'message' => 'Equipment request #' . $this->request->id . ' status: ' . ucfirst($this->request->status),
            'url' => '/equipment-requests/' . $this->request->id,
        ];
    }
}