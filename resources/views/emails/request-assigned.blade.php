@extends('emails.layout')

@section('content')
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            🏢
        </div>
        <h2 style="color: #1f2937; margin: 0 0 6px; font-size: 22px;">Warehouse Space Assigned!</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">Namaste {{ $request->client->name ?? 'Valued Client' }}, your storage request has been fulfilled.</p>
    </div>

    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <h3 style="color: #1f2937; margin: 0 0 12px; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Assignment Details</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Request ID:</td>
                <td style="padding: 7px 0; color: #1f2937; font-weight: 600; text-align: right;">#{{ $request->id ?? 1 }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Allocated Warehouse:</td>
                <td style="padding: 7px 0; color: #1f2937; font-weight: 700; text-align: right;">{{ $request->assignedWarehouse->name ?? $request->warehouse->name ?? 'KTM Central Hub' }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Location:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $request->assignedWarehouse->address ?? $request->assignedWarehouse->location ?? $request->warehouse->location ?? 'Kathmandu Valley' }}</td>
            </tr>
        </table>
    </div>

    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px;">
        <p style="margin: 0; color: #166534; font-size: 13px; line-height: 1.5;">
            <strong>✓ You're ready to store & dispatch</strong><br>
            You can now view stock positions, schedule in-bound cargo shipments, and issue outbound dispatch orders from your client portal.
        </p>
    </div>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ url('/client/requests') }}" style="display: inline-block; background: #d96b43; color: white; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;">Open Storage Dashboard →</a>
    </div>
</div>
@endsection