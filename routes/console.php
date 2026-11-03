<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// KTM-WDC Scheduled Tasks
Schedule::command('warehouse:billing')->daily();           // Run billing daily
Schedule::command('shipments:update-status')->everyTenMinutes();  // Update shipment status
Schedule::command('invoices:send-reminders')->dailyAt('09:00');    // Send invoice reminders
Schedule::command('drivers:verify-documents')->weekly();           // Verify driver docs weekly
Schedule::command('users:generate-codes')->daily();                // Generate missing user codes
Schedule::command('reminders:send-due-emails')->everyFiveMinutes(); // Send user reminder emails

// Or if you only want to run once for existing users
// Schedule::command('users:generate-codes')->once();
