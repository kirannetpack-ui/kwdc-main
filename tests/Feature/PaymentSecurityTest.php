<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AdminEmailService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_initialize_payment_for_another_users_invoice(): void
    {
        $owner = User::factory()->create(['role' => 'client']);
        $otherUser = User::factory()->create(['role' => 'client']);
        $invoice = $this->invoiceFor($owner, 1500);

        $this->actingAs($otherUser)
            ->postJson(route('payment.khalti.init'), [
                'invoice_id' => $invoice->id,
                'amount' => 1500,
            ])
            ->assertNotFound();
    }

    public function test_payment_initialization_rejects_tampered_amounts(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $invoice = $this->invoiceFor($user, 1500);

        $this->actingAs($user)
            ->postJson(route('payment.khalti.init'), [
                'invoice_id' => $invoice->id,
                'amount' => 1,
            ])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Payment amount does not match the invoice total.',
            ]);

        $this->assertDatabaseMissing('transactions', [
            'invoice_id' => $invoice->id,
        ]);
    }

    public function test_esewa_success_callback_does_not_mark_invoice_paid_without_provider_verification(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $invoice = $this->invoiceFor($user, 1500);

        Transaction::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'amount' => 1500,
            'payment_method' => 'esewa',
            'transaction_id' => 'pid-123',
            'status' => 'pending',
        ]);

        $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('verifyEsewaPayment')
                ->once()
                ->with('pid-123', 'ref-456', 1500.0)
                ->andReturn([
                    'success' => false,
                    'message' => 'Provider rejected the transaction.',
                ]);
        });

        $this->actingAs($user)
            ->get(route('payment.esewa.success', [
                'pid' => 'pid-123',
                'refId' => 'ref-456',
            ]))
            ->assertRedirect(route('payment.failure', absolute: false));

        $this->assertSame('unpaid', $invoice->fresh()->payment_status);
        $this->assertDatabaseHas('transactions', [
            'invoice_id' => $invoice->id,
            'transaction_id' => 'pid-123',
            'status' => 'failed',
        ]);
    }

    public function test_khalti_verify_fails_when_local_transaction_is_missing(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('verifyKhaltiPayment')->never();
        });

        $this->actingAs($user)
            ->get(route('payment.khalti.verify', ['pidx' => 'unknown-pidx']))
            ->assertRedirect(route('payment.failure', absolute: false));
    }

    public function test_completed_khalti_callback_is_idempotent_without_resending_notifications(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'client']);
        $invoice = $this->invoiceFor($user, 1500);
        $invoice->markAsPaid('khalti');

        Transaction::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'amount' => 1500,
            'payment_method' => 'khalti',
            'transaction_id' => 'completed-pidx',
            'status' => 'completed',
            'payment_date' => now(),
        ]);

        $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('verifyKhaltiPayment')->never();
        });

        $this->mock(AdminEmailService::class, function ($mock) {
            $mock->shouldReceive('notifyPaymentReceived')->never();
        });

        $this->actingAs($user)
            ->get(route('payment.khalti.verify', ['pidx' => 'completed-pidx']))
            ->assertRedirect(route('payment.success', absolute: false));

        Mail::assertNothingSent();
    }

    public function test_esewa_success_callback_requires_provider_reference(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $invoice = $this->invoiceFor($user, 1500);

        Transaction::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'amount' => 1500,
            'payment_method' => 'esewa',
            'transaction_id' => 'pid-without-ref',
            'status' => 'pending',
        ]);

        $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('verifyEsewaPayment')->never();
        });

        $this->actingAs($user)
            ->get(route('payment.esewa.success', ['pid' => 'pid-without-ref']))
            ->assertRedirect(route('payment.failure', absolute: false));

        $this->assertSame('unpaid', $invoice->fresh()->payment_status);
    }

    public function test_payment_provider_routes_are_rate_limited(): void
    {
        foreach ([
            'payment.khalti.init',
            'payment.khalti.verify',
            'payment.esewa.init',
            'payment.esewa.success',
            'payment.esewa.failure',
        ] as $routeName) {
            $middleware = Route::getRoutes()->getByName($routeName)->gatherMiddleware();

            $this->assertNotEmpty(
                preg_grep('/^throttle:/', $middleware),
                "Expected {$routeName} to include throttle middleware."
            );
        }
    }

    public function test_new_khalti_payment_session_retires_previous_pending_session(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $invoice = $this->invoiceFor($user, 1500);

        $oldTransaction = Transaction::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'amount' => 1500,
            'payment_method' => 'khalti',
            'transaction_id' => 'old-pidx',
            'status' => 'pending',
        ]);

        $this->mock(PaymentService::class, function ($mock) use ($invoice) {
            $mock->shouldReceive('initiateKhaltiPayment')
                ->once()
                ->with(1500.0, $invoice->invoice_number, $invoice->user_id, $invoice->id)
                ->andReturn([
                    'success' => true,
                    'data' => [
                        'pidx' => 'new-pidx',
                        'payment_url' => 'https://payments.example/new-pidx',
                    ],
                ]);
        });

        $this->actingAs($user)
            ->postJson(route('payment.khalti.init'), [
                'invoice_id' => $invoice->id,
                'amount' => 1500,
            ])
            ->assertOk()
            ->assertJsonPath('pidx', 'new-pidx');

        $this->assertDatabaseHas('transactions', [
            'id' => $oldTransaction->id,
            'status' => 'failed',
            'notes' => 'Retired after a newer payment session was created.',
        ]);

        $this->assertDatabaseHas('transactions', [
            'invoice_id' => $invoice->id,
            'transaction_id' => 'new-pidx',
            'status' => 'pending',
        ]);
    }

    public function test_payment_service_does_not_call_khalti_without_secret_key(): void
    {
        Http::fake();
        Config::set('payment.khalti.secret_key', '');

        $user = User::factory()->create(['role' => 'client']);

        $this->actingAs($user);

        $result = app(PaymentService::class)->initiateKhaltiPayment(1500, 'INV-MISSING-KEY', $user->id, 123);

        $this->assertFalse($result['success']);
        $this->assertSame('Payment provider is not configured. Please contact support.', $result['message']);
        Http::assertNothingSent();
    }

    public function test_khalti_initiation_requires_complete_provider_response_before_creating_transaction(): void
    {
        Http::fake([
            'a.khalti.com/*' => Http::response([
                'pidx' => 'missing-payment-url',
            ], 200),
        ]);

        Config::set('payment.khalti.secret_key', 'test-secret');

        $user = User::factory()->create(['role' => 'client']);
        $invoice = $this->invoiceFor($user, 1500);

        $this->actingAs($user)
            ->postJson(route('payment.khalti.init'), [
                'invoice_id' => $invoice->id,
                'amount' => 1500,
            ])
            ->assertStatus(500)
            ->assertJsonPath('success', false);

        $this->assertDatabaseMissing('transactions', [
            'invoice_id' => $invoice->id,
            'transaction_id' => 'missing-payment-url',
        ]);
    }

    public function test_khalti_initiation_sends_integer_paisa_amount(): void
    {
        Http::fake([
            'a.khalti.com/*' => Http::response([
                'pidx' => 'pidx-amount-check',
                'payment_url' => 'https://payments.example/pidx-amount-check',
            ], 200),
        ]);

        Config::set('payment.khalti.secret_key', 'test-secret');

        $user = User::factory()->create(['role' => 'client']);
        $this->actingAs($user);

        $result = app(PaymentService::class)->initiateKhaltiPayment(1500.75, 'INV-AMOUNT', $user->id, 123);

        $this->assertTrue($result['success']);
        Http::assertSent(fn ($request) => $request['amount'] === 150075);
    }

    public function test_esewa_payment_session_uses_uuid_transaction_id(): void
    {
        Config::set('payment.esewa.merchant_code', 'merchant-code');

        $result = app(PaymentService::class)->initiateEsewaPayment(1500, 'INV-ESEWA', 123);

        $this->assertTrue($result['success']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $result['pid']
        );
    }

    public function test_payment_service_does_not_call_esewa_without_merchant_code(): void
    {
        Http::fake();
        Config::set('payment.esewa.merchant_code', '');

        $result = app(PaymentService::class)->verifyEsewaPayment('pid-123', 'ref-456', 1500);

        $this->assertFalse($result['success']);
        $this->assertSame('Payment provider is not configured. Please contact support.', $result['message']);
        Http::assertNothingSent();
    }

    public function test_stripe_payment_initialization_creates_pending_transaction(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $invoice = $this->invoiceFor($user, 2500);

        $this->mock(PaymentService::class, function ($mock) use ($invoice, $user) {
            $mock->shouldReceive('initiateStripePayment')
                ->once()
                ->with(2500.0, $invoice->invoice_number, $user->id, $invoice->id)
                ->andReturn([
                    'success' => true,
                    'session_id' => 'cs_test_session_123',
                    'payment_url' => 'https://checkout.stripe.com/c/pay/cs_test_session_123',
                    'data' => ['id' => 'cs_test_session_123'],
                ]);
        });

        $response = $this->actingAs($user)
            ->postJson(route('payment.stripe.init'), [
                'invoice_id' => $invoice->id,
                'amount' => 2500,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'session_id' => 'cs_test_session_123',
                'payment_url' => 'https://checkout.stripe.com/c/pay/cs_test_session_123',
            ]);

        $this->assertDatabaseHas('transactions', [
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'payment_method' => 'stripe',
            'transaction_id' => 'cs_test_session_123',
            'status' => 'pending',
        ]);
    }

    public function test_stripe_success_callback_verifies_session_and_marks_invoice_paid(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'client']);
        $invoice = $this->invoiceFor($user, 2500);

        $transaction = Transaction::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'amount' => 2500,
            'payment_method' => 'stripe',
            'transaction_id' => 'cs_test_session_456',
            'status' => 'pending',
        ]);

        $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('verifyStripePayment')
                ->once()
                ->with('cs_test_session_456')
                ->andReturn([
                    'success' => true,
                    'status' => 'Completed',
                    'data' => [
                        'id' => 'cs_test_session_456',
                        'payment_status' => 'paid',
                    ],
                ]);
        });

        $this->actingAs($user)
            ->get(route('payment.stripe.success', ['session_id' => 'cs_test_session_456']))
            ->assertRedirect(route('payment.success', absolute: false));

        $this->assertSame('paid', $invoice->fresh()->payment_status);
        $this->assertSame('completed', $transaction->fresh()->status);
    }

    public function test_stripe_cancel_redirects_to_failure(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $this->actingAs($user)
            ->get(route('payment.stripe.cancel'))
            ->assertRedirect(route('payment.failure', absolute: false));
    }

    private function invoiceFor(User $user, float $amount): Invoice
    {
        $warehouse = Warehouse::factory()->create([
            'user_id' => $user->id,
            'location' => 'Kathmandu',
        ]);

        $warehouseRequestId = DB::table('warehouse_requests')->insertGetId([
            'client_id' => $user->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $invoiceId = DB::table('invoices')->insertGetId([
            'user_id' => $user->id,
            'client_id' => $user->id,
            'warehouse_request_id' => $warehouseRequestId,
            'invoice_number' => 'INV-TEST-' . $user->id . '-' . random_int(1000, 9999),
            'amount' => $amount,
            'subtotal' => $amount,
            'grand_total' => $amount,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'due_date' => now()->addDays(7)->toDateString(),
            'payment_due_date' => now()->addDays(7)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Invoice::findOrFail($invoiceId);
    }
}
