<?php

namespace App\Http\Controllers;

use App\Models\SecurityAgency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SecurityAgencyProfileController extends Controller
{
    public function edit()
    {
        $agency = Auth::user()->securityAgency;

        if (!$agency) {
            return redirect()->route('dashboard')->with('warning', 'Please complete your security agency profile first.');
        }

        return view('security.profile', compact('agency'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $agency = $user->securityAgency;

        if (!$agency) {
            return redirect()->route('dashboard')->with('error', 'Security agency profile not found.');
        }

        $validated = $request->validate([
            'agency_name' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'services_offered' => ['nullable', 'string', 'max:255'],
            'year_established' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'pan_vat_number' => ['nullable', 'string', 'max:100'],
            'certifications' => ['nullable', 'string', 'max:255'],
            'registration_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'license_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'pan_vat_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        if ($request->hasFile('registration_certificate')) {
            if ($agency->registration_certificate_path) {
                $this->deleteExistingDocument($agency->registration_certificate_path);
            }
            $validated['registration_certificate_path'] = $request->file('registration_certificate')->store('security-documents', 'private_uploads');
        }

        if ($request->hasFile('license_certificate')) {
            if ($agency->license_certificate_path) {
                $this->deleteExistingDocument($agency->license_certificate_path);
            }
            $validated['license_certificate_path'] = $request->file('license_certificate')->store('security-documents', 'private_uploads');
        }

        if ($request->hasFile('pan_vat_certificate')) {
            if ($agency->pan_vat_certificate_path) {
                $this->deleteExistingDocument($agency->pan_vat_certificate_path);
            }
            $validated['pan_vat_certificate_path'] = $request->file('pan_vat_certificate')->store('security-documents', 'private_uploads');
        }

        $agency->update($validated);

        return redirect()->route('security.profile')->with('success', 'Agency profile updated successfully.');
    }

    private function deleteExistingDocument(string $path): void
    {
        if (Storage::disk('private_uploads')->exists($path)) {
            Storage::disk('private_uploads')->delete($path);
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
