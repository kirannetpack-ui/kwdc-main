<?php

namespace Tests\Feature;

use App\Models\DispatchOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DriverDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_dashboard_hides_available_dispatches_while_busy(): void
    {
        $driver = User::factory()->create([
            'role' => 'driver',
            'is_driver' => true,
        ]);

        $client = User::factory()->create([
            'role' => 'client',
            'is_client' => true,
        ]);

        DispatchOrder::factory()->create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'pickup_address' => 'Kalimati, Kathmandu',
            'delivery_address' => 'Boudha, Kathmandu',
            'status' => 'assigned',
            'base_price' => 3200,
            'driver_earning' => 2400,
        ]);

        DispatchOrder::factory()->create([
            'client_id' => $client->id,
            'driver_id' => null,
            'pickup_address' => 'Birgunj Dry Port',
            'delivery_address' => 'Hetauda Industrial Area',
            'status' => 'pending',
            'base_price' => 14500,
        ]);

        DB::table('pickup_requests')->insert([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'tracking_id' => 'PUP-TEST-001',
            'total_distance' => 8.5,
            'total_price' => 1800,
            'status' => 'assigned',
            'notes' => 'Pickup route near Asan, Kathmandu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($driver)
            ->get(route('driver.dashboard'))
            ->assertOk()
            ->assertSee('Driver Dashboard')
            ->assertSee('Kalimati, Kathmandu')
            ->assertDontSee('Birgunj Dry Port')
            ->assertSee('Pickup route near Asan, Kathmandu');
    }

    public function test_driver_can_decline_an_offer_and_only_accept_one_dispatch_at_a_time(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'is_driver' => true]);
        $declined = DispatchOrder::factory()->create(['driver_id' => $driver->id, 'status' => 'pending']);
        $first = DispatchOrder::factory()->create(['driver_id' => null, 'status' => 'pending', 'payment_method' => 'cod']);
        $next = DispatchOrder::factory()->create(['driver_id' => null, 'status' => 'pending']);

        $pickupId = DB::table('pickup_requests')->insertGetId([
            'client_id' => $first->client_id,
            'tracking_id' => 'PUP-OPEN-OFFER',
            'status' => 'pending',
            'total_distance' => 8,
            'total_price' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($driver)->get(route('driver.available-jobs'))->assertOk()->assertSee('PUP-OPEN-OFFER');
        $this->actingAs($driver)
            ->post(route('driver.jobs.reject', $declined))
            ->assertRedirect();
        $this->assertNull($declined->fresh()->driver_id);
        $otherDriver = User::factory()->create(['role' => 'driver', 'is_driver' => true]);
        $this->actingAs($otherDriver)->get(route('driver.available-jobs'))->assertOk()
            ->assertSee(route('driver.jobs.accept', $declined));
        $this->actingAs($driver);
        $this->post(route('driver.jobs.accept', $declined))->assertNotFound();

        \Illuminate\Support\Facades\Mail::fake();
        $this->post(route('driver.jobs.accept', $first))->assertRedirect();
        $this->assertTrue((bool) $first->fresh()->tracking_enabled);
        $this->postJson(route('dispatch.update-location', $first), [
            'latitude' => 27.71, 'longitude' => 85.32,
        ])->assertOk();
        $this->getJson(route('dispatch.tracking-position', $first))->assertJsonPath('active', true);
        $this->post(route('driver.jobs.accept', $next))->assertConflict();
        $this->post(route('driver.accept-pickup', $pickupId))->assertConflict();

        $this->assertDatabaseHas('invoices', ['order_type' => 'dispatch', 'order_id' => $first->id, 'payment_method' => 'cod', 'payment_status' => 'unpaid']);
        $invoice = \App\Models\Invoice::where('order_type', 'dispatch')->where('order_id', $first->id)->sole();
        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\InvoiceGenerated::class, 1);
        $this->actingAs($first->client)->get(route('invoices.index'))->assertOk()->assertSee($invoice->invoice_number);
        $this->actingAs($driver)->get(route('invoice.verify', $invoice->invoice_number))->assertForbidden();
        $this->get(route('dispatch.pdf', $first))->assertOk();
        $this->get(route('driver.jobs'))->assertOk()->assertSee('Collect NPR');
        $copy = view('pdf.invoice', ['invoice' => $invoice, 'company' => config('company'), 'deliveryCopy' => $first])->render();
        $this->assertStringContainsString($invoice->invoice_number, $copy);
        $this->assertStringContainsString('Delivery points', $copy);
        $this->assertStringNotContainsString('Total Due', $copy);
        $this->assertStringNotContainsString('Unit Price', $copy);
        $this->post(route('driver.jobs.start', $first))->assertRedirect();
        $this->post(route('driver.jobs.deliver', $first))->assertRedirect();
        $this->assertFalse((bool) $first->fresh()->tracking_enabled);
        $this->postJson(route('dispatch.update-location', $first), [
            'latitude' => 27.72, 'longitude' => 85.33,
        ])->assertConflict();
        $this->getJson(route('dispatch.tracking-position', $first))
            ->assertJsonPath('active', false)
            ->assertJsonPath('latitude', null);
        $this->post(route('driver.jobs.accept', $next))->assertRedirect();
    }

    public function test_driver_quote_expires_after_24_hours_and_acceptance_assigns_dispatch(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'is_driver' => true]);
        $client = User::factory()->create(['role' => 'client', 'is_client' => true]);
        $job = DispatchOrder::factory()->create([
            'client_id' => $client->id, 'driver_id' => null, 'status' => 'pending',
        ]);

        $this->freezeSecond();
        $this->actingAs($driver)
            ->post(route('driver.propose-price', $job), ['proposed_price' => 1200])
            ->assertRedirect();
        $quote = \App\Models\Proposal::sole();
        $this->assertEquals(now()->addHours(24), $quote->valid_until);

        $this->travel(24)->hours();
        $this->actingAs($client)->post(route('client.proposals.accept', $quote))->assertConflict();

        $this->actingAs($driver)
            ->post(route('driver.propose-price', $job), ['proposed_price' => 1300])
            ->assertRedirect();
        $quote = \App\Models\Proposal::latest('id')->firstOrFail();
        $this->actingAs($client)->post(route('client.proposals.accept', $quote))->assertRedirect();
        $this->assertEquals($driver->id, $job->fresh()->driver_id);
        $this->assertEquals(1300, $job->fresh()->base_price);
        $this->assertTrue($quote->fresh()->isAccepted());
        $this->assertDatabaseHas('invoices', ['order_type' => 'dispatch', 'order_id' => $job->id, 'subtotal' => 1300]);
    }
}
