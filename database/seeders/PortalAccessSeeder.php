<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PortalAccessSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['old' => 'admin.demo@kwdc.test', 'email' => 'admin.access@kwdc.test', 'name' => 'Portal Admin', 'role' => 'admin', 'phone' => '9800000000', 'is_admin' => true],
            ['old' => 'client.demo@kwdc.test', 'email' => 'client.access@kwdc.test', 'name' => 'Portal Client', 'role' => 'client', 'phone' => '9800000001', 'is_client' => true],
            ['old' => 'driver.demo@kwdc.test', 'email' => 'driver.access@kwdc.test', 'name' => 'Portal Driver', 'role' => 'driver', 'phone' => '9800000002', 'is_driver' => true],
            ['old' => 'property.demo@kwdc.test', 'email' => 'property.access@kwdc.test', 'name' => 'Portal Property Owner', 'role' => 'property_owner', 'phone' => '9800000003', 'is_property_owner' => true],
            ['old' => 'equipment.demo@kwdc.test', 'email' => 'equipment.access@kwdc.test', 'name' => 'Portal Equipment Owner', 'role' => 'equipment_owner', 'phone' => '9800000004', 'is_equipment_owner' => true],
            ['old' => 'security.demo@kwdc.test', 'email' => 'security.access@kwdc.test', 'name' => 'Portal Security Agency', 'role' => 'security_agency', 'phone' => '9800000005'],
        ];

        foreach ($accounts as $account) {
            $legacyUser = User::where('email', $account['old'])->first();
            $user = User::where('email', $account['email'])->first();

            if (!$user && $legacyUser) {
                $user = $legacyUser;
                $user->email = $account['email'];
            }

            $user ??= new User(['email' => $account['email']]);
            $user->forceFill([
                'name' => $account['name'],
                'email' => $account['email'],
                'password' => Hash::make('KwdcNew2026!'),
                'phone' => $account['phone'],
                'role' => $account['role'],
                'user_type' => $account['role'],
                'is_admin' => $account['is_admin'] ?? false,
                'is_client' => $account['is_client'] ?? false,
                'is_driver' => $account['is_driver'] ?? false,
                'is_property_owner' => $account['is_property_owner'] ?? false,
                'is_equipment_owner' => $account['is_equipment_owner'] ?? false,
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();
        }

        User::whereIn('email', [
            'admin.demo@kwdc.test',
            'client.demo@kwdc.test',
            'driver.demo@kwdc.test',
            'property.demo@kwdc.test',
            'equipment.demo@kwdc.test',
            'security.demo@kwdc.test',
        ])->update(['is_active' => false]);
    }
}