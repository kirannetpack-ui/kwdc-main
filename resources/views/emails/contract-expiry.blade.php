@extends('emails.layout')

@section('content')
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #fef3c7; border: 1px solid #fde68a; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            ⏳
        </div>
        <h2 style="color: #1f2937; margin: 0 0 6px; font-size: 22px;">Warehouse Contract Expiry Reminder</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">Namaste {{ $request->client->name ?? 'Valued Partner' }}, your storage lease agreement is approaching expiration.</p>
    </div>

    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <p style="color: #1f2937; font-size: 15px; margin: 0 0 12px;">
            Your warehouse reservation expires in <strong style="color: #d97706;">{{ $daysLeft }} days</strong> 
            (on {{ isset($request->contract_end_date) ? \Carbon\Carbon::parse($request->contract_end_date)->format('d M Y') : date('d M Y') }}).
        </p>
        <p style="color: #4b5563; font-size: 14px; margin: 0;">
            Please contact our logistics coordination desk to renew your contract or make scheduled arrangements for goods collection.
        </p>
    </div>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ url('/client/requests') }}" style="display: inline-block; background: #d96b43; color: white; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;">Manage Warehouse Contract →</a>
    </div>
</div>
@endsection