@extends('emails.layout')

@section('content')
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #fee2e2; border: 1px solid #fecaca; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            ⚠️
        </div>
        <h2 style="color: #991b1b; margin: 0 0 6px; font-size: 22px;">Extended Storage Overdue Alert</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">Attention {{ $request->client->name ?? 'Valued Client' }}, urgent notice regarding your inventory storage.</p>
    </div>

    <div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <p style="color: #1f2937; font-size: 15px; margin: 0 0 12px;">
            Your stored goods have been in facility custody for <strong style="color: #dc2626;">{{ $daysOverdue }} days</strong> beyond the approved lease agreement period.
        </p>
        <p style="color: #4b5563; font-size: 14px; margin: 0; line-height: 1.5;">
            Please arrange immediate collection or contract extension. Under warehouse governance bylaws, items remaining uncollected beyond 60 days of overdue status may be scheduled for statutory liquidation.
        </p>
    </div>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ url('/client/requests') }}" style="display: inline-block; background: #dc2626; color: white; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;">Review Inventory Status →</a>
    </div>
</div>
@endsection