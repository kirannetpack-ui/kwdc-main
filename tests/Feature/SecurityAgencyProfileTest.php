<?php

namespace Tests\Feature;

use App\Models\SecurityAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAgencyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_agency_is_redirected_to_agency_profile_from_generic_profile_route(): void
    {
        $user = User::factory()->create([
            'role' => 'security_agency',
            'email' => 'agency-route@example.com',
        ]);

        SecurityAgency::create([
            'user_id' => $user->id,
            'agency_name' => 'Route Agency',
            'registration_number' => 'ROUTE-001',
            'address' => 'Kathmandu',
            'phone' => '9800000000',
            'email' => $user->email,
            'services_offered' => 'Guarding',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertRedirect('/security/profile');
    }

    public function test_admin_can_review_agency_documents_and_approve_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agencyUser = User::factory()->create(['role' => 'security_agency', 'email' => 'agency-review@example.com']);

        $agency = SecurityAgency::create([
            'user_id' => $agencyUser->id,
            'agency_name' => 'Review Agency',
            'registration_number' => 'REV-007',
            'license_number' => 'LIC-REV',
            'address' => 'Pokhara',
            'phone' => '9851000000',
            'email' => 'agency-review@example.com',
            'services_offered' => 'Event security',
            'status' => 'pending',
            'registration_certificate_path' => 'security-documents/registration.pdf',
            'license_certificate_path' => 'security-documents/license.pdf',
            'pan_vat_certificate_path' => 'security-documents/pan.pdf',
        ]);

        $response = $this->actingAs($admin)->get('/admin/security/agencies/' . $agency->id);

        $response->assertOk();
        $response->assertSee('Review Agency');
        $response->assertSee('Registration Certificate');
        $response->assertSee('License Certificate');
        $response->assertSee('PAN / VAT Certificate');
    }

    public function test_admin_can_view_security_agencies_monitoring_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        SecurityAgency::create([
            'user_id' => User::factory()->create(['role' => 'security_agency', 'email' => 'agency-monitor@example.com'])->id,
            'agency_name' => 'Monitoring Agency',
            'registration_number' => 'MON-100',
            'address' => 'Lalitpur',
            'phone' => '9851000100',
            'email' => 'agency-monitor@example.com',
            'services_offered' => 'Patrols',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/security/agencies');

        $response->assertOk();
        $response->assertSee('Security Agencies');
        $response->assertSee('Monitoring Agency');
    }

    public function test_security_agency_can_update_profile_and_upload_required_documents(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'security_agency',
            'email' => 'agency-profile@example.com',
        ]);

        $agency = SecurityAgency::create([
            'user_id' => $user->id,
            'agency_name' => 'Old Agency Name',
            'registration_number' => 'REG-001',
            'license_number' => 'LIC-001',
            'address' => 'Old Address',
            'phone' => '9800000000',
            'email' => $user->email,
            'services_offered' => 'Guarding',
            'year_established' => '2018',
            'pan_vat_number' => 'PAN-001',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post('/security/profile', [
            'agency_name' => 'Everest Security Solutions',
            'registration_number' => 'REG-2026-001',
            'license_number' => 'SEC-LIC-2026-001',
            'address' => 'Boudha, Kathmandu',
            'phone' => '9851123456',
            'emergency_phone' => '9801123456',
            'email' => 'info@everestsecurity.com.np',
            'services_offered' => 'CCTV monitoring, manned guarding, access control',
            'year_established' => '2015',
            'pan_vat_number' => '602123456',
            'certifications' => 'ISO 9001, Nepal Police Approved',
            'registration_certificate' => UploadedFile::fake()->create('registration.pdf', 100, 'application/pdf'),
            'license_certificate' => UploadedFile::fake()->image('license.jpg', 600, 400),
            'pan_vat_certificate' => UploadedFile::fake()->create('pan.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect('/security/profile');
        $agency->refresh();

        $this->assertEquals('Everest Security Solutions', $agency->agency_name);
        $this->assertNotNull($agency->registration_certificate_path);
        $this->assertNotNull($agency->license_certificate_path);
        $this->assertNotNull($agency->pan_vat_certificate_path);
        Storage::disk('private_uploads')->assertExists($agency->registration_certificate_path);
        Storage::disk('private_uploads')->assertExists($agency->license_certificate_path);
    }
}
