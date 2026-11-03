@extends('emails.layout')

@section('content')
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #fef3c7; border: 1px solid #fde68a; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            🏗️
        </div>
        <h2 style="color: #1f2937; margin: 0 0 6px; font-size: 22px;">Warehouse Registration Received</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">Namaste {{ $warehouse->user->name ?? $warehouse->owner_name ?? 'Property Owner' }}, your warehouse listing is now under review.</p>
    </div>

    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <h3 style="color: #1f2937; margin: 0 0 12px; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Submitted Facility Details</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Facility Name:</td>
                <td style="padding: 7px 0; color: #1f2937; font-weight: 600; text-align: right;">{{ $warehouse->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Location / Address:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $warehouse->location ?? $warehouse->address ?? 'Kathmandu Valley' }}</td>
            </tr>
            @if(isset($warehouse->capacity) || isset($warehouse->total_area))
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Capacity / Area:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $warehouse->capacity ?? $warehouse->total_area }} sq. ft.</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Current Status:</td>
                <td style="padding: 7px 0; text-align: right;"><span style="display: inline-block; background: #fef3c7; color: #b45309; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">Pending Verification</span></td>
            </tr>
        </table>
    </div>

    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px;">
        <p style="margin: 0; color: #1e40af; font-size: 13px; line-height: 1.5;">
            <strong>What happens next?</strong><br>
            Our logistics inspection team reviews property documents and safety compliance within 24–48 hours. Once verified, your facility will go live on the KTM-WDC marketplace.
        </p>
    </div>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ url('/property/warehouses') }}" style="display: inline-block; background: #d96b43; color: white; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;">View Property Portal →</a>
    </div>
</div>
@endsection
