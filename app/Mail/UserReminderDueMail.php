<?php

namespace App\Mail;

use App\Models\UserReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserReminderDueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public UserReminder $reminder)
    {
    }

    public function build()
    {
        return $this->subject('Reminder: ' . $this->reminder->title)
            ->view('emails.user-reminder-due');
    }
}
