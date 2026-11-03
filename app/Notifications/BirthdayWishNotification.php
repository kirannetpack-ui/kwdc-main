<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BirthdayWishNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $age;

    public function __construct($age)
    {
        $this->age = $age;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $age = $this->age;
        $name = $notifiable->name;

        return (new MailMessage)
            ->subject('🎂 Happy Birthday, ' . $name . '! 🎉')
            ->greeting("Dear {$name},")
            ->line("On this special day, the entire KTM-WDC family wishes you a very happy **{$age}th birthday**!")
            ->line('Thank you for being such an integral part of our logistics and warehouse community. Your trust and partnership drive our success.')
            ->line('We are grateful for your continued support and look forward to many more years of collaboration.')
            ->line('May your year ahead be filled with joy, prosperity, and success!')
            ->action('🎁 Visit Your Dashboard', url('/dashboard'))
            ->line('Warm regards,')
            ->line('**The KTM-WDC Team**')
            ->salutation('🎈🎂🎁');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '🎂 Happy Birthday!',
            'message' => "Happy {$this->age}th Birthday, {$notifiable->name}!",
            'type' => 'birthday',
        ];
    }
}