<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\MarginTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_equipment_update_ignores_protected_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'equipment_owner']);
        $attackerOwner = User::factory()->create(['role' => 'equipment_owner']);

        $equipment = Equipment::create([
            'user_id' => $owner->id,
            'owner_id' => $owner->id,
            'name' => 'Original Crane',
            'type' => 'crane',
            'daily_rate' => 1000,
            'status' => 'available',
            'registration_doc' => 'equipment/documents/original.pdf',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.equipment.update', $equipment->id), [
                'name' => 'Updated Crane',
                'type' => 'crane',
                'daily_rate' => 1200,
                'status' => 'maintenance',
                'user_id' => $attackerOwner->id,
                'owner_id' => $attackerOwner->id,
                'registration_doc' => 'equipment/documents/tampered.pdf',
            ])
            ->assertRedirect(route('admin.equipment-list'));

        $equipment->refresh();

        $this->assertSame('Updated Crane', $equipment->name);
        $this->assertSame('maintenance', $equipment->status);
        $this->assertEquals(1200, $equipment->daily_rate);
        $this->assertSame($owner->id, $equipment->user_id);
        $this->assertSame($owner->id, $equipment->owner_id);
        $this->assertSame('equipment/documents/original.pdf', $equipment->registration_doc);
    }

    public function test_admin_driver_update_cannot_change_role_or_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $driver = User::factory()->create([
            'role' => 'driver',
            'password' => Hash::make('original-password'),
            'is_admin' => false,
        ]);

        $originalPassword = $driver->password;

        $this->actingAs($admin)
            ->put(route('admin.drivers.update', $driver->id), [
                'name' => 'Updated Driver',
                'email' => $driver->email,
                'phone' => '9800000014',
                'address' => 'Kathmandu',
                'avg_rating' => 4.5,
                'role' => 'admin',
                'is_admin' => true,
                'password' => 'new-password',
            ])
            ->assertRedirect(route('admin.drivers'));

        $driver->refresh();

        $this->assertSame('Updated Driver', $driver->name);
        $this->assertSame('9800000014', $driver->phone);
        $this->assertSame('driver', $driver->role);
        $this->assertFalse((bool) $driver->is_admin);
        $this->assertSame($originalPassword, $driver->password);
    }

    public function test_admin_margin_tier_update_uses_validated_fields_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $tier = MarginTier::create([
            'name' => 'Original Tier',
            'service_type' => 'dispatch',
            'margin_type' => 'percentage',
            'margin_value' => 10,
            'min_distance' => 0,
            'max_distance' => 50,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.margin-tiers.update', $tier->id), [
                'name' => 'Updated Tier',
                'service_type' => 'pickup',
                'margin_type' => 'flat',
                'margin_value' => 25,
                'min_distance' => 5,
                'max_distance' => 100,
                'is_active' => false,
                'created_at' => now()->subYear()->toDateTimeString(),
                'updated_at' => now()->subYear()->toDateTimeString(),
            ])
            ->assertRedirect(route('admin.margin-tiers'));

        $tier->refresh();

        $this->assertSame('Updated Tier', $tier->name);
        $this->assertSame('pickup', $tier->service_type);
        $this->assertSame('flat', $tier->margin_type);
        $this->assertEquals(25, $tier->margin_value);
        $this->assertFalse((bool) $tier->is_active);
        $this->assertTrue($tier->created_at->isToday());
    }
}
