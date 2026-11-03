<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use App\Models\UserReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinimalWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_details_and_decisions_are_scoped_and_cannot_be_repeated(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $other = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $warehouse = Warehouse::factory()->create();
        $record = WarehouseRequest::create(['client_id' => $client->id, 'warehouse_id' => $warehouse->id, 'required_area' => 100, 'duration_months' => 2, 'purpose' => 'Demo inventory', 'status' => 'pending']);
        $url = '/warehouse-requests/'.$record->id;
        $this->actingAs($client)->get($url)->assertOk()->assertSee('Demo inventory');
        $this->actingAs($other)->get($url)->assertForbidden();
        $this->actingAs($client)->post($url.'/decision', ['decision' => 'approved'])->assertForbidden();
        $this->actingAs($admin)->post($url.'/decision', ['decision' => 'approved'])->assertRedirect();
        $this->assertSame('approved', $record->fresh()->status);
        $this->post($url.'/decision', ['decision' => 'rejected'])->assertStatus(409);
    }

    public function test_calendar_renders_dates_and_protects_other_users_events(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $event = UserReminder::create(['user_id' => $other->id, 'title' => 'Private appointment', 'starts_at' => now(), 'status' => 'scheduled']);
        $this->actingAs($user)->get('/reminders')->assertOk()->assertSee('calendar-grid')->assertDontSee('Private appointment');
        $this->put('/reminders/'.$event->id, ['title' => 'Changed', 'starts_at' => now()->toDateTimeString()])->assertForbidden();
        $this->getJson('/reminders?month=invalid')->assertUnprocessable();
        $this->postJson('/ai/voice-assistant', ['message' => []])->assertUnprocessable();
    }
}
