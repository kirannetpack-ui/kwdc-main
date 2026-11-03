<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_cannot_open_driver_zone(): void
    {
        $client = User::factory()->create(['role' => 'client', 'user_type' => 'client']);

        $this->actingAs($client)
            ->get('/driver/dashboard')
            ->assertForbidden();
    }

    public function test_driver_cannot_open_client_zone(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'user_type' => 'driver']);

        $this->actingAs($driver)
            ->get('/client/reports')
            ->assertForbidden();
    }

    public function test_client_cannot_open_equipment_owner_zone(): void
    {
        $client = User::factory()->create(['role' => 'client', 'user_type' => 'client']);

        $this->actingAs($client)
            ->get('/equipment/dashboard')
            ->assertForbidden();
    }

    public function test_client_cannot_open_property_owner_zone(): void
    {
        $client = User::factory()->create(['role' => 'client', 'user_type' => 'client']);

        $this->actingAs($client)
            ->get('/property/pending')
            ->assertForbidden();
    }

    public function test_client_cannot_open_security_agency_zone(): void
    {
        $client = User::factory()->create(['role' => 'client', 'user_type' => 'client']);

        $this->actingAs($client)
            ->get('/security/dashboard')
            ->assertForbidden();
    }
}
