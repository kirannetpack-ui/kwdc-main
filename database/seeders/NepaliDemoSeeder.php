<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NepaliDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('KwdcNew2026!');

        $admin = $this->user('admin.access@kwdc.test', 'Demo Admin', 'admin', $password, [
            'is_admin' => true,
            'phone' => '9800000000',
            'address' => 'Putalisadak, Kathmandu',
            'user_code' => 'ADM-DEMO',
        ]);

        $client = $this->user('client.access@kwdc.test', 'Sita Enterprises Demo', 'client', $password, [
            'is_client' => true,
            'phone' => '9801112233',
            'address' => 'New Road, Kathmandu',
            'user_code' => 'CLI-DEMO',
        ]);

        $clientTwo = $this->user('client.pokhara@kwdc.test', 'Pokhara Fresh Mart Demo', 'client', $password, [
            'is_client' => true,
            'phone' => '9801112244',
            'address' => 'Chipledhunga, Pokhara',
            'user_code' => 'CLI-PKR',
        ]);

        $driver = $this->user('driver.access@kwdc.test', 'Ram Bahadur Driver', 'driver', $password, [
            'is_driver' => true,
            'phone' => '9802223344',
            'address' => 'Koteshwor, Kathmandu',
            'user_code' => 'DRV-DEMO',
        ]);

        $driverTwo = $this->user('driver.laxman@kwdc.test', 'Laxman Gurung', 'driver', $password, [
            'is_driver' => true,
            'phone' => '9802223355',
            'address' => 'Bharatpur, Chitwan',
            'user_code' => 'DRV-LAXMAN',
        ]);

        $propertyOwner = $this->user('property.access@kwdc.test', 'Maya Warehouse Owner', 'property_owner', $password, [
            'is_property_owner' => true,
            'phone' => '9803334455',
            'address' => 'Satdobato, Lalitpur',
            'user_code' => 'PRO-DEMO',
        ]);

        $equipmentOwner = $this->user('equipment.access@kwdc.test', 'Krishna Equipment Owner', 'equipment_owner', $password, [
            'is_equipment_owner' => true,
            'phone' => '9804445566',
            'address' => 'Biratnagar, Morang',
            'user_code' => 'EQP-DEMO',
        ]);

        $securityUser = $this->user('security.access@kwdc.test', 'Nepal Suraksha Agency', 'security_agency', $password, [
            'phone' => '9805556677',
            'address' => 'Baluwatar, Kathmandu',
            'user_code' => 'SEC-DEMO',
        ]);

        $warehouseOne = $this->warehouse($propertyOwner->id, 'Kalimati Cold Store Demo', 'Kalimati, Kathmandu', [
            'address' => 'Kalimati Tarkari Bazaar gate pachadi, Kathmandu',
            'email' => 'kalimati.cold@kwdc.test',
            'area_sqft' => 6200,
            'price_per_sqft' => 52,
            'description' => 'Cold storage for tarkari, dairy, and festival stock with 24/7 guard service.',
            'cold_storage' => true,
            'temperature_min' => -4,
            'temperature_max' => 6,
            'cctv_count' => 18,
            'guards_count' => 4,
            'status' => 'approved',
            'city' => 'Kathmandu',
        ]);

        $warehouseTwo = $this->warehouse($propertyOwner->id, 'Birgunj Transit Godown Demo', 'Birgunj, Parsa', [
            'address' => 'Dry Port road, Birgunj',
            'email' => 'birgunj.transit@kwdc.test',
            'area_sqft' => 14000,
            'price_per_sqft' => 38,
            'description' => 'Large border transit warehouse for FMCG, cement, and import cargo.',
            'loading_dock' => true,
            'parking_spaces' => 22,
            'cctv_count' => 24,
            'guards_count' => 6,
            'status' => 'approved',
            'city' => 'Birgunj',
        ]);

        $this->vehicle($driver->id, 'BA 5 KHA 2244', 'mini truck', 'Tata', 'Ace Gold', 1.5);
        $this->vehicle($driverTwo->id, 'NA 8 KHA 7711', 'truck', 'Eicher', 'Pro 2059', 5.0);

        $this->driverRate($driver->id, 250, 'Mini Truck');
        $this->driverRate($driverTwo->id, 420, 'Heavy Truck');

        $this->dispatch($client->id, $driver->id, 'TRK-KTM-001', 'Kalimati, Kathmandu', 'Bhaktapur Durbar Square', 17.5, 3200, 'assigned');
        $this->dispatch($client->id, $driver->id, 'TRK-PKR-002', 'Nayabazar, Pokhara', 'Lakeside, Pokhara', 8.2, 1800, 'picked_up');
        $this->dispatch($client->id, $driver->id, 'TRK-LTP-003', 'Lagankhel, Lalitpur', 'Boudha, Kathmandu', 13.1, 2500, 'delivered');
        $this->dispatch($client->id, null, 'TRK-BGW-004', 'Birgunj Dry Port', 'Hetauda Industrial Area', 92.0, 14500, 'pending');
        $this->dispatch($client->id, null, 'TRK-ITR-005', 'Itahari Chowk', 'Dharan Bazar', 21.4, 4100, 'pending');
        $this->dispatch($client->id, $driverTwo->id, 'TRK-CTW-006', 'Narayanghat Bus Park, Chitwan', 'Bharatpur Hospital Road', 6.8, 1900, 'assigned');
        $this->dispatch($client->id, null, 'TRK-BRT-007', 'Biratnagar Industrial Corridor', 'Damak Main Road', 72.5, 11800, 'pending');
        $this->dispatch($clientTwo->id, $driverTwo->id, 'TRK-PKR-008', 'Prithvi Chowk, Pokhara', 'Baglung Bus Park', 68.0, 10200, 'on_the_way');
        $this->dispatch($clientTwo->id, null, 'TRK-LMB-009', 'Bhairahawa ICD', 'Lumbini Sanskritik Gate', 24.5, 4700, 'pending');
        $this->dispatch($clientTwo->id, $driver->id, 'TRK-KTM-010', 'Balaju Industrial Area', 'Durbarmarg, Kathmandu', 11.3, 2600, 'delivered');

        $this->pickup($client->id, $driver->id, 'PUP-KTM-001', 'Asan, Kathmandu', 9.4, 2100, 'assigned', 'New Road, Kathmandu');
        $this->pickup($client->id, $driver->id, 'PUP-LTP-002', 'Jawalakhel, Lalitpur', 6.7, 1600, 'completed', 'Kalimati Cold Store Demo');
        $this->pickup($client->id, null, 'PUP-BKT-003', 'Suryabinayak, Bhaktapur', 12.0, 2300, 'pending', 'Lokanthali, Bhaktapur');
        $this->pickup($client->id, null, 'PUP-KTM-004', 'Maharajgunj, Kathmandu', 5.8, 1400, 'pending', 'Baluwatar, Kathmandu');
        $this->pickup($client->id, $driverTwo->id, 'PUP-BRJ-005', 'Adarsh Nagar, Birgunj', 18.9, 3900, 'picked_up', 'Birgunj Transit Godown Demo');
        $this->pickup($clientTwo->id, $driverTwo->id, 'PUP-PKR-006', 'Mahendrapool, Pokhara', 7.1, 1550, 'assigned', 'Lakeside, Pokhara');
        $this->pickup($clientTwo->id, null, 'PUP-DHN-007', 'Dharan Bazar', 10.6, 2200, 'pending', 'Itahari Chowk');

        $stockId = $this->stock($client->id, $warehouseOne->id, 'Ilam Tea Cartons', 'ILAM-TEA-2083', 'SKU-TEA-001', 48);
        $this->box($stockId, $client->id, 'TEA-BOX-001');
        $this->box($stockId, $client->id, 'TEA-BOX-002');
        $this->stock($client->id, $warehouseTwo->id, 'Birgunj FMCG Mixed Cartons', 'BRJ-FMCG-2083', 'SKU-FMCG-014', 130);
        $this->stock($clientTwo->id, $warehouseOne->id, 'Pokhara Herbal Soap Packs', 'PKR-SOAP-2083', 'SKU-SOAP-025', 72);
        $this->stock($clientTwo->id, $warehouseTwo->id, 'Mustang Apple Juice Cases', 'MST-JUICE-2083', 'SKU-JUICE-033', 96);

        $this->equipment($equipmentOwner->id, 'JCB Backhoe Loader Demo', 'backhoe_loader', 'Kathmandu Ring Road', 18000);
        $this->equipment($equipmentOwner->id, 'Forklift 3 Ton Demo', 'forklift', 'Birgunj Dry Port', 8500);

        $agencyId = $this->securityAgency($securityUser->id);
        $guardId = $this->securityPersonnel($agencyId, 'Bikash Thapa', 'SEC-GRD-001');
        $this->securityGood($agencyId, 'Handheld Metal Detector', 'screening');
        $this->securityAssignment($warehouseOne->id, $agencyId, $guardId);

        $extraUsers = $this->seedHeavyDemoLayer(
            $password,
            $admin,
            [$client, $clientTwo],
            [$driver, $driverTwo],
            $propertyOwner,
            $equipmentOwner,
            $securityUser,
            [$warehouseOne, $warehouseTwo],
            $agencyId
        );

        foreach (array_merge([$admin, $client, $clientTwo, $driver, $driverTwo, $propertyOwner, $equipmentOwner, $securityUser], $extraUsers) as $user) {
            $this->reminder($user->id, 'Follow up demo calendar task', 'Check today dashboard and pending actions.');
            $this->reminder($user->id, 'Monthly billing review', 'Review demo invoices, pickup requests, and dispatch payments.');
            $this->reminder($user->id, 'Morning operations standup', 'Review assigned jobs, stock alerts, invoices, and messages before 10 AM.');
            $this->notification($user->id, 'Demo data ready', 'Nepali demo records have been loaded for this role.', 'demo');
            $this->notification($user->id, 'Reminder calendar synced', 'Your calendar now has demo reminders and follow-up tasks.', 'reminder');
            $this->notification($user->id, 'Unread demo activity', 'There are fresh role-specific notifications waiting in this account.', 'activity');
        }
    }

    private function user(string $email, string $name, string $role, string $password, array $extra): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            array_merge([
                'name' => $name,
                'password' => $password,
                'role' => $role,
                'is_active' => true,
                'email_verified_at' => now(),
            ], $extra)
        );
    }

    private function warehouse(int $userId, string $name, string $location, array $extra): object
    {
        DB::table('warehouses')->updateOrInsert(
            ['name' => $name],
            array_merge([
                'user_id' => $userId,
                'location' => $location,
                'contact_number' => '9807778899',
                'country' => 'Nepal',
                'facilities' => json_encode(['CCTV', 'Loading dock', 'Guard post', 'Parking']),
                'available_from' => now()->addDays(3)->toDateString(),
                'minimum_rental_period' => 1,
                'insurance_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], $extra)
        );

        return DB::table('warehouses')->where('name', $name)->first();
    }

    private function vehicle(int $driverId, string $number, string $type, string $manufacturer, string $model, float $capacity): void
    {
        DB::table('vehicles')->updateOrInsert(
            ['vehicle_number' => $number],
            [
                'user_id' => $driverId,
                'driver_id' => $driverId,
                'driver_code' => 'DRV-' . $driverId,
                'vehicle_type' => $type,
                'capacity' => $capacity,
                'capacity_unit' => 'tons',
                'manufacturer' => $manufacturer,
                'model' => $model,
                'year' => 2022,
                'color' => 'White',
                'fuel_type' => 'diesel',
                'status' => 'active',
                'is_verified' => true,
                'verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function driverRate(int $driverId, int $basePrice, string $vehicleType): void
    {
        DB::table('driver_rates')->updateOrInsert(
            ['driver_id' => $driverId, 'date' => now()->toDateString()],
            [
                'user_id' => $driverId,
                'vehicle_type' => $vehicleType,
                'base_price' => $basePrice,
                'rate_tiers' => json_encode(['0_5km' => $basePrice, '6_15km' => $basePrice * 1.8, '16_plus_per_km' => 65]),
                'status' => 'active',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function dispatch(?int $clientId, ?int $driverId, string $tracking, string $pickup, string $delivery, float $distance, int $price, string $status): int
    {
        DB::table('dispatch_orders')->updateOrInsert(
            ['tracking_id' => $tracking],
            [
                'client_id' => $clientId,
                'driver_id' => $driverId,
                'pickup_address' => $pickup,
                'delivery_address' => $delivery,
                'total_distance' => $distance,
                'base_price' => $price,
                'driver_earning' => $driverId ? $price * 0.75 : null,
                'admin_margin' => $price * 0.25,
                'status' => $status,
                'payment_status' => $status === 'delivered' ? 'paid' : 'pending',
                'assigned_at' => $driverId ? now()->subDays(2) : null,
                'picked_up_at' => in_array($status, ['picked_up', 'delivered'], true) ? now()->subDay() : null,
                'delivered_at' => $status === 'delivered' ? now()->subHours(5) : null,
                'created_at' => now()->subDays(rand(1, 9)),
                'updated_at' => now(),
            ]
        );

        $dispatchId = (int) DB::table('dispatch_orders')->where('tracking_id', $tracking)->value('id');
        $this->deliveryStop($dispatchId, 1, $delivery, $status);

        return $dispatchId;
    }

    private function deliveryStop(int $dispatchId, int $stopNumber, string $address, string $status): void
    {
        DB::table('delivery_stops')->updateOrInsert(
            ['dispatch_order_id' => $dispatchId, 'stop_number' => $stopNumber],
            [
                'address' => $address,
                'recipient_name' => 'Demo Receiver ' . $stopNumber,
                'recipient_phone' => '98100000' . str_pad((string) $stopNumber, 2, '0', STR_PAD_LEFT),
                'distance_from_previous' => 4 + $stopNumber,
                'distance_price' => 700 + ($stopNumber * 250),
                'notes' => 'Demo delivery stop near ' . $address,
                'status' => $status === 'delivered' ? 'delivered' : 'pending',
                'delivered_at' => $status === 'delivered' ? now()->subHours(4) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function pickup(int $clientId, ?int $driverId, string $tracking, string $pickupAddress, float $distance, int $price, string $status, string $destination): int
    {
        if (!Schema::hasTable('pickup_requests')) {
            return 0;
        }

        DB::table('pickup_requests')->updateOrInsert(
            ['tracking_id' => $tracking],
            [
                'client_id' => $clientId,
                'driver_id' => $driverId,
                'pickup_address' => $pickupAddress,
                'destination_address' => $destination,
                'items_description' => 'Demo cartons and local market goods',
                'weight' => 12,
                'notes' => 'Pickup route from ' . $pickupAddress . ' to ' . $destination,
                'total_distance' => $distance,
                'total_price' => $price,
                'driver_earning' => $driverId ? $price * 0.75 : null,
                'admin_margin' => $price * 0.25,
                'status' => $status,
                'payment_status' => $status === 'completed' ? 'paid' : 'pending',
                'payment_due_date' => now()->addDays(7)->toDateString(),
                'assigned_at' => $driverId ? now()->subDay() : null,
                'picked_up_at' => $status === 'completed' ? now()->subHours(9) : null,
                'delivered_at' => $status === 'completed' ? now()->subHours(3) : null,
                'completed_at' => $status === 'completed' ? now()->subHours(3) : null,
                'created_at' => now()->subDays(rand(1, 8)),
                'updated_at' => now(),
            ]
        );

        $pickupId = (int) DB::table('pickup_requests')->where('tracking_id', $tracking)->value('id');
        $this->pickupStop($pickupId, 1, $pickupAddress, $status);

        return $pickupId;
    }

    private function pickupStop(int $pickupId, int $stopNumber, string $address, string $status): void
    {
        DB::table('pickup_stops')->updateOrInsert(
            ['pickup_request_id' => $pickupId, 'stop_number' => $stopNumber],
            [
                'address' => $address,
                'contact_name' => 'Demo Supplier ' . $stopNumber,
                'contact_phone' => '98200000' . str_pad((string) $stopNumber, 2, '0', STR_PAD_LEFT),
                'items_description' => 'Demo market cartons, fragile label included',
                'estimated_weight' => 12 + $stopNumber,
                'distance_price' => 500 + ($stopNumber * 200),
                'status' => $status === 'completed' ? 'completed' : 'pending',
                'picked_up_at' => $status === 'completed' ? now()->subHours(8) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function stock(int $clientId, int $warehouseId, string $product, string $batch, string $sku, int $boxes): int
    {
        DB::table('stocks')->updateOrInsert(
            ['batch_id' => $batch],
            [
                'user_id' => $clientId,
                'client_id' => $clientId,
                'warehouse_id' => $warehouseId,
                'product_name' => $product,
                'description' => 'Demo stock from Nepal supply route.',
                'unit' => 'cartons',
                'number_of_boxes' => $boxes,
                'quantity_per_box' => 24,
                'total_quantity' => $boxes * 24,
                'remaining_quantity' => $boxes * 24,
                'sku' => $sku,
                'client_code' => 'CLI-DEMO',
                'client_name' => 'Sita Enterprises Demo',
                'received_date' => now()->subDays(4)->toDateString(),
                'status' => 'in_stock',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $stockId = (int) DB::table('stocks')->where('batch_id', $batch)->value('id');

        for ($box = 1; $box <= 3; $box++) {
            $this->box($stockId, $clientId, strtoupper($batch) . '-BOX-' . str_pad((string) $box, 2, '0', STR_PAD_LEFT));
        }

        return $stockId;
    }

    private function box(int $stockId, int $clientId, string $boxNumber): void
    {
        DB::table('boxes')->updateOrInsert(
            ['box_number' => $boxNumber],
            [
                'stock_id' => $stockId,
                'client_id' => $clientId,
                'qr_code' => 'QR-' . $boxNumber,
                'qr_code_data' => json_encode(['box' => $boxNumber, 'origin' => 'Ilam']),
                'status' => 'active',
                'description' => 'Demo tracked carton for warehouse scanning.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function equipment(int $ownerId, string $name, string $type, string $location, int $dailyRate): void
    {
        DB::table('equipment')->updateOrInsert(
            ['name' => $name],
            [
                'user_id' => $ownerId,
                'owner_id' => $ownerId,
                'type' => $type,
                'model' => 'Demo Model',
                'year' => 2021,
                'description' => 'Nepali construction equipment demo listing.',
                'daily_rate' => $dailyRate,
                'weekly_rate' => $dailyRate * 6,
                'monthly_rate' => $dailyRate * 24,
                'location' => $location,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function securityAgency(int $userId): int
    {
        DB::table('security_agencies')->updateOrInsert(
            ['user_id' => $userId],
            [
                'agency_name' => 'Kathmandu Suraksha Sewa Demo',
                'registration_number' => 'SEC-DEMO-2083',
                'license_number' => 'LIC-DEMO-7788',
                'address' => 'Baluwatar, Kathmandu',
                'phone' => '9805556677',
                'emergency_phone' => '9805556688',
                'email' => 'security.access@kwdc.test',
                'services_offered' => 'Warehouse guard, night patrol, CCTV monitoring',
                'year_established' => '2075',
                'status' => 'approved',
                'is_verified' => true,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return (int) DB::table('security_agencies')->where('user_id', $userId)->value('id');
    }

    private function securityPersonnel(int $agencyId, string $name, string $employeeId): int
    {
        $table = Schema::hasTable('security_personnels') ? 'security_personnels' : 'security_personnel';

        DB::table($table)->updateOrInsert(
            ['employee_id' => $employeeId],
            [
                'agency_id' => $agencyId,
                'name' => $name,
                'phone' => '9806667788',
                'email' => Str::slug($name) . '@kwdc.test',
                'citizenship_number' => 'DEMO-CIT-7788',
                'address' => 'Kirtipur, Kathmandu',
                'position' => 'Senior Guard',
                'qualifications' => 'Basic fire safety, CCTV monitoring',
                'status' => 'active',
                'has_vehicle' => true,
                'vehicle_type' => 'motorbike',
                'shift_availability' => json_encode(['day', 'night']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return (int) DB::table($table)->where('employee_id', $employeeId)->value('id');
    }

    private function securityGood(int $agencyId, string $item, string $category): void
    {
        DB::table('security_goods')->updateOrInsert(
            ['agency_id' => $agencyId, 'item_name' => $item],
            [
                'category' => $category,
                'model' => 'Demo-2083',
                'specifications' => 'Rechargeable, warehouse gate use',
                'quantity_available' => 12,
                'unit_price' => 4500,
                'is_rental' => true,
                'rental_rate_per_day' => 350,
                'description' => 'Demo security equipment for warehouse assignment.',
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function securityAssignment(int $warehouseId, int $agencyId, int $personnelId): void
    {
        DB::table('security_assignments')->updateOrInsert(
            ['warehouse_id' => $warehouseId, 'agency_id' => $agencyId, 'personnel_id' => $personnelId],
            [
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'shift' => 'night',
                'shift_start' => '18:00',
                'shift_end' => '06:00',
                'status' => 'active',
                'notes' => 'Night guard demo assignment for Kalimati warehouse.',
                'total_cost' => 42000,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function seedHeavyDemoLayer(string $password, User $admin, array $clients, array $drivers, User $propertyOwner, User $equipmentOwner, User $securityUser, array $warehouses, int $agencyId): array
    {
        $moreClients = [
            $this->user('client.lalitpur@kwdc.test', 'Lalitpur Hardware Suppliers', 'client', $password, [
                'is_client' => true,
                'phone' => '9801112255',
                'address' => 'Lagankhel, Lalitpur',
                'user_code' => 'CLI-LTP',
            ]),
            $this->user('client.biratnagar@kwdc.test', 'Biratnagar Pharma House', 'client', $password, [
                'is_client' => true,
                'phone' => '9801112266',
                'address' => 'Traffic Chowk, Biratnagar',
                'user_code' => 'CLI-BRT',
            ]),
            $this->user('client.chitwan@kwdc.test', 'Chitwan Agro Traders', 'client', $password, [
                'is_client' => true,
                'phone' => '9801112277',
                'address' => 'Narayanghat, Chitwan',
                'user_code' => 'CLI-CTW',
            ]),
        ];

        $moreDrivers = [
            $this->user('driver.sita@kwdc.test', 'Sita Rai', 'driver', $password, [
                'is_driver' => true,
                'phone' => '9802223366',
                'address' => 'Dharan, Sunsari',
                'user_code' => 'DRV-SITA',
            ]),
            $this->user('driver.hari@kwdc.test', 'Hari Shrestha', 'driver', $password, [
                'is_driver' => true,
                'phone' => '9802223377',
                'address' => 'Baneshwor, Kathmandu',
                'user_code' => 'DRV-HARI',
            ]),
            $this->user('driver.tsering@kwdc.test', 'Tsering Lama', 'driver', $password, [
                'is_driver' => true,
                'phone' => '9802223388',
                'address' => 'Boudha, Kathmandu',
                'user_code' => 'DRV-TSERING',
            ]),
        ];

        $propertyOwnerTwo = $this->user('property.gita.demo@kwdc.test', 'Gita Property Demo', 'property_owner', $password, [
            'is_property_owner' => true,
            'phone' => '9803334466',
            'address' => 'Bharatpur, Chitwan',
            'user_code' => 'PRO-GITA',
        ]);

        $equipmentOwnerTwo = $this->user('equipment.maya.demo@kwdc.test', 'Maya Heavy Equipment Demo', 'equipment_owner', $password, [
            'is_equipment_owner' => true,
            'phone' => '9804445577',
            'address' => 'Hetauda, Makwanpur',
            'user_code' => 'EQP-MAYA',
        ]);

        $securityUserTwo = $this->user('security.birgunj@kwdc.test', 'Birgunj Suraksha Agency', 'security_agency', $password, [
            'phone' => '9805556699',
            'address' => 'Adarsh Nagar, Birgunj',
            'user_code' => 'SEC-BRJ',
        ]);

        $allClients = array_merge($clients, $moreClients);
        $allDrivers = array_merge($drivers, $moreDrivers);

        $moreWarehouses = [
            $this->warehouse($propertyOwnerTwo->id, 'Chitwan Agro Hub Demo', 'Bharatpur, Chitwan', [
                'address' => 'Bharatpur bypass road, Chitwan',
                'email' => 'chitwan.agro@kwdc.test',
                'area_sqft' => 9800,
                'price_per_sqft' => 34,
                'description' => 'Agro warehouse for rice, feed, seasonal fruits, and festival goods.',
                'loading_dock' => true,
                'parking_spaces' => 14,
                'cctv_count' => 12,
                'guards_count' => 3,
                'status' => 'approved',
                'city' => 'Bharatpur',
            ]),
            $this->warehouse($propertyOwnerTwo->id, 'Dharan Retail Storage Demo', 'Dharan, Sunsari', [
                'address' => 'Dharan Bazar line, Sunsari',
                'email' => 'dharan.storage@kwdc.test',
                'area_sqft' => 4200,
                'price_per_sqft' => 29,
                'description' => 'Medium warehouse for retail cartons and small distributors.',
                'parking_spaces' => 7,
                'cctv_count' => 10,
                'guards_count' => 2,
                'status' => 'pending',
                'city' => 'Dharan',
            ]),
        ];

        $allWarehouses = array_merge($warehouses, $moreWarehouses);

        foreach ($allDrivers as $index => $driver) {
            $this->vehicle($driver->id, 'DEMO ' . ($index + 10) . ' KHA ' . (3300 + $index), $index % 2 === 0 ? 'truck' : 'mini truck', $index % 2 === 0 ? 'Eicher' : 'Tata', 'Demo Fleet ' . ($index + 1), $index % 2 === 0 ? 6.0 : 2.0);
            $this->driverRate($driver->id, 260 + ($index * 35), $index % 2 === 0 ? 'Truck' : 'Mini Truck');
        }

        $routes = [
            ['TRK-HEAVY-011', 'Teku, Kathmandu', 'Patan Industrial Area', 12.2, 2900, 'assigned'],
            ['TRK-HEAVY-012', 'Boudha, Kathmandu', 'Nagarkot Road, Bhaktapur', 24.8, 4800, 'on_the_way'],
            ['TRK-HEAVY-013', 'Bharatpur, Chitwan', 'Hetauda Industrial Area', 78.4, 13200, 'pending'],
            ['TRK-HEAVY-014', 'Biratnagar Airport Road', 'Itahari Chowk', 23.5, 4500, 'picked_up'],
            ['TRK-HEAVY-015', 'Dharan Bazar', 'Birtamode, Jhapa', 84.3, 13900, 'delivered'],
            ['TRK-HEAVY-016', 'Pokhara Industrial Area', 'Damauli Bazar', 51.7, 8300, 'assigned'],
            ['TRK-HEAVY-017', 'Balaju, Kathmandu', 'Tokha Road', 7.8, 1700, 'delivered'],
            ['TRK-HEAVY-018', 'Bhairahawa ICD', 'Butwal Golpark', 25.1, 4700, 'pending'],
        ];

        foreach ($routes as $index => [$tracking, $pickup, $delivery, $distance, $price, $status]) {
            $client = $allClients[$index % count($allClients)];
            $driver = in_array($status, ['pending'], true) ? null : $allDrivers[$index % count($allDrivers)];
            $dispatchId = $this->dispatch($client->id, $driver?->id, $tracking, $pickup, $delivery, $distance, $price, $status);
            $this->notifyDispatchFlow($admin, $client, $driver, $dispatchId, $tracking, $status, $pickup, $delivery);
            if ($driver && in_array($status, ['delivered', 'picked_up', 'on_the_way'], true)) {
                $this->partnerEarning($driver->id, 'dispatch', $dispatchId, (float) ($price * 0.75), $status === 'delivered' ? 'paid' : 'pending');
            }
        }

        $pickupRoutes = [
            ['PUP-HEAVY-008', 'Kirtipur, Kathmandu', 8.8, 1800, 'assigned', 'Kalimati Cold Store Demo'],
            ['PUP-HEAVY-009', 'Sanepa, Lalitpur', 5.4, 1300, 'completed', 'Lagankhel, Lalitpur'],
            ['PUP-HEAVY-010', 'Kausaltar, Bhaktapur', 10.2, 2100, 'pending', 'Lokanthali, Bhaktapur'],
            ['PUP-HEAVY-011', 'Biratnagar Main Road', 18.7, 3800, 'picked_up', 'Itahari Warehouse Line'],
            ['PUP-HEAVY-012', 'Bharatpur, Chitwan', 14.0, 2700, 'assigned', 'Chitwan Agro Hub Demo'],
            ['PUP-HEAVY-013', 'Lakeside, Pokhara', 9.2, 1850, 'completed', 'Prithvi Chowk, Pokhara'],
        ];

        foreach ($pickupRoutes as $index => [$tracking, $pickupAddress, $distance, $price, $status, $destination]) {
            $client = $allClients[$index % count($allClients)];
            $driver = in_array($status, ['pending'], true) ? null : $allDrivers[$index % count($allDrivers)];
            $pickupId = $this->pickup($client->id, $driver?->id, $tracking, $pickupAddress, $distance, $price, $status, $destination);
            $this->notifyPickupFlow($admin, $client, $driver, $pickupId, $tracking, $status, $pickupAddress, $destination);
            if ($driver && $status === 'completed') {
                $this->partnerEarning($driver->id, 'pickup', $pickupId, (float) ($price * 0.75), 'paid');
            }
        }

        foreach ($allClients as $index => $client) {
            $warehouse = $allWarehouses[$index % count($allWarehouses)];
            $requestId = $this->warehouseRequest($client->id, $warehouse->id, $index % 2 === 0 ? 'approved' : 'pending', 800 + ($index * 250), 'Demo storage for ' . $client->name);
            $this->invoice($client->id, $warehouse->id, $requestId, 'INV-DEMO-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT), 12000 + ($index * 3500), $index % 2 === 0 ? 'paid' : 'unpaid');
            $this->stock($client->id, $warehouse->id, $client->name . ' Mixed Demo Stock', 'BATCH-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT), 'SKU-DEMO-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT), 30 + ($index * 12));
            $this->notification($client->id, 'Warehouse request updated', $warehouse->name . ' request is now ' . ($index % 2 === 0 ? 'approved' : 'waiting for admin review') . '.', 'warehouse_request', $requestId, 'warehouse_request');
            $this->notification($propertyOwner->id, 'Client requested warehouse space', $client->name . ' requested space at ' . $warehouse->name . '.', 'warehouse_request', $requestId, 'warehouse_request');
        }

        $equipmentIds = [
            $this->equipmentRecord($equipmentOwner->id, 'Excavator Chain Demo', 'excavator', 'Hetauda Industrial Area', 24000),
            $this->equipmentRecord($equipmentOwnerTwo->id, 'Road Roller Demo', 'road_roller', 'Bharatpur, Chitwan', 21000),
            $this->equipmentRecord($equipmentOwnerTwo->id, 'Mobile Crane Demo', 'crane', 'Birgunj Dry Port', 32000),
        ];

        foreach ($equipmentIds as $index => $equipmentId) {
            $client = $allClients[$index % count($allClients)];
            $owner = $index === 0 ? $equipmentOwner : $equipmentOwnerTwo;
            $requestId = $this->equipmentRequest($client->id, $equipmentId, $owner->id, ['excavator', 'road_roller', 'crane'][$index], ['pending', 'approved', 'completed'][$index]);
            $jobId = $this->equipmentJob($equipmentId, $owner->id, $client->id, ['pending', 'accepted', 'completed'][$index], 18000 + ($index * 7000));
            $this->notification($client->id, 'Equipment request status', 'Your equipment request has demo status update: ' . ['pending', 'approved', 'completed'][$index] . '.', 'equipment_request', $requestId, 'equipment_request');
            $this->notification($owner->id, 'Equipment job activity', 'New equipment job activity is available for review.', 'equipment_job', $jobId, 'equipment_job');
            if ($index === 2) {
                $this->partnerEarning($owner->id, 'equipment', $jobId, 25000, 'paid');
            }
        }

        $agencyTwoId = $this->securityAgencyFor($securityUserTwo->id, 'Birgunj Border Security Demo', 'SEC-BRJ-2083', 'LIC-BRJ-9922', 'Adarsh Nagar, Birgunj');
        $guardTwoId = $this->securityPersonnel($agencyTwoId, 'Nabin Chaudhary', 'SEC-GRD-002');
        $this->securityGood($agencyTwoId, 'CCTV Portable Kit', 'monitoring');
        $assignmentTwoId = $this->securityAssignmentRecord($allWarehouses[1]->id, $agencyTwoId, $guardTwoId, 'day');
        $this->securityIncident($allWarehouses[1]->id, $securityUserTwo->id, $assignmentTwoId, 'gate_check', 'Truck entered without updated visitor slip.', 'medium', 'resolved');
        $this->securityIncident($allWarehouses[0]->id, $securityUser->id, null, 'stock_audit', 'Night audit found two cartons needing recount.', 'low', 'investigating');
        $this->notification($securityUserTwo->id, 'Security incident logged', 'A demo gate-check incident was recorded and resolved.', 'security_incident');
        $this->notification($admin->id, 'Security monitoring update', 'Security agency demo incidents and assignments are ready for review.', 'security_incident');

        foreach (array_merge([$admin, $propertyOwner, $equipmentOwner, $equipmentOwnerTwo, $securityUser, $securityUserTwo], $allClients, $allDrivers) as $index => $user) {
            $this->notification($user->id, 'Dashboard has new demo activity', 'Open your dashboard to review fresh Nepali logistics records.', 'dashboard');
            $this->notification($user->id, 'Payment and invoice reminder', 'Demo billing activity has been added for review and follow-up.', 'invoice');
            $this->notification($user->id, 'Operations alert ' . ($index + 1), 'New route, stock, partner, or security activity is waiting for this account.', 'operations');
        }

        return array_merge($moreClients, $moreDrivers, [$propertyOwnerTwo, $equipmentOwnerTwo, $securityUserTwo]);
    }

    private function equipmentRecord(int $ownerId, string $name, string $type, string $location, int $dailyRate): int
    {
        $this->equipment($ownerId, $name, $type, $location, $dailyRate);

        return (int) DB::table('equipment')->where('name', $name)->value('id');
    }

    private function warehouseRequest(int $clientId, int $warehouseId, string $status, int $spaceRequired, string $purpose): int
    {
        if (!Schema::hasTable('warehouse_requests')) {
            return 0;
        }

        $key = ['client_id' => $clientId, 'warehouse_id' => $warehouseId, 'purpose' => $purpose];
        $values = [
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'space_required' => $spaceRequired,
            'status' => $status,
            'approved_at' => $status === 'approved' ? now() : null,
            'approved_by' => $status === 'approved' ? User::where('role', 'admin')->value('id') : null,
            'admin_notes' => $status === 'approved' ? 'Approved for demo storage flow.' : 'Pending demo admin review.',
            'preferred_warehouse' => (string) $warehouseId,
            'agreed_price' => $status === 'approved' ? $spaceRequired * 42 : null,
            'pricing_details' => json_encode(['rate_per_sqft' => 42, 'demo' => true]),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('warehouse_requests')->updateOrInsert($key, $this->onlyColumns('warehouse_requests', $values));

        return (int) DB::table('warehouse_requests')->where($key)->value('id');
    }

    private function invoice(int $clientId, int $warehouseId, int $warehouseRequestId, string $invoiceNumber, int $amount, string $paymentStatus): void
    {
        if (!Schema::hasTable('invoices') || $warehouseRequestId === 0) {
            return;
        }

        $subtotal = $amount;
        $tax = round($subtotal * 0.13, 2);
        DB::table('invoices')->updateOrInsert(
            ['invoice_number' => $invoiceNumber],
            $this->onlyColumns('invoices', [
                'user_id' => $clientId,
                'client_id' => $clientId,
                'warehouse_id' => $warehouseId,
                'warehouse_request_id' => $warehouseRequestId,
                'order_type' => 'warehouse_request',
                'order_id' => $warehouseRequestId,
                'amount' => $subtotal,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax_rate' => 13,
                'tax_amount' => $tax,
                'grand_total' => $subtotal + $tax,
                'billing_type' => 'regular',
                'billing_address' => 'Demo billing address, Nepal',
                'items' => json_encode([['name' => 'Warehouse storage demo charge', 'amount' => $subtotal]]),
                'status' => $paymentStatus === 'paid' ? 'paid' : 'pending',
                'payment_status' => $paymentStatus,
                'due_date' => now()->addDays(10)->toDateString(),
                'payment_due_date' => now()->addDays(10)->toDateString(),
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
                'payment_method' => $paymentStatus === 'paid' ? 'cash_demo' : null,
                'notes' => 'Demo invoice for seeded Nepali warehouse activity.',
                'created_at' => now(),
                'updated_at' => now(),
            ])
        );

        $this->notification($clientId, 'Invoice ' . $invoiceNumber . ' is ' . $paymentStatus, 'Demo invoice amount NPR ' . number_format($amount) . ' has been added.', 'invoice');
    }

    private function equipmentRequest(int $clientId, int $equipmentId, int $ownerId, string $type, string $status): int
    {
        if (!Schema::hasTable('equipment_requests')) {
            return 0;
        }

        $key = ['client_id' => $clientId, 'equipment_type' => $type, 'start_date' => now()->addDays(2)->toDateString()];
        DB::table('equipment_requests')->updateOrInsert(
            $key,
            $this->onlyColumns('equipment_requests', [
                'equipment_id' => $equipmentId,
                'owner_id' => $ownerId,
                'title' => ucwords(str_replace('_', ' ', $type)) . ' demo request',
                'description' => 'Seeded demo equipment request for Nepali construction work.',
                'equipment_name' => ucwords(str_replace('_', ' ', $type)),
                'quantity' => 1,
                'end_date' => now()->addDays(6)->toDateString(),
                'duration_days' => 4,
                'location' => 'Kathmandu Valley demo site',
                'budget' => 65000,
                'quoted_price' => 62000,
                'proposed_price' => 62000,
                'agreed_price' => $status === 'completed' ? 62000 : null,
                'status' => $status,
                'special_requirements' => 'Operator, fuel estimate, and safety checklist required.',
                'preferred_brands' => json_encode(['JCB', 'Tata Hitachi']),
                'budget_range' => '50000-75000',
                'notes' => 'Heavy demo request.',
                'approved_at' => in_array($status, ['approved', 'completed'], true) ? now() : null,
                'completed_at' => $status === 'completed' ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ])
        );

        return (int) DB::table('equipment_requests')->where($key)->value('id');
    }

    private function equipmentJob(int $equipmentId, int $ownerId, int $clientId, string $status, int $price): int
    {
        if (!Schema::hasTable('equipment_jobs')) {
            return 0;
        }

        $key = ['equipment_id' => $equipmentId, 'client_id' => $clientId, 'job_type' => 'rental_demo'];
        DB::table('equipment_jobs')->updateOrInsert(
            $key,
            $this->onlyColumns('equipment_jobs', [
                'owner_id' => $ownerId,
                'equipment_owner_id' => $ownerId,
                'pickup_location' => 'Owner yard, Nepal',
                'delivery_location' => 'Client project site, Nepal',
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(6)->toDateString(),
                'description' => 'Seeded equipment job with realistic status and pricing.',
                'location' => 'Kathmandu Valley demo project',
                'price' => $price,
                'proposed_price' => $price,
                'amount' => $price,
                'paid_amount' => $status === 'completed' ? $price : 0,
                'proposal_message' => 'Demo owner proposal is ready.',
                'client_message' => 'Client accepted demo equipment schedule.',
                'status' => $status,
                'accepted_by_client_status' => in_array($status, ['accepted', 'completed'], true) ? 'accepted' : null,
                'accepted_by_owner_status' => in_array($status, ['accepted', 'completed'], true) ? 'accepted' : null,
                'accepted_at' => in_array($status, ['accepted', 'completed'], true) ? now() : null,
                'started_at' => $status === 'completed' ? now()->subDays(2) : null,
                'completed_at' => $status === 'completed' ? now() : null,
                'request_date' => now()->subDays(2),
                'completion_date' => $status === 'completed' ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ])
        );

        return (int) DB::table('equipment_jobs')->where($key)->value('id');
    }

    private function securityAgencyFor(int $userId, string $name, string $registration, string $license, string $address): int
    {
        DB::table('security_agencies')->updateOrInsert(
            ['user_id' => $userId],
            $this->onlyColumns('security_agencies', [
                'agency_name' => $name,
                'registration_number' => $registration,
                'license_number' => $license,
                'address' => $address,
                'phone' => '9805556699',
                'emergency_phone' => '9805556600',
                'email' => 'security.birgunj@kwdc.test',
                'services_offered' => 'Border warehouse guard, gate pass check, night patrol',
                'year_established' => '2078',
                'status' => 'approved',
                'is_verified' => true,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ])
        );

        return (int) DB::table('security_agencies')->where('user_id', $userId)->value('id');
    }

    private function securityAssignmentRecord(int $warehouseId, int $agencyId, int $personnelId, string $shift): int
    {
        $this->securityAssignment($warehouseId, $agencyId, $personnelId);

        DB::table('security_assignments')
            ->where(['warehouse_id' => $warehouseId, 'agency_id' => $agencyId, 'personnel_id' => $personnelId])
            ->update($this->onlyColumns('security_assignments', ['shift' => $shift, 'updated_at' => now()]));

        return (int) DB::table('security_assignments')->where(['warehouse_id' => $warehouseId, 'agency_id' => $agencyId, 'personnel_id' => $personnelId])->value('id');
    }

    private function securityIncident(int $warehouseId, int $reportedByUserId, ?int $assignmentId, string $category, string $description, string $severity, string $status): void
    {
        if (!Schema::hasTable('security_incidents')) {
            return;
        }

        DB::table('security_incidents')->updateOrInsert(
            ['warehouse_id' => $warehouseId, 'category' => $category, 'description' => $description],
            $this->onlyColumns('security_incidents', [
                'reported_by_user_id' => $reportedByUserId,
                'assignment_id' => $assignmentId,
                'incident_time' => now()->subHours(6),
                'severity' => $severity,
                'actions_taken' => 'Demo record: supervisor notified, gate log checked, and follow-up reminder created.',
                'attachments' => json_encode([]),
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ])
        );
    }

    private function partnerEarning(int $partnerId, string $orderType, int $orderId, float $amount, string $status): void
    {
        if (!Schema::hasTable('partner_earnings') || $orderId === 0) {
            return;
        }

        DB::table('partner_earnings')->updateOrInsert(
            ['partner_id' => $partnerId, 'order_type' => $orderType, 'order_id' => $orderId],
            $this->onlyColumns('partner_earnings', [
                'amount' => $amount,
                'status' => $status,
                'earned_at' => now()->subDay(),
                'notes' => 'Seeded partner earning for demo dashboard and payment review.',
                'created_at' => now(),
                'updated_at' => now(),
            ])
        );
    }

    private function notifyDispatchFlow(User $admin, User $client, ?User $driver, int $dispatchId, string $tracking, string $status, string $pickup, string $delivery): void
    {
        $this->notification($client->id, 'Dispatch ' . $tracking . ' is ' . $status, $pickup . ' to ' . $delivery . ' has a demo status update.', 'dispatch', $dispatchId, 'dispatch_order');
        $this->notification($admin->id, 'Dispatch monitoring: ' . $tracking, 'Admin demo monitoring has a ' . $status . ' dispatch.', 'dispatch', $dispatchId, 'dispatch_order');

        if ($driver) {
            $this->notification($driver->id, 'Driver job update ' . $tracking, 'You have demo dispatch activity from ' . $pickup . ' to ' . $delivery . '.', 'driver_job', $dispatchId, 'dispatch_order');
        }
    }

    private function notifyPickupFlow(User $admin, User $client, ?User $driver, int $pickupId, string $tracking, string $status, string $pickupAddress, string $destination): void
    {
        $this->notification($client->id, 'Pickup ' . $tracking . ' is ' . $status, $pickupAddress . ' to ' . $destination . ' has a demo status update.', 'pickup', $pickupId, 'pickup_request');
        $this->notification($admin->id, 'Pickup monitoring: ' . $tracking, 'Admin demo monitoring has a ' . $status . ' pickup request.', 'pickup', $pickupId, 'pickup_request');

        if ($driver) {
            $this->notification($driver->id, 'Pickup job update ' . $tracking, 'You have demo pickup activity from ' . $pickupAddress . '.', 'driver_pickup', $pickupId, 'pickup_request');
        }
    }

    private function reminder(int $userId, string $title, string $notes): void
    {
        DB::table('user_reminders')->updateOrInsert(
            ['user_id' => $userId, 'title' => $title],
            [
                'notes' => $notes,
                'starts_at' => now()->addDay(),
                'remind_at' => now()->addHours(18),
                'status' => 'scheduled',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function notification(int $userId, string $title, string $message, string $type = 'demo', ?int $relatedId = null, ?string $relatedType = null): void
    {
        DB::table('notifications')->updateOrInsert(
            ['user_id' => $userId, 'title' => $title],
            $this->onlyColumns('notifications', [
                'type' => $type,
                'message' => $message,
                'related_id' => $relatedId,
                'related_type' => $relatedType,
                'notification_number' => 'NOT-DEMO-' . strtoupper(substr(md5($userId . $title), 0, 10)),
                'recipient_email' => User::whereKey($userId)->value('email'),
                'recipient_name' => User::whereKey($userId)->value('name'),
                'subject' => $title,
                'status' => 'sent',
                'sent_at' => now(),
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ])
        );
    }

    private function onlyColumns(string $table, array $data): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        return array_intersect_key($data, array_flip(Schema::getColumnListing($table)));
    }
}
