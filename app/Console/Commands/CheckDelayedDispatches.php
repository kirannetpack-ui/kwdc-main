<?php

namespace App\Console\Commands;

use App\Models\DispatchOrder;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DelayedDispatchNotification;

class CheckDelayedDispatches extends Command
{
    protected $signature = 'dispatches:check-delayed';
    protected $description = 'Check for dispatches that have not updated location recently and notify admin.';

    public function handle()
    {
        $thresholdMinutes = 15; // configurable
        $cutoff = now()->subMinutes($thresholdMinutes);

        $delayed = DispatchOrder::where('status', 'in_progress')
            ->where(function ($query) use ($cutoff) {
                $query->where('last_location_update', '<', $cutoff)
                      ->orWhereNull('last_location_update');
            })
            ->get();

        if ($delayed->count() > 0) {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::send($admin, new DelayedDispatchNotification($delayed));
            }

            $this->info('Notified admins about ' . $delayed->count() . ' delayed dispatches.');
        } else {
            $this->info('No delayed dispatches found.');
        }
    }
}