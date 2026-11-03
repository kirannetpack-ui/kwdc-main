@extends('emails.layout')

@section('content')
@php
    $clientName = $order->client->name ?? $order->warehouseRequest->client->name ?? 'Valued Partner';
    $statusColor = match($order->status ?? 'pending') {
        'delivered', 'completed' => '#059669',
        'picked_up', 'in_transit', 'on_the_way' => '#d96b43',
        'assigned' => '#2563eb',
        default => '#d97706',
    };
@endphp
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            🚚
        </div>
        <h2 style="color: #1f2937; margin: 0 0 6px; font-size: 22px;">Dispatch Status Updated</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">Namaste {{ $clientName }}, here is the latest update on your dispatch consignment.</p>
    </div>

    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Tracking / Order ID:</td>
                <td style="padding: 7px 0; color: #1f2937; font-weight: 700; text-align: right; font-family: monospace;">{{ $order->tracking_id ?? ('#ORD-' . $order->id) }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Current Status:</td>
                <td style="padding: 7px 0; text-align: right;">
                    <span style="display: inline-block; background: #fef3c7; color: {{ $statusColor }}; padding: 3px 10px; border-radius: 4px; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                        {{ ucfirst(str_replace('_', ' ', $order->status ?? 'pending')) }}
                    </span>
                </td>
            </tr>
            @if(isset($order->delivery_address))
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Destination:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $order->delivery_address }}</td>
            </tr>
            @endif
            @if(($order->status ?? '') === 'assigned' || ($order->status ?? '') === 'in_transit')
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Assigned Vehicle / Driver:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">
                    {{ $order->vehicle->registration_number ?? 'Fleet Assigned' }} 
                    @if(isset($order->driver->name) || isset($order->vehicle->driver_name))
                        ({{ $order->driver->name ?? $order->vehicle->driver_name }})
                    @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    @if(($order->status ?? '') === 'delivered')
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px;">
        <p style="margin: 0; color: #166534; font-size: 13px; line-height: 1.5;">
            <strong>✓ Consignment Delivered</strong><br>
            Your order has arrived at the destination. Proof of Delivery (POD) signature and photos are available in your portal.
        </p>
    </div>
    @endif

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ url('/client/dispatches') }}" style="display: inline-block; background: #d96b43; color: white; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;">Track Dispatch Live →</a>
    </div>
</div>
@endsection