<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ProfessionalResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_legacy_password_reset_urls_are_not_registered(): void
    {
        $uris = collect(Route::getRoutes())->map(fn ($route) => $route->uri())->all();

        $this->assertNotContains('password/reset', $uris);
        $this->assertNotContains('password/email', $uris);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ProfessionalResetPasswordNotification::class);
    }

    public function test_reset_password_email_has_professional_security_details(): void
    {
        $user = User::factory()->create([
            'name' => 'Kiran',
            'email' => 'kiran@example.com',
        ]);

        $notification = new ProfessionalResetPasswordNotification('reset-token');
        $mail = $notification->toMail($user);
        $html = view($mail->view, $mail->viewData)->render();

        $this->assertSame('Reset your KTM-WDC password', $mail->subject);
        $this->assertStringContainsString('Account security', $html);
        $this->assertStringContainsString('Hello Kiran', $html);
        $this->assertStringContainsString('Reset password', $html);
        $this->assertStringContainsString('This password reset link expires in 60 minutes.', $html);
        $this->assertStringContainsString('KTM-WDC will never ask you to share your password, reset link, verification code, or payment details', $html);
        $this->assertStringContainsString('reset-token', $html);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ProfessionalResetPasswordNotification::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ProfessionalResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_password_reset_submission_routes_are_rate_limited(): void
    {
        $this->assertNotEmpty(
            preg_grep('/^throttle:/', app('router')->getRoutes()->getByName('password.email')->gatherMiddleware())
        );

        $this->assertNotEmpty(
            preg_grep('/^throttle:/', app('router')->getRoutes()->getByName('password.store')->gatherMiddleware())
        );
    }
}
