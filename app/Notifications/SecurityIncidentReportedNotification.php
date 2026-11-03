<?php

namespace App\Notifications;

use App\Models\SecurityIncident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityIncidentReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $incident;

    public function __construct(SecurityIncident $incident)
    {
        $this->incident = $incident;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🚨 Security Incident Reported')
            ->greeting('Hello ' . $notifiable->name)
            ->line('A security incident has been reported.')
            ->line('Category: ' . $this->incident->category)
            ->line('Severity: ' . ucfirst($this->incident->severity))
            ->line('Description: ' . $this->incident->description)
            ->action('View Incident', url('/security/incidents/' . $this->incident->id))
            ->line('Please investigate and take action.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => '🚨 Security Incident',
            'message' => 'Incident reported: ' . $this->incident->category,
            'url' => '/security/incidents/' . $this->incident->id,
            'type' => 'security',
            'icon' => 'fa-exclamation-triangle',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => '🚨 Security Incident',
            'message' => 'Incident reported: ' . $this->incident->category,
            'url' => '/security/incidents/' . $this->incident->id,
        ];
    }
}