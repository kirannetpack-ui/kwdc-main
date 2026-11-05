<?php
namespace Tests\Feature;

use App\Models\EquipmentRequest;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EquipmentRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_create_and_view_request_without_assigning_privileged_fields(): void
    {
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);
        $other = User::factory()->create(['role' => 'client', 'is_active' => true]);
        $this->actingAs($client)->postJson('/equipment-requests', [
            'equipment_type' => 'Forklift', 'location' => 'Kathmandu',
            'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDays(2)->toDateString(),
            'client_id' => $other->id, 'status' => 'approved', 'quoted_price' => 1,
        ])->assertCreated()->assertJson(['success' => true]);
        $record = EquipmentRequest::firstOrFail();
        $this->assertSame($client->id, $record->client_id);
        $this->assertSame('pending', $record->status);
        $this->assertSame('Kathmandu', $record->location);
        $this->assertNull($record->quoted_price);
        $this->get('/equipment-requests/'.$record->id)->assertOk();
        $this->actingAs($other)->get('/equipment-requests/'.$record->id)->assertForbidden();
        $this->actingAs($other)->post('/equipment-requests/'.$record->id.'/approve')->assertForbidden();
    }

    public function test_equipment_owner_cannot_see_other_clients_unassigned_requests(): void
    {
        $client = User::factory()->create();
        EquipmentRequest::create(['client_id' => $client->id, 'equipment_type' => 'Private request', 'location' => 'Kathmandu', 'start_date' => now(), 'end_date' => now()->addDay(), 'status' => 'pending']);
        $owner = User::factory()->create(['role' => 'equipment_owner', 'is_active' => true]);
        $this->actingAs($owner)->get('/equipment-requests')->assertOk()->assertDontSee('Private request');
    }

    public function test_equipment_rejects_executable_uploads(): void
    {
        $owner = User::factory()->create(['role' => 'equipment_owner', 'is_active' => true]);
        $this->actingAs($owner)->post('/equipment', [
            'name' => 'Forklift', 'type' => 'Forklift', 'location' => 'Kathmandu',
            'front_photo' => UploadedFile::fake()->create('payload.php', 1, 'application/x-php'),
        ])->assertSessionHasErrors('front_photo');
    }

    public function test_equipment_request_can_be_approved_fulfilled_and_returned_with_assigned_equipment(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true, 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'equipment_owner', 'is_active' => true]);
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);
        $equipment = Equipment::create([
            'owner_id' => $owner->id,
            'user_id' => $owner->id,
            'name' => 'Warehouse Forklift',
            'type' => 'Forklift',
            'location' => 'Kathmandu',
            'status' => 'available',
        ]);
        $record = EquipmentRequest::create([
            'client_id' => $client->id,
            'equipment_type' => 'Forklift',
            'location' => 'Kathmandu',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post('/equipment-requests/'.$record->id.'/approve', ['assigned_equipment_id' => $equipment->id])
            ->assertRedirect();

        $record->refresh();
        $this->assertSame('approved', $record->status);
        $this->assertSame($equipment->id, $record->assigned_equipment_id);

        $overlap = $record->replicate();
        $overlap->forceFill(['status' => 'pending', 'equipment_id' => null, 'assigned_equipment_id' => null])->save();
        $this->post('/equipment-requests/'.$overlap->id.'/approve', ['assigned_equipment_id' => $equipment->id])
            ->assertConflict();

        $this->post('/equipment-requests/'.$record->id.'/fulfill')->assertRedirect();
        $this->assertSame('fulfilled', $record->refresh()->status);
        $this->assertSame('rented', $equipment->refresh()->status);

        $this->post('/equipment-requests/'.$record->id.'/return')->assertRedirect();
        $this->assertSame('returned', $record->refresh()->status);
        $this->assertSame('available', $equipment->refresh()->status);
    }

    public function test_equipment_quote_requires_client_acceptance_and_prepayment(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $client = User::factory()->create(['role' => 'client']);
        $equipment = Equipment::create([
            'owner_id' => $admin->id, 'name' => 'Forklift', 'type' => 'Forklift',
            'location' => 'Kathmandu', 'status' => 'available',
        ]);
        $record = EquipmentRequest::create([
            'client_id' => $client->id, 'equipment_type' => 'Forklift', 'location' => 'Kathmandu',
            'start_date' => today(), 'end_date' => today()->addDay(), 'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('equipment-requests.approve', $record), [
            'assigned_equipment_id' => $equipment->id, 'quoted_price' => 1500,
        ])->assertRedirect();
        $this->assertSame('pending', $record->fresh()->status);

        $this->actingAs($client)->post(route('equipment-requests.accept-quote', $record))->assertRedirect();
        $invoice = \App\Models\Invoice::where('order_type', 'equipment')->where('order_id', $record->id)->sole();
        $this->assertSame('1500.00', $invoice->amount);
        $this->actingAs($admin)->post(route('equipment-requests.fulfill', $record))->assertConflict();
        $invoice->markAsPaid('bank_transfer');
        $this->post(route('equipment-requests.fulfill', $record))->assertRedirect();
    }
}
