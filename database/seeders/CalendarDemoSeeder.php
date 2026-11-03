<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserReminder;
use Illuminate\Database\Seeder;

class CalendarDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local', 'testing')) {
            throw new \RuntimeException('Demo data is only available locally.');
        }
        $titles = ['Operations review', 'Dispatch planning', 'Warehouse inspection', 'Invoice review', 'Partner check-in', 'Stock count', 'Delivery follow-up', 'Weekly review'];
        foreach (User::where('email', 'like', '%@kwdc.test')->get() as $user) {
            foreach ($titles as $index => $title) {
                UserReminder::firstOrCreate(
                    ['user_id' => $user->id, 'title' => $title.' (Demo)'],
                    [
                        'starts_at' => now()->startOfMonth()->addDays(2 + $index * 3)->setTime(9 + $index % 6, 0),
                        'notes' => 'Demo activity for '.$user->name.'.',
                        'status' => 'scheduled',
                    ]
                );
            }
        }
    }
}
