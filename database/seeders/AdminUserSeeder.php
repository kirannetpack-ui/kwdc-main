<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user only if it doesn't exist
        if (!User::where('email', 'admin.access@kwdc.test')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin.access@kwdc.test',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'role' => 'admin',
                'user_code' => 'ADM-2026-0001',
                'is_client' => false,
                'is_driver' => false,
                'is_equipment_owner' => false,
                'is_property_owner' => false,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $this->command->info('✅ Admin user created successfully!');
        } else {
            $this->command->info('ℹ️ Admin user already exists.');
        }
    }
}