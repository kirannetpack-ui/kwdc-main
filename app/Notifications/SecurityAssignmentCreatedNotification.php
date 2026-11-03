<?php

namespace App\Notifications;

use App\Models\SecurityAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityAssignmentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assignment;

    public function __construct(SecurityAssignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🛡️ New Security Assignment')
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new security assignment has been created.')
            ->line('Warehouse: ' . ($this->assignment->warehouse->name ?? 'N/A'))
            ->line('Start Date: ' . $this->assignment->start_date->format('F j, Y'))
            ->line('Shift: ' . $this->assignment->shift)
            ->action('View Assignment', url('/security/assignments/' . $this->assignment->id))
            ->line('Please review and confirm.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Security Assignment',
            'message' => 'Security assignment for Warehouse #' . $this->assignment->warehouse_id . ' created.',
            'url' => '/security/assignments/' . $this->assignment->id,
            'type' => 'security',
            'icon' => 'fa-shield-alt',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'New Security Assignment',
            'message' => 'Security assignment for Warehouse #' . $this->assignment->warehouse_id . ' created.',
            'url' => '/security/assignments/' . $this->assignment->id,
        ];
    }
}