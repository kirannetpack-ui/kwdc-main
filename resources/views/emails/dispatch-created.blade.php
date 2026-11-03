@extends('emails.layout')

@section('content')
@php
    $recipient = $recipientName ?? $recipient_name ?? 'Valued Client';
    $orderNo = $dispatchNumber ?? ($dispatch->invoice_no ?? ($orderNumber ?? ('#ORD-' . ($dispatch->id ?? '1'))));
    $pickupLoc = $pickupAddress ?? ($dispatch->pickup_address ?? 'KTM Hub');
    $deliveryLoc = $deliveryAddress ?? ($dispatch->delivery_address ?? 'Kathmandu Valley');
    $dist = $distance ?? ($dispatch->total_distance ?? null);
    $cost = $amount ?? ($dispatch->total_price ?? ($dispatch->grand_total ?? null));
    $trackLink = $trackingUrl ?? ($tracking_url ?? url('/client/dispatches'));
    $stopsList = $dispatch->stops ?? [];
@endphp
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            🚚
        </div>
        <h2 style="color: #1f2937; margin: 0 0 6px; font-size: 22px;">Dispatch Order Created</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">Namaste {{ $recipient }}, your dispatch order has been received and is being processed.</p>
    </div>

    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Dispatch / Order ID:</td>
                <td style="padding: 7px 0; color: #1f2937; font-weight: 700; text-align: right; font-family: monospace;">{{ $orderNo }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Pickup Origin:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $pickupLoc }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Primary Destination:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $deliveryLoc }}</td>
            </tr>
            @if(!empty($dist))
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Total Estimated Distance:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ number_format((float)$dist, 2) }} km</td>
            </tr>
            @endif
            @if(!empty($cost))
            <tr style="border-top: 1px dashed #d1c7b7;">
                <td style="padding: 10px 0 4px; font-size: 15px; font-weight: 700; color: #1f2937;">Estimated Freight Cost:</td>
                <td style="padding: 10px 0 4px; font-size: 16px; font-weight: 700; color: #d96b43; text-align: right;">NPR {{ number_format((float)$cost, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if(count($stopsList) > 0)
    <div style="margin-bottom: 24px;">
        <h3 style="color: #1f2937; margin: 0 0 10px; font-size: 15px; font-weight: 600;">Delivery Waypoints</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <tr style="background: #f3f4f6; color: #4b5563; text-align: left;">
                <th style="padding: 6px 10px;">#</th>
                <th style="padding: 6px 10px;">Stop Address</th>
                <th style="padding: 6px 10px;">Recipient</th>
            </tr>
            @foreach($stopsList as $idx => $stop)
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 6px 10px; font-weight: 600;">{{ $idx + 1 }}</td>
                <td style="padding: 6px 10px;">{{ $stop->address ?? 'N/A' }}</td>
                <td style="padding: 6px 10px;">{{ $stop->recipient_name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ $trackLink }}" style="display: inline-block; background: #d96b43; color: white; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;">Track Consignment Live →</a>
    </div>
</div>
@endsection