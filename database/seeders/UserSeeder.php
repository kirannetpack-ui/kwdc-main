<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
            ['email' => 'admin.access@kwdc.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000000',
                'is_admin' => true,
                'role' => 'admin',
                'user_type' => 'admin',
            ]
        );

        // Client Users
        User::firstOrCreate(
            ['email' => 'client.access@kwdc.test'],
            [
                'name' => 'Client User',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000001',
                'is_client' => true,
                'role' => 'client',
                'user_type' => 'client',
            ]
        );

        User::firstOrCreate(
            ['email' => 'client.test@kwdc.test'],
            [
                'name' => 'Test User',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000002',
                'is_client' => true,
                'role' => 'client',
                'user_type' => 'client',
            ]
        );

        // Driver Users
        User::firstOrCreate(
            ['email' => 'driver.access@kwdc.test'],
            [
                'name' => 'Driver User',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000003',
                'is_driver' => true,
                'role' => 'driver',
                'user_type' => 'driver',
            ]
        );

        User::firstOrCreate(
            ['email' => 'driver.madan@kwdc.test'],
            [
                'name' => 'Madan Driver',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000004',
                'is_driver' => true,
                'role' => 'driver',
                'user_type' => 'driver',
            ]
        );

        // Equipment Owner
        User::firstOrCreate(
            ['email' => 'equipment.owner@kwdc.test'],
            [
                'name' => 'Equipment Owner',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000005',
                'is_equipment_owner' => true,
                'role' => 'equipment_owner',
                'user_type' => 'equipment_owner',
            ]
        );

        // Property Owner
        User::firstOrCreate(
            ['email' => 'property.owner@kwdc.test'],
            [
                'name' => 'Warehouse Owner',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000006',
                'is_property_owner' => true,
                'role' => 'property_owner',
                'user_type' => 'property_owner',
            ]
        );

        $this->command->info('Users seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin.access@kwdc.test / KwdcNew2026!');
        $this->command->info('Client: client.access@kwdc.test / KwdcNew2026!');
        $this->command->info('Driver: driver.access@kwdc.test / KwdcNew2026!');
        $this->command->info('Equipment: equipment.owner@kwdc.test / development demo password');
        $this->command->info('Property: property.owner@kwdc.test / development demo password');
    }
}