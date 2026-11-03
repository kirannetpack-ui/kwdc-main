<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class EnterpriseReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $client;
    protected User $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
            'email_verified_at' => now(),
            'phone' => '9801234567',
        ]);

        $this->client = User::factory()->create([
            'role' => 'client',
            'is_client' => true,
            'is_active' => true,
            'email_verified_at' => now(),
            'phone' => '9841987654',
        ]);

        $this->driver = User::factory()->create([
            'role' => 'driver',
            'is_driver' => true,
            'is_active' => true,
            'email_verified_at' => now(),
            'phone' => '9812345678',
        ]);
    }

    /**
     * Test 1: SMS Gateway Service and Nepal mobile normalization
     */
    public function test_sms_service_normalizes_nepal_numbers_and_logs_sent_messages(): void
    {
        $smsService = app(SmsService::class);

        $normalized = $smsService->normalizePhoneNumber('+977-9841234567');
        $this->assertEquals('9841234567', $normalized);

        $invalid = $smsService->normalizePhoneNumber('12345');
        $this->assertNull($invalid);

        $result = $smsService->send('9841234567', 'Test SMS message', 'test');
        $this->assertTrue($result['success']);
        $this->assertEquals('9841234567', $result['to']);
    }

    /**
     * Test 2: Bilingual Locale Switching (EN / नेपाली)
     */
    public function test_user_can_switch_locale_between_english_and_nepali(): void
    {
        $response = $this->get(route('locale.switch', 'ne'));
        $response->assertRedirect();
        $this->assertEquals('ne', session('locale'));

        // Verify SetLocale middleware translates public welcome page to Nepali
        $welcomeNe = $this->withSession(['locale' => 'ne'])->get('/');
        $welcomeNe->assertStatus(200);
        $welcomeNe->assertSee('सुरु गर्नुहोस्');

        $responseEn = $this->get(route('locale.switch', 'en'));
        $responseEn->assertRedirect();
        $this->assertEquals('en', session('locale'));

        $welcomeEn = $this->withSession(['locale' => 'en'])->get('/');
        $welcomeEn->assertStatus(200);
        $welcomeEn->assertSee('Get started');

        // Verify authenticated dashboard translates sidebar & header to Nepali and English
        $dashboardNe = $this->actingAs($this->admin)->withSession(['locale' => 'ne'])->get('/dashboard');
        $dashboardNe->assertStatus(200);
        $dashboardNe->assertSee('कार्यकारी सिंहावलोकन');
        $dashboardNe->assertSee('प्रतिक्षारत स्वीकृतिहरू');

        $dashboardEn = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/dashboard');
        $dashboardEn->assertStatus(200);
        $dashboardEn->assertSee('Executive Overview');
        $dashboardEn->assertSee('Pending Approvals');
    }

    /**
     * Test 3: PWA Manifest and Offline Fallback Screen are Accessible
     */
    public function test_pwa_manifest_and_offline_page_are_accessible(): void
    {
        $responseManifest = $this->get('/manifest.json');
        $responseManifest->assertStatus(200);
        $responseManifest->assertSee('KTM-WDC Logistics');

        $responseOffline = $this->get('/offline.html');
        $responseOffline->assertStatus(200);
        $responseOffline->assertSee('Offline Mode');
    }

    /**
     * Test 4: IRD Schedule 13 (अनुसूची १३) Sales Book Export
     */
    public function test_admin_can_export_ird_schedule_13_sales_book_csv(): void
    {
        $warehouse = Warehouse::create([
            'name' => 'Boudha Central Depot',
            'location' => 'Boudha, Kathmandu',
            'user_id' => $this->admin->id,
            'status' => 'approved',
        ]);

        $request = WarehouseRequest::create([
            'client_id' => $this->client->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'approved',
            'space_required' => 50,
        ]);

        Invoice::create([
            'invoice_number' => 'INV-IRD-TEST-001',
            'client_id' => $this->client->id,
            'warehouse_request_id' => $request->id,
            'amount' => 10000.00,
            'grand_total' => 11300.00,
            'vat_amount' => 1300.00,
            'status' => 'paid',
            'payment_status' => 'paid',
            'due_date' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.invoices.export-schedule-13'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Test non-admin authorization guard
        $forbiddenResponse = $this->actingAs($this->client)->get(route('admin.invoices.export-schedule-13'));
        $forbiddenResponse->assertStatus(403);
    }

    /**
     * Test 5: Vehicle Fleet Compliance and Blue Book Expiry Monitor
     */
    public function test_vehicle_fleet_compliance_status_and_admin_view(): void
    {
        $compliantVehicle = Vehicle::create([
            'user_id' => $this->admin->id,
            'driver_id' => $this->driver->id,
            'vehicle_number' => 'BA 2 CHA 9988',
            'vehicle_type' => 'Covered Container Truck',
            'capacity' => 5.0,
            'capacity_unit' => 'Tons',
            'fitness_valid_until' => now()->addMonths(6),
            'insurance_valid_until' => now()->addMonths(8),
            'permit_valid_until' => now()->addMonths(4),
            'is_verified' => true,
        ]);

        $this->assertTrue($compliantVehicle->isCompliant());
        $this->assertEmpty($compliantVehicle->getComplianceIssues());

        $expiredVehicle = Vehicle::create([
            'user_id' => $this->admin->id,
            'driver_id' => $this->driver->id,
            'vehicle_number' => 'BA 1 KHA 1234',
            'vehicle_type' => 'Flatbed Cargo',
            'capacity' => 10.0,
            'capacity_unit' => 'Tons',
            'fitness_valid_until' => now()->subDays(5),
            'insurance_valid_until' => now()->addMonths(2),
            'permit_valid_until' => now()->addMonths(1),
            'is_verified' => true,
        ]);

        $this->assertFalse($expiredVehicle->isCompliant());
        $this->assertNotEmpty($expiredVehicle->getComplianceIssues());

        $adminViewResponse = $this->actingAs($this->admin)->get(route('admin.vehicles.compliance'));
        $adminViewResponse->assertStatus(200);
        $adminViewResponse->assertSee('Fleet Compliance & Blue Book Monitor', false);
        $adminViewResponse->assertSee('BA 2 CHA 9988');
    }

    /**
     * Test 6: Two-Factor Authentication (2FA) Generation and Verification
     */
    public function test_two_factor_authentication_workflow(): void
    {
        $user = $this->client;
        $this->assertFalse((bool) $user->two_factor_enabled);

        // Toggle 2FA ON
        $response = $this->actingAs($user)->post(route('profile.two-factor.toggle'));
        $response->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
        $this->assertNotNull($user->two_factor_code);
        $this->assertNotNull($user->two_factor_expires_at);

        $validCode = $user->two_factor_code;

        // Verify with invalid code
        $this->assertFalse($user->verifyTwoFactorCode('000000'));

        // Verify with valid code
        $this->assertTrue($user->verifyTwoFactorCode($validCode));

        $user->refresh();
        $this->assertNull($user->two_factor_code);
        $this->assertNull($user->two_factor_expires_at);

        // Toggle 2FA OFF
        $responseOff = $this->actingAs($user)->post(route('profile.two-factor.toggle'));
        $responseOff->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
    }
}
