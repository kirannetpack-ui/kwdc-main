<?php

namespace App\Notifications;

use App\Models\LoaderAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoaderAssignmentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assignment;

    public function __construct(LoaderAssignment $assignment)
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
            ->subject('📋 New Loader Assignment')
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new loader assignment has been created.')
            ->line('Dispatch ID: #' . $this->assignment->dispatch_id)
            ->line('Required Loaders: ' . $this->assignment->required_loaders)
            ->line('Date: ' . $this->assignment->assignment_date->format('F j, Y'))
            ->action('View Assignment', url('/loader/assignments/' . $this->assignment->id))
            ->line('Please coordinate the loaders.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Loader Assignment',
            'message' => 'Loader assignment for Dispatch #' . $this->assignment->dispatch_id . ' created.',
            'url' => '/loader/assignments/' . $this->assignment->id,
            'type' => 'loader',
            'icon' => 'fa-people-arrows',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'New Loader Assignment',
            'message' => 'Loader assignment for Dispatch #' . $this->assignment->dispatch_id . ' created.',
            'url' => '/loader/assignments/' . $this->assignment->id,
        ];
    }
}