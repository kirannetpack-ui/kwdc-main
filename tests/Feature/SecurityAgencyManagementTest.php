<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\SecurityAgency;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityAgencyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_agency_can_create_personnel_and_record_notification(): void
    {
        $user = User::factory()->create([
            'role' => 'security_agency',
            'email' => 'agency@example.com',
        ]);

        SecurityAgency::create([
            'user_id' => $user->id,
            'agency_name' => 'Alpha Guard',
            'email' => 'agency@example.com',
            'phone' => '9800000000',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->post('/security/personnel', [
            'name' => 'Ram Shrestha',
            'phone' => '9876543210',
            'email' => 'ram@example.com',
            'position' => 'Senior Guard',
            'status' => 'active',
        ]);

        $response->assertRedirect('/security/personnel');
        $this->assertDatabaseHas('security_personnels', ['name' => 'Ram Shrestha']);
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'type' => 'security_personnel_created']);
    }

    public function test_security_agency_can_update_and_delete_personnel_and_record_notifications(): void
    {
        $user = User::factory()->create(['role' => 'security_agency']);
        $agency = SecurityAgency::create([
            'user_id' => $user->id,
            'agency_name' => 'Bravo Guard',
            'email' => $user->email,
            'phone' => '9800000001',
            'status' => 'approved',
        ]);

        $personnel = $agency->personnel()->create([
            'name' => 'Old Name',
            'phone' => '9800000002',
            'email' => 'old@example.com',
            'position' => 'Guard',
            'status' => 'active',
        ]);

        $updateResponse = $this->actingAs($user)->put('/security/personnel/' . $personnel->id, [
            'name' => 'Updated Name',
            'phone' => '9800000002',
            'email' => 'updated@example.com',
            'position' => 'Supervisor',
            'status' => 'active',
        ]);

        $updateResponse->assertRedirect('/security/personnel');
        $this->assertDatabaseHas('security_personnels', ['id' => $personnel->id, 'name' => 'Updated Name']);
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'type' => 'security_personnel_updated']);

        $deleteResponse = $this->actingAs($user)->delete('/security/personnel/' . $personnel->id);

        $deleteResponse->assertRedirect('/security/personnel');
        $this->assertDatabaseMissing('security_personnels', ['id' => $personnel->id]);
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'type' => 'security_personnel_deleted']);
    }

    public function test_security_agency_can_manage_goods_and_assignments_with_notifications(): void
    {
        $user = User::factory()->create(['role' => 'security_agency', 'email' => 'agency2@example.com']);
        $agency = SecurityAgency::create([
            'user_id' => $user->id,
            'agency_name' => 'Charlie Guard',
            'email' => 'agency2@example.com',
            'phone' => '9800000003',
            'status' => 'approved',
        ]);

        $warehouse = \App\Models\Warehouse::create([
            'user_id' => $user->id,
            'name' => 'Main Warehouse',
            'location' => 'Kathmandu',
            'address' => 'Test Street 1',
            'latitude' => 27.7172,
            'longitude' => 85.3240,
            'status' => 'approved',
        ]);

        $goodResponse = $this->actingAs($user)->post('/security/goods', [
            'item_name' => 'Guard Gear Kit',
            'category' => 'Equipment',
            'quantity_available' => 10,
            'unit_price' => 150.00,
            'status' => 'available',
        ]);

        $goodResponse->assertRedirect('/security/goods');
        $this->assertDatabaseHas('security_goods', ['item_name' => 'Guard Gear Kit']);
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'type' => 'security_good_created']);

        $personnel = $agency->personnel()->create([
            'name' => 'Assignment Guard',
            'phone' => '9800000004',
            'position' => 'Guard',
            'status' => 'active',
        ]);

        $assignmentResponse = $this->actingAs($user)->post('/security/assignments', [
            'warehouse_id' => $warehouse->id,
            'personnel_id' => $personnel->id,
            'start_date' => '2026-08-15',
            'shift' => 'day',
            'status' => 'active',
            'total_cost' => 500,
        ]);

        $assignmentResponse->assertRedirect('/security/assignments');
        $this->assertDatabaseHas('security_assignments', ['warehouse_id' => $warehouse->id, 'agency_id' => $agency->id]);
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'type' => 'security_assignment_created']);
    }

    public function test_security_agencies_cannot_modify_another_agencys_resources(): void
    {
        Mail::fake();

        $ownerUser = User::factory()->create(['role' => 'security_agency']);
        $otherUser = User::factory()->create(['role' => 'security_agency']);

        $ownerAgency = SecurityAgency::create([
            'user_id' => $ownerUser->id,
            'agency_name' => 'Delta Guard',
            'email' => $ownerUser->email,
            'phone' => '9800000005',
            'status' => 'approved',
        ]);

        $otherAgency = SecurityAgency::create([
            'user_id' => $otherUser->id,
            'agency_name' => 'Echo Guard',
            'email' => $otherUser->email,
            'phone' => '9800000006',
            'status' => 'approved',
        ]);

        $ownerPersonnel = $ownerAgency->personnel()->create([
            'name' => 'Protected Guard',
            'phone' => '9800000007',
            'email' => 'protected@example.com',
            'position' => 'Guard',
            'status' => 'active',
        ]);

        $otherPersonnel = $otherAgency->personnel()->create([
            'name' => 'Other Guard',
            'phone' => '9800000008',
            'position' => 'Guard',
            'status' => 'active',
        ]);

        $ownerGood = $ownerAgency->goods()->create([
            'item_name' => 'Protected Radio',
            'category' => 'Equipment',
            'quantity_available' => 5,
            'unit_price' => 250,
            'status' => 'available',
        ]);

        $warehouse = Warehouse::create([
            'user_id' => $ownerUser->id,
            'name' => 'Protected Warehouse',
            'location' => 'Kathmandu',
            'address' => 'Secure Street 1',
            'latitude' => 27.7172,
            'longitude' => 85.3240,
            'status' => 'approved',
        ]);

        $ownerAssignment = $ownerAgency->assignments()->create([
            'warehouse_id' => $warehouse->id,
            'personnel_id' => $ownerPersonnel->id,
            'start_date' => '2026-08-15',
            'shift' => 'day',
            'status' => 'active',
            'total_cost' => 500,
        ]);

        $this->actingAs($otherUser)
            ->put('/security/personnel/' . $ownerPersonnel->id, [
                'name' => 'Hijacked Guard',
                'phone' => '9800000007',
                'email' => 'hijacked@example.com',
                'position' => 'Supervisor',
                'status' => 'inactive',
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete('/security/personnel/' . $ownerPersonnel->id)
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->put('/security/goods/' . $ownerGood->id, [
                'item_name' => 'Hijacked Radio',
                'category' => 'Equipment',
                'quantity_available' => 1,
                'unit_price' => 1,
                'status' => 'maintenance',
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete('/security/goods/' . $ownerGood->id)
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->put('/security/assignments/' . $ownerAssignment->id, [
                'warehouse_id' => $warehouse->id,
                'personnel_id' => $otherPersonnel->id,
                'start_date' => '2026-08-20',
                'shift' => 'night',
                'status' => 'cancelled',
                'total_cost' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete('/security/assignments/' . $ownerAssignment->id)
            ->assertForbidden();

        $this->assertDatabaseHas('security_personnels', [
            'id' => $ownerPersonnel->id,
            'agency_id' => $ownerAgency->id,
            'name' => 'Protected Guard',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('security_goods', [
            'id' => $ownerGood->id,
            'agency_id' => $ownerAgency->id,
            'item_name' => 'Protected Radio',
            'quantity_available' => 5,
        ]);

        $this->assertDatabaseHas('security_assignments', [
            'id' => $ownerAssignment->id,
            'agency_id' => $ownerAgency->id,
            'personnel_id' => $ownerPersonnel->id,
            'shift' => 'day',
            'status' => 'active',
        ]);
    }

    public function test_security_incidents_index_is_scoped_and_dead_crud_routes_are_not_registered(): void
    {
        $user = User::factory()->create(['role' => 'security_agency']);
        $agency = SecurityAgency::create([
            'user_id' => $user->id,
            'agency_name' => 'Foxtrot Guard',
            'email' => $user->email,
            'phone' => '9800000009',
            'status' => 'approved',
        ]);

        $warehouse = Warehouse::create([
            'user_id' => $user->id,
            'name' => 'Incident Warehouse',
            'location' => 'Kathmandu',
            'address' => 'Watch Street 1',
            'latitude' => 27.7172,
            'longitude' => 85.3240,
            'status' => 'approved',
        ]);

        $assignment = $agency->assignments()->create([
            'warehouse_id' => $warehouse->id,
            'start_date' => '2026-08-15',
            'shift' => 'day',
            'status' => 'active',
        ]);

        \App\Models\SecurityIncident::create([
            'warehouse_id' => $warehouse->id,
            'reported_by_user_id' => $user->id,
            'assignment_id' => $assignment->id,
            'incident_time' => '2026-08-20 09:00:00',
            'category' => 'Gate breach',
            'description' => 'Unauthorized entry attempt.',
            'severity' => 'high',
            'status' => 'reported',
        ]);

        $this->actingAs($user)
            ->get('/security/incidents')
            ->assertOk()
            ->assertSee('Foxtrot Guard')
            ->assertSee('Gate breach')
            ->assertSee('Incident Warehouse');

        $this->assertTrue(Route::has('security.incidents.index'));
        $this->assertFalse(Route::has('security.incidents.create'));
        $this->assertFalse(Route::has('security.incidents.store'));
        $this->assertFalse(Route::has('security.incidents.show'));
        $this->assertFalse(Route::has('security.incidents.edit'));
        $this->assertFalse(Route::has('security.incidents.update'));
        $this->assertFalse(Route::has('security.incidents.destroy'));
    }
}
