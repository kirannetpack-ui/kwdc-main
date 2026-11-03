<?php

namespace Tests\Feature;

use Database\Seeders\NepaliDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NepaliDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_nepali_demo_seeder_fills_major_app_areas_with_notifications(): void
    {
        $this->seed(NepaliDemoSeeder::class);

        $this->assertGreaterThanOrEqual(15, DB::table('users')->count());
        $this->assertGreaterThanOrEqual(120, DB::table('notifications')->count());
        $this->assertGreaterThanOrEqual(18, DB::table('dispatch_orders')->count());
        $this->assertGreaterThanOrEqual(13, DB::table('pickup_requests')->count());
        $this->assertGreaterThanOrEqual(4, DB::table('warehouses')->count());
        $this->assertGreaterThanOrEqual(5, DB::table('warehouse_requests')->count());
        $this->assertGreaterThanOrEqual(9, DB::table('stocks')->count());
        $this->assertGreaterThanOrEqual(25, DB::table('boxes')->count());
        $this->assertGreaterThanOrEqual(5, DB::table('equipment')->count());
        $this->assertGreaterThanOrEqual(3, DB::table('equipment_requests')->count());
        $this->assertGreaterThanOrEqual(3, DB::table('equipment_jobs')->count());
        $this->assertGreaterThanOrEqual(2, DB::table('security_agencies')->count());
        $this->assertGreaterThanOrEqual(2, DB::table('security_incidents')->count());
        $this->assertGreaterThanOrEqual(50, DB::table('user_reminders')->count());
        $this->assertGreaterThanOrEqual(5, DB::table('invoices')->count());

        if (Schema::hasTable('partner_earnings')) {
            $this->assertGreaterThanOrEqual(7, DB::table('partner_earnings')->count());
        }
    }
}
