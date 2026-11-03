<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ActivationCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReleaseReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_users_cannot_bypass_activation_by_opening_operations(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'client', 'is_active' => true]);
        foreach (['/dashboard', '/pickup', '/dispatch', '/profile', '/notifications'] as $path) {
            $this->actingAs($user)->get($path)
                ->assertRedirect(route('activation.notice', ['email' => $user->email], false));
        }
        $this->actingAs($user)->getJson('/notifications/unread-count')->assertForbidden();
    }

    public function test_disabled_account_cannot_use_an_existing_session_or_activation_code(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'client', 'is_active' => false]);
        $code = app(ActivationCodeService::class)->generateFor($user);
        $this->actingAs($user)->get('/pickup')->assertForbidden();
        $this->post('/activate', ['email' => $user->email, 'activation_code' => $code])
            ->assertSessionHasErrors('activation_code');
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_failed_resend_preserves_existing_code_and_reports_unavailability(): void
    {
        $user = User::factory()->unverified()->create(['is_active' => true]);
        $service = app(ActivationCodeService::class);
        $code = $service->generateFor($user);
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Provider unavailable'));
        $this->from('/activate')->post('/activate/resend', ['email' => $user->email])
            ->assertRedirect('/activate')->assertSessionHas('status', 'Email delivery is temporarily unavailable. Please try sending a new code shortly.')
            ->assertSessionMissing('activation_demo_code');
        $this->assertTrue($service->verify($user->fresh(), $code));
    }

    public function test_registration_remains_recoverable_when_activation_mail_fails(): void
    {
        config(['kwdc.demo' => false]);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Provider unavailable'));
        Mail::shouldReceive('send')->andReturnNull();
        $this->post('/register', [
            'name' => 'Release Test', 'email' => 'release@example.com', 'role' => 'client',
            'password' => 'test-password-123', 'password_confirmation' => 'test-password-123',
        ])->assertRedirect(route('activation.notice', ['email' => 'release@example.com'], false))
            ->assertSessionHas('status', 'Email delivery is temporarily unavailable. Please try sending a new code shortly.');
        $this->assertDatabaseHas('users', ['email' => 'release@example.com', 'email_verified_at' => null]);
        $this->get('/dashboard')->assertRedirect();
    }

    public function test_missing_registration_role_returns_validation_error(): void
    {
        $this->post('/register', ['name' => 'Test', 'email' => 'test@example.com'])
            ->assertSessionHasErrors('role');
    }

    public function test_successful_activation_code_cannot_be_replayed(): void
    {
        $user = User::factory()->unverified()->create(['is_active' => true]);
        $code = app(ActivationCodeService::class)->generateFor($user);
        $payload = ['email' => $user->email, 'activation_code' => $code];
        $this->post('/activate', $payload)->assertRedirect('/dashboard');
        $this->post('/activate', $payload)->assertSessionHasErrors('activation_code');
    }
}
