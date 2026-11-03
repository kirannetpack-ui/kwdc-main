<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_seeded_roles_can_open_dashboards_and_demo_invoices(): void
    {
        config(['kwdc.demo' => true]);
        $this->seed(\Database\Seeders\NepaliDemoSeeder::class);

        foreach (['admin', 'client', 'driver', 'property', 'equipment', 'security'] as $role) {
            $user = \App\Models\User::where('email', $role.'.access@kwdc.test')->firstOrFail();
            $this->actingAs($user)->get('/dashboard')->assertOk();
        }
        $this->actingAs(\App\Models\User::where('email', 'admin.access@kwdc.test')->firstOrFail())
            ->get('/admin/invoices')->assertOk();

        $this->actingAs(\App\Models\User::where('email', 'client.access@kwdc.test')->firstOrFail())
            ->get('/payment')->assertOk()->assertSee('does not accept money')->assertDontSee('01100175023910');
    }

    public function test_demo_blocks_external_payments_registration_and_file_uploads(): void
    {
        config(['kwdc.demo' => true]);
        Http::preventStrayRequests();

        $this->postJson('/payment/khalti/init', ['invoice_id' => 1])->assertStatus(409);
        $this->get('/payment/esewa/success')->assertStatus(409);
        $this->post('/register', [])->assertStatus(409);
        $this->post('/profile', ['document' => UploadedFile::fake()->create('private.pdf', 10)])->assertStatus(409);
        Http::assertNothingSent();
    }

    public function test_demo_login_explains_sample_access(): void
    {
        config(['kwdc.demo' => true]);

        $this->get('/login')->assertOk()->assertSee('client.access@kwdc.test')->assertSee('DEMO');
    }
}
