<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\User;
use App\Services\BusinessSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_save_a_pending_profile_and_enable_registered_bank_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $client = User::factory()->create(['role' => 'client']);
        $draft = [
            'name' => 'KWDC',
            'address' => 'Kathmandu, Nepal',
            'email' => 'kiran.kwdc@gmail.com',
            'registration_status' => 'pending',
            'bank_enabled' => '0',
            'qr_enabled' => '0',
        ];

        $this->actingAs($client)->get(route('admin.business-profile.edit'))->assertForbidden();
        $this->put(route('admin.business-profile.update'), $draft)->assertForbidden();
        $this->actingAs($admin)->get(route('admin.business-profile.edit'))->assertOk()->assertSee('Registration pending');
        $this->put(route('admin.business-profile.update'), $draft)->assertRedirect();
        $this->assertSame($admin->id, BusinessProfile::sole()->updated_by);
        $this->assertStringNotContainsString($draft['email'], BusinessProfile::sole()->getRawOriginal('details'));
        $this->assertSame([], (new BusinessSettings)->banking()['accounts']);

        $invalid = array_replace($draft, ['bank_enabled' => '1']);
        $this->put(route('admin.business-profile.update'), $invalid)->assertSessionHasErrors(['registration_number', 'pan', 'account_number']);

        $registered = array_replace($invalid, [
            'registration_status' => 'registered', 'registration_number' => 'KWDC-001', 'pan' => '001234567',
            'bank_name' => 'Test Bank', 'account_name' => 'KWDC', 'account_number' => '00012345',
        ]);
        $this->put(route('admin.business-profile.update'), $registered)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('00012345', (new BusinessSettings)->banking()['accounts'][0]['account_number']);
        $this->assertSame('001234567', (new BusinessSettings)->company()['pan']);
    }
}
