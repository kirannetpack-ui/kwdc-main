<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SecurityAgency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkspacePagesTest extends TestCase
{
    use RefreshDatabase;

    public static function workspaces(): array
    {
        return [
            ['security_agency', ['/dashboard', '/security/dashboard', '/security/profile', '/security/personnel', '/security/goods', '/security/assignments', '/security/incidents']],
            ['client', ['/dashboard', '/pickup', '/dispatch', '/pickup/direct-create', '/dispatch/direct-create', '/stock', '/boxes', '/equipment-requests', '/profile', '/notifications', '/reminders', '/payment/history']],
            ['driver', ['/dashboard', '/driver/dashboard', '/driver/jobs', '/driver/pickups', '/driver/earnings', '/driver/rates', '/driver/vehicles']],
            ['property_owner', ['/dashboard', '/property/pending', '/property/approved', '/property/rejected', '/property/requests', '/property/analytics']],
            ['equipment_owner', ['/dashboard', '/equipment/dashboard', '/equipment/list', '/equipment/register', '/equipment/jobs/requests', '/equipment/jobs/active', '/equipment/jobs/history', '/equipment/jobs/earnings']],
            ['admin', ['/dashboard', '/admin/warehouses', '/admin/requests', '/admin/stocks', '/admin/vehicles', '/admin/dispatch', '/admin/pickup-requests', '/admin/clients', '/admin/drivers', '/admin/equipment-list', '/admin/invoices', '/admin/reports', '/admin/security/agencies']],
        ];
    }

    /** @dataProvider workspaces */
    public function test_role_can_open_its_operational_pages(string $role, array $paths): void
    {
        Mail::fake();
        $user = User::factory()->create(['role' => $role, 'user_type' => $role, 'is_active' => true, 'is_admin' => $role === 'admin']);
        if ($role === 'security_agency') {
            SecurityAgency::create(['user_id' => $user->id, 'agency_name' => 'Test Agency', 'registration_number' => 'TEST-001', 'status' => 'approved']);
        }
        foreach ($paths as $path) {
            $response = $this->actingAs($user)->get($path);
            $this->assertSame(200, $response->status(), "$role $path returned ".$response->status());
        }
    }
}
