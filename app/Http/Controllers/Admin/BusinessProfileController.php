<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Services\BusinessSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessProfileController extends Controller
{
    public function edit(BusinessSettings $settings)
    {
        return response()->view('admin.business-profile', [
            'details' => $settings->stored() + $settings->company(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function update(Request $request)
    {
        abort_if(config('kwdc.demo'), 403, 'Business profile editing is available in production.');

        $requiresRegistration = Rule::requiredIf(
            $request->input('registration_status') === 'registered' || $request->boolean('bank_enabled') || $request->boolean('qr_enabled')
        );
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:30',
            'email' => 'required|email|max:254',
            'registration_status' => ['required', Rule::in(['pending', 'registered'])],
            'registration_number' => [$requiresRegistration, 'nullable', 'string', 'max:80'],
            'pan' => [$requiresRegistration, 'nullable', 'string', 'max:40'],
            'bank_enabled' => 'required|boolean',
            'bank_name' => 'nullable|required_if:bank_enabled,1|string|max:120',
            'account_name' => 'nullable|required_if:bank_enabled,1|string|max:160',
            'account_number' => 'nullable|required_if:bank_enabled,1|string|max:80',
            'branch' => 'nullable|string|max:120',
            'qr_enabled' => 'required|boolean',
            'merchant_name' => 'nullable|required_if:qr_enabled,1|string|max:160',
            'qr_url' => 'nullable|required_if:qr_enabled,1|url:https|max:1000',
        ]);
        if (($data['bank_enabled'] || $data['qr_enabled']) && $data['registration_status'] !== 'registered') {
            return back()->withInput()->withErrors(['registration_status' => 'Record your completed business registration before enabling payments.']);
        }

        BusinessProfile::updateOrCreate(['id' => 1], ['details' => $data, 'updated_by' => $request->user()->id]);

        return back()->with('success', 'Business profile saved.');
    }
}
