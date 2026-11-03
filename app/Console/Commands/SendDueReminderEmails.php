<?php

namespace App\Console\Commands;

use App\Mail\UserReminderDueMail;
use App\Models\Notification;
use App\Models\UserReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDueReminderEmails extends Command
{
    protected $signature = 'reminders:send-due-emails';

    protected $description = 'Send email notifications for due user reminders';

    public function handle(): int
    {
        UserReminder::with('user')
            ->dueForEmail()
            ->chunkById(100, function ($reminders) {
                foreach ($reminders as $reminder) {
                    if (!$reminder->user || !$reminder->user->email) {
                        continue;
                    }

                    Mail::to($reminder->user->email, $reminder->user->name)
                        ->send(new UserReminderDueMail($reminder));

                    $reminder->forceFill(['emailed_at' => now()])->save();

                    Notification::createNotification(
                        $reminder->user_id,
                        'reminder_due',
                        'Reminder: ' . $reminder->title,
                        'Your reminder is due on ' . $reminder->starts_at->format('M d, Y h:i A') . '.',
                        $reminder->id,
                        UserReminder::class
                    );
                }
            });

        return self::SUCCESS;
    }
}
