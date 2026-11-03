<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\DriverRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // 1. CREATE USERS
        // ============================================
        
        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin.access@kwdc.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000000',
                'role' => 'admin',
                'is_admin' => true,
                'is_driver' => false,
                'is_equipment_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
                'user_code' => 'ADM' . Str::random(6),
            ]
        );
        echo "✅ Admin: admin.access@kwdc.test (ID: {$admin->id})\n";

        // Clients
        $client1 = User::updateOrCreate(
            ['email' => 'client.primary@kwdc.test'],
            [
                'name' => 'Kiran Thapa',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000001',
                'role' => 'client',
                'is_admin' => false,
                'is_driver' => false,
                'is_equipment_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
                'user_code' => 'CLI' . Str::random(6),
            ]
        );
        echo "✅ Client: client.primary@kwdc.test (ID: {$client1->id})\n";

        $client2 = User::updateOrCreate(
            ['email' => 'client.secondary@kwdc.test'],
            [
                'name' => 'Test Client',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000002',
                'role' => 'client',
                'is_admin' => false,
                'is_driver' => false,
                'is_equipment_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
                'user_code' => 'CLI' . Str::random(6),
            ]
        );
        echo "✅ Client: client.secondary@kwdc.test (ID: {$client2->id})\n";

        // Drivers
        $driver1 = User::updateOrCreate(
            ['email' => 'driver.madan@kwdc.test'],
            [
                'name' => 'Madan Gurung',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000010',
                'role' => 'driver',
                'is_admin' => false,
                'is_driver' => true,
                'is_equipment_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
                'user_code' => 'DRV' . Str::random(6),
            ]
        );
        echo "✅ Driver: driver.madan@kwdc.test (ID: {$driver1->id})\n";

        $driver2 = User::updateOrCreate(
            ['email' => 'driver.sita@kwdc.test'],
            [
                'name' => 'Sita Rai',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000011',
                'role' => 'driver',
                'is_admin' => false,
                'is_driver' => true,
                'is_equipment_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
                'user_code' => 'DRV' . Str::random(6),
            ]
        );
        echo "✅ Driver: driver.sita@kwdc.test (ID: {$driver2->id})\n";

        $driver3 = User::updateOrCreate(
            ['email' => 'driver.hari@kwdc.test'],
            [
                'name' => 'Hari Shrestha',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000012',
                'role' => 'driver',
                'is_admin' => false,
                'is_driver' => true,
                'is_equipment_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
                'user_code' => 'DRV' . Str::random(6),
            ]
        );
        echo "✅ Driver: driver.hari@kwdc.test (ID: {$driver3->id})\n";

        // Property Owners
        $propertyOwner1 = User::updateOrCreate(
            ['email' => 'property.ram@kwdc.test'],
            [
                'name' => 'Ram Sharma',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000020',
                'role' => 'property_owner',
                'is_admin' => false,
                'is_driver' => false,
                'is_equipment_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
                'user_code' => 'PRO' . Str::random(6),
            ]
        );
        echo "✅ Property Owner: property.ram@kwdc.test (ID: {$propertyOwner1->id})\n";

        $propertyOwner2 = User::updateOrCreate(
            ['email' => 'property.gita@kwdc.test'],
            [
                'name' => 'Gita Adhikari',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000021',
                'role' => 'property_owner',
                'is_admin' => false,
                'is_driver' => false,
                'is_equipment_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
                'user_code' => 'PRO' . Str::random(6),
            ]
        );
        echo "✅ Property Owner: property.gita@kwdc.test (ID: {$propertyOwner2->id})\n";

        // Equipment Owners
        $equipmentOwner1 = User::updateOrCreate(
            ['email' => 'equipment.krishna@kwdc.test'],
            [
                'name' => 'Krishna Tamang',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000030',
                'role' => 'equipment_owner',
                'is_admin' => false,
                'is_driver' => false,
                'is_equipment_owner' => true,
                'is_active' => true,
                'email_verified_at' => now(),
                'user_code' => 'EQU' . Str::random(6),
            ]
        );
        echo "✅ Equipment Owner: equipment.krishna@kwdc.test (ID: {$equipmentOwner1->id})\n";

        $equipmentOwner2 = User::updateOrCreate(
            ['email' => 'equipment.maya@kwdc.test'],
            [
                'name' => 'Maya Thapa',
                'password' => Hash::make(env('KWDC_DEMO_PASSWORD', 'KwdcNew2026!')),
                'phone' => '9800000031',
                'role' => 'equipment_owner',
                'is_admin' => false,
                'is_driver' => false,
                'is_equipment_owner' => true,
                'is_active' => true,
                'email_verified_at' => now(),
                'user_code' => 'EQU' . Str::random(6),
            ]
        );
        echo "✅ Equipment Owner: equipment.maya@kwdc.test (ID: {$equipmentOwner2->id})\n";

                // ============================================
        // SEED MARGIN TIERS (Updated structure)
        // ============================================
        if (Schema::hasTable('margin_tiers')) {
            DB::table('margin_tiers')->insert([
                [
                    'name' => 'Short Distance (0-10km)',
                    'service_type' => 'dispatch',
                    'margin_type' => 'percentage',
                    'margin_value' => 10.00,
                    'min_distance' => 0,
                    'max_distance' => 10,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Medium Distance (11-50km)',
                    'service_type' => 'dispatch',
                    'margin_type' => 'percentage',
                    'margin_value' => 12.00,
                    'min_distance' => 10,
                    'max_distance' => 50,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Long Distance (51km+)',
                    'service_type' => 'dispatch',
                    'margin_type' => 'percentage',
                    'margin_value' => 15.00,
                    'min_distance' => 50,
                    'max_distance' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // ============================================
        // 2. CREATE VEHICLES (Using consolidated schema)
        // ============================================
        
        if (Schema::hasTable('vehicles')) {
            $vehicles = [
                [
                    'user_id' => $driver1->id,
                    'driver_id' => $driver1->id,
                    'driver_code' => 'DRV001',
                    'vehicle_number' => 'BA 1 KA 1234',
                    'vehicle_type' => 'truck',
                    'capacity' => 1.5,
                    'capacity_unit' => 'tons',
                    'manufacturer' => 'Tata',
                    'model' => 'Ace',
                    'year' => 2020,
                    'color' => 'White',
                    'fuel_type' => 'diesel',
                    'status' => 'active',
                    'is_verified' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $driver2->id,
                    'driver_id' => $driver2->id,
                    'driver_code' => 'DRV002',
                    'vehicle_number' => 'BA 1 KA 5678',
                    'vehicle_type' => 'van',
                    'capacity' => 800,
                    'capacity_unit' => 'kg',
                    'manufacturer' => 'Maruti Suzuki',
                    'model' => 'Eeco',
                    'year' => 2021,
                    'color' => 'Silver',
                    'fuel_type' => 'petrol',
                    'status' => 'active',
                    'is_verified' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $driver3->id,
                    'driver_id' => $driver3->id,
                    'driver_code' => 'DRV003',
                    'vehicle_number' => 'BA 1 KA 9012',
                    'vehicle_type' => 'truck',
                    'capacity' => 5.0,
                    'capacity_unit' => 'tons',
                    'manufacturer' => 'Hino',
                    'model' => '300',
                    'year' => 2019,
                    'color' => 'Blue',
                    'fuel_type' => 'diesel',
                    'status' => 'active',
                    'is_verified' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($vehicles as $vehicle) {
                try {
                    // Check if vehicle already exists
                    $exists = DB::table('vehicles')->where('vehicle_number', $vehicle['vehicle_number'])->exists();
                    if (!$exists) {
                        DB::table('vehicles')->insert($vehicle);
                        echo "✅ Vehicle created: {$vehicle['vehicle_number']}\n";
                    } else {
                        echo "⚠️ Vehicle {$vehicle['vehicle_number']} already exists.\n";
                    }
                } catch (\Exception $e) {
                    echo "❌ Error creating vehicle: " . $e->getMessage() . "\n";
                }
            }
        }

        // ============================================
        // 3. CREATE WAREHOUSES (Using consolidated schema)
        // ============================================
        
        if (Schema::hasTable('warehouses')) {
            $warehouses = [
                [
                    'user_id' => $propertyOwner1->id,
                    'name' => 'Kalimati Warehouse',
                    'location' => 'Kalimati, Kathmandu',
                    'address' => 'Kalimati, Kathmandu, Nepal',
                    'latitude' => 27.6823,
                    'longitude' => 85.3038,
                    'contact_number' => $propertyOwner1->phone,
                    'email' => 'kalimati.warehouse@kwdc.test',
                    'area_sqft' => 5000,
                    'area_sqm' => 464.5,
                    'price_per_sqft' => 50,
                    'description' => 'Large warehouse in Kalimati area with cold storage facilities.',
                    'cctv_count' => 10,
                    'guards_count' => 3,
                    'fire_extinguishers' => 5,
                    'nearby_police' => 'Kalimati Police Station - 500m',
                    'nearby_fire' => 'Kalimati Fire Station - 1km',
                    'nearby_hospital' => 'Kalimati Hospital - 2km',
                    'nearby_bank' => 'Nepal Bank - 200m',
                    'nearby_fuel' => 'Petrol Pump - 300m',
                    'nearby_market' => 'Kalimati Market - 800m',
                    'cold_storage' => true,
                    'temperature_min' => -5,
                    'temperature_max' => 5,
                    'humidity_control' => true,
                    'insurance_available' => true,
                    'loading_dock' => true,
                    'office_space' => true,
                    'staff_quarters' => false,
                    'parking_spaces' => 10,
                    'parking_type' => 'covered',
                    'available_from' => now()->addDays(7),
                    'minimum_rental_period' => 3,
                    'facilities' => json_encode(['24/7 Security', 'CCTV Surveillance', 'Loading Dock', 'Forklift', 'Racking System']),
                    'status' => 'approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $propertyOwner2->id,
                    'name' => 'Balkumari Warehouse',
                    'location' => 'Balkumari, Lalitpur',
                    'address' => 'Balkumari, Lalitpur, Nepal',
                    'latitude' => 27.6753,
                    'longitude' => 85.3230,
                    'contact_number' => $propertyOwner2->phone,
                    'email' => 'balkumari.warehouse@kwdc.test',
                    'area_sqft' => 8000,
                    'area_sqm' => 743.2,
                    'price_per_sqft' => 45,
                    'description' => 'Spacious warehouse in Balkumari with easy highway access.',
                    'cctv_count' => 15,
                    'guards_count' => 4,
                    'fire_extinguishers' => 8,
                    'nearby_police' => 'Balkumari Police Station - 300m',
                    'nearby_fire' => 'Balkumari Fire Station - 800m',
                    'nearby_hospital' => 'Balkumari Hospital - 1.5km',
                    'nearby_bank' => 'Nepal Investment Bank - 100m',
                    'nearby_fuel' => 'Petrol Pump - 200m',
                    'nearby_market' => 'Balkumari Market - 500m',
                    'cold_storage' => false,
                    'temperature_min' => null,
                    'temperature_max' => null,
                    'humidity_control' => false,
                    'insurance_available' => true,
                    'loading_dock' => true,
                    'office_space' => false,
                    'staff_quarters' => false,
                    'parking_spaces' => 15,
                    'parking_type' => 'open',
                    'available_from' => now()->addDays(14),
                    'minimum_rental_period' => 1,
                    'facilities' => json_encode(['24/7 Security', 'CCTV Surveillance', 'Loading Dock']),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($warehouses as $warehouse) {
                try {
                    $exists = DB::table('warehouses')->where('name', $warehouse['name'])->exists();
                    if (!$exists) {
                        DB::table('warehouses')->insert($warehouse);
                        echo "✅ Warehouse created: {$warehouse['name']}\n";
                    } else {
                        echo "⚠️ Warehouse {$warehouse['name']} already exists.\n";
                    }
                } catch (\Exception $e) {
                    echo "❌ Error creating warehouse: " . $e->getMessage() . "\n";
                }
            }
        }

        // ============================================
        // 4. CREATE DRIVER RATES (Using consolidated schema)
        // ============================================
        
        if (Schema::hasTable('driver_rates')) {
            $drivers = User::where('is_driver', true)->get();
            
            if ($drivers->count() > 0) {
                $rateTiers = [
                    [
                        'tier1' => 200,
                        'tier2' => 350,
                        'tier3' => 600,
                        'tier4' => 800,
                        'tier4_per_km' => 40,
                        'base_price' => 200,
                    ],
                    [
                        'tier1' => 250,
                        'tier2' => 400,
                        'tier3' => 650,
                        'tier4' => 900,
                        'tier4_per_km' => 45,
                        'base_price' => 250,
                    ],
                    [
                        'tier1' => 300,
                        'tier2' => 450,
                        'tier3' => 700,
                        'tier4' => 1000,
                        'tier4_per_km' => 50,
                        'base_price' => 300,
                    ],
                ];

                foreach ($drivers as $index => $driver) {
                    $tiers = $rateTiers[$index % count($rateTiers)];
                    try {
                        $exists = DB::table('driver_rates')
                            ->where('user_id', $driver->id)
                            ->whereDate('date', date('Y-m-d'))
                            ->exists();
                            
                        if (!$exists) {
                            DB::table('driver_rates')->insert([
                                'user_id' => $driver->id,
                                'driver_id' => $driver->id,
                                'date' => date('Y-m-d'),
                                'rate_tiers' => json_encode($tiers),
                                'base_price' => $tiers['base_price'],
                                'vehicle_type' => 'Standard',
                                'status' => 'active',
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            echo "✅ Driver Rate created for driver: {$driver->email}\n";
                        } else {
                            echo "⚠️ Driver Rate for {$driver->email} already exists.\n";
                        }
                    } catch (\Exception $e) {
                        echo "❌ Error creating driver rate: " . $e->getMessage() . "\n";
                    }
                }
            }
        }

        // ============================================
        // 5. CREATE MARGIN TIERS
        // ============================================
        
        if (Schema::hasTable('margin_tiers')) {
            $margins = [
                ['Short Distance (0-10km)', 0, 10, 10.00],
                ['Medium Distance (11-50km)', 10, 50, 12.00],
                ['Long Distance (51km+)', 50, null, 15.00],
            ];

            foreach ($margins as $margin) {
                try {
                    $exists = DB::table('margin_tiers')->where('name', $margin[0])->exists();
                    if (!$exists) {
                        DB::table('margin_tiers')->insert([
                            'name' => $margin[0],
                            'min_distance' => $margin[1],
                            'max_distance' => $margin[2],
                            'margin_percentage' => $margin[3],
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        echo "✅ Margin Tier created: {$margin[0]}\n";
                    }
                } catch (\Exception $e) {
                    echo "❌ Error creating margin tier: " . $e->getMessage() . "\n";
                }
            }
        }

        $this->call(NepaliDemoSeeder::class);
        $this->call(PortalAccessSeeder::class);
        $this->call(CalendarDemoSeeder::class);

        echo "\n🎉 Database seeding completed!\n";
        echo "============================================\n";
        echo "🔑 Test Credentials:\n";
        echo "   Admin: admin.access@kwdc.test / KwdcNew2026!\n";
        echo "   Client: client.primary@kwdc.test / KwdcNew2026!\n";
        echo "   Client: client.secondary@kwdc.test / KwdcNew2026!\n";
        echo "   Driver: driver.madan@kwdc.test / KwdcNew2026!\n";
        echo "   Driver: driver.sita@kwdc.test / KwdcNew2026!\n";
        echo "   Driver: driver.hari@kwdc.test / KwdcNew2026!\n";
        echo "   Property Owner: property.ram@kwdc.test / KwdcNew2026!\n";
        echo "   Property Owner: property.gita@kwdc.test / KwdcNew2026!\n";
        echo "   Equipment Owner: equipment.krishna@kwdc.test / KwdcNew2026!\n";
        echo "   Equipment Owner: equipment.maya@kwdc.test / KwdcNew2026!\n";
        echo "============================================\n";
    }
}
