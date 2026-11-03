<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Welcome to KTM-WDC',
                'message' => 'Welcome to the Warehouse & Distribution Connect platform!',
                'type' => 'success',
                'is_read' => false,
            ]);
        }
    }
}