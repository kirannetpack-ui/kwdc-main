<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // The schedules defined in routes/console.php will be loaded automatically
        // Or you can define them directly here:
        
        // $schedule->command('warehouse:billing')->daily();
        // $schedule->command('users:generate-codes')->daily();
    $schedule->command('dispatches:check-delayed')->everyFiveMinutes();

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        
        require base_path('routes/console.php');
    }
}