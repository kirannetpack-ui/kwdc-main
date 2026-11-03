@extends('emails.layout')

@section('content')
<div class="animate-fade">
    <div style="margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin: 0 0 10px;">🙏 Namaste {{ $recipientName }}!</h2>
        <p style="color: #4b5563; font-size: 16px; margin: 0;">A new pickup request has been created.</p>
    </div>
    
    <div style="text-align: center; margin: 20px 0;">
        <div class="animate-pulse" style="display: inline-block; background: #dbeafe; border-radius: 50%; padding: 20px;">
            <span style="font-size: 50px;">📦</span>
        </div>
    </div>
    
    <div style="background: #f9fafb; border-radius: 12px; padding: 20px; margin: 20px 0;">
        <h3 style="color: #1f2937; margin: 0 0 15px; font-size: 18px;">📋 Pickup Request Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Request Number:</td>
                <td style="padding: 8px 0; color: #1f2937; font-weight: bold;">{{ $pickupNumber }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Destination:</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $destinationAddress }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Pickup Stops:</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $stopCount }} locations</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Total Boxes:</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $totalBoxes }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Total Distance:</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $distance }} km</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Total Amount:</td>
                <td style="padding: 8px 0; color: #059669; font-weight: bold;">रु {{ number_format($amount) }}</td>
            </tr>
        </table>
    </div>
    
    <div style="text-align: center; margin-top: 25px;">
        <a href="{{ $trackingUrl }}" style="display: inline-block; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 600;">View Request →</a>
    </div>
</div>
@endsection