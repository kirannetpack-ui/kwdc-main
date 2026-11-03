<?php

namespace Tests\Feature;

use App\Mail\UserReminderDueMail;
use App\Models\User;
use App\Models\UserReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReminderCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_create_reminders(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);

        $response = $this->actingAs($user)->post(route('reminders.store'), [
            'title' => 'Call warehouse owner',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'remind_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'notes' => 'Confirm loading dock availability.',
        ]);

        $response->assertRedirect(route('reminders.index', absolute: false));
        $this->assertDatabaseHas('user_reminders', [
            'user_id' => $user->id,
            'title' => 'Call warehouse owner',
        ]);
    }

    public function test_users_cannot_delete_another_users_reminder(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $other = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $reminder = UserReminder::create([
            'user_id' => $owner->id,
            'title' => 'Private reminder',
            'starts_at' => now()->addDay(),
            'remind_at' => now(),
        ]);

        $this->actingAs($other)
            ->delete(route('reminders.destroy', $reminder))
            ->assertForbidden();
    }

    public function test_due_reminder_email_is_sent_once(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $reminder = UserReminder::create([
            'user_id' => $user->id,
            'title' => 'Pay invoice',
            'starts_at' => now()->addHour(),
            'remind_at' => now()->subMinute(),
        ]);

        $this->artisan('reminders:send-due-emails')->assertSuccessful();
        $this->artisan('reminders:send-due-emails')->assertSuccessful();

        Mail::assertSent(UserReminderDueMail::class, 1);
        $this->assertNotNull($reminder->fresh()->emailed_at);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'reminder_due',
        ]);
    }
}
