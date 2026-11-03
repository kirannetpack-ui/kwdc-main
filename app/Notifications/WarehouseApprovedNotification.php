<?php

namespace App\Notifications;

use App\Models\Warehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WarehouseApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $warehouse;
    protected $action;

    public function __construct(Warehouse $warehouse, $action = 'approved')
    {
        $this->warehouse = $warehouse;
        $this->action = $action;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        $status = $this->action === 'approved' ? 'Approved' : 'Rejected';
        return (new MailMessage)
            ->subject('🏭 Warehouse ' . $status)
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your warehouse "' . $this->warehouse->name . '" has been ' . strtolower($status) . '.')
            ->action('View Warehouse', url('/warehouses/' . $this->warehouse->id))
            ->line('Thank you for using KTM-WDC!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Warehouse ' . ucfirst($this->action),
            'message' => 'Your warehouse "' . $this->warehouse->name . '" has been ' . $this->action . '.',
            'url' => '/warehouses/' . $this->warehouse->id,
            'type' => 'warehouse',
            'icon' => 'fa-warehouse',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'Warehouse ' . ucfirst($this->action),
            'message' => 'Your warehouse "' . $this->warehouse->name . '" has been ' . $this->action . '.',
            'url' => '/warehouses/' . $this->warehouse->id,
        ];
    }
}