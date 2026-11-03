@extends('emails.layout')

@section('content')
@php
    $ownerName = $warehouse->owner->name ?? $warehouse->user->name ?? $warehouse->owner_name ?? 'Property Partner';
@endphp
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #fee2e2; border: 1px solid #fecaca; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            ℹ️
        </div>
        <h2 style="color: #1f2937; margin: 0 0 6px; font-size: 22px;">Warehouse Application Update</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">Namaste {{ $ownerName }}, an update regarding your listing submission.</p>
    </div>

    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <p style="color: #1f2937; font-size: 15px; margin: 0 0 12px;">
            We regret to inform you that your warehouse <strong>{{ $warehouse->name ?? 'Facility' }}</strong> could not be approved for the KTM-WDC network at this time.
        </p>
        <p style="color: #4b5563; font-size: 14px; margin: 0;">
            This may be due to missing safety documentation, access roadway constraints, or municipal permits. Our compliance team is available to assist you in resolving these requirements.
        </p>
    </div>

    <div style="text-align: center; margin-top: 24px;">
        <a href="mailto:support@ktm-wdc.com" style="display: inline-block; background: #d96b43; color: white; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;">Contact Support Team →</a>
    </div>
</div>
@endsection