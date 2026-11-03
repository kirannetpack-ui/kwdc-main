@extends('pdf.layout')

@section('title', 'Dispatch Manifest - #' . $dispatch->id)
@section('doc_title', 'Dispatch Manifest & Consignment Note')
@section('doc_ref', $dispatch->tracking_id ?: ('DSP-' . str_pad($dispatch->id, 5, '0', STR_PAD_LEFT)))

@section('content')
    <div style="margin-bottom: 16px;">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <td style="border: none; padding: 0;">
                    <h2 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 800; color: #0f172a;">
                        Dispatch Order #{{ $dispatch->id }}
                    </h2>
                    <p style="margin: 0; font-size: 11px; color: #64748b;">
                        Tracking Waybill: <strong style="color: #ea580c;">{{ $dispatch->tracking_id ?: 'KWDC-TRK-' . $dispatch->id }}</strong>
                    </p>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: top;">
                    <span class="status-pill @if($dispatch->status == 'delivered') status-approved @elseif($dispatch->status == 'pending') status-pending @elseif($dispatch->status == 'cancelled') status-rejected @else status-completed @endif">
                        {{ ucfirst(str_replace('_', ' ', $dispatch->status ?? 'pending')) }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-header">Transport & Consignment Summary</div>

    <table class="content-table">
        <tr>
            <th>Shipper / Client</th>
            <td style="font-weight: 700;">{{ optional($dispatch->client)->name ?? 'Corporate Client' }}</td>
            <th style="width: 22%;">Contact Phone</th>
            <td>{{ optional($dispatch->client)->phone ?? '+977-1-4400000' }}</td>
        </tr>
        <tr>
            <th>Assigned Driver</th>
            <td style="font-weight: 700;">{{ optional($dispatch->driver)->name ?? 'Designated Fleet Operator' }}</td>
            <th>Vehicle Fleet</th>
            <td>{{ optional($dispatch->driver)->vehicle_type ?? 'Medium Commercial Truck' }}</td>
        </tr>
        <tr>
            <th>Pickup Origin</th>
            <td colspan="3">{{ $dispatch->pickup_address ?: 'Kathmandu Logistics Hub' }}</td>
        </tr>
        <tr>
            <th>Final Destination</th>
            <td colspan="3">{{ $dispatch->delivery_address ?: 'Designated Receiving Center' }}</td>
        </tr>
        <tr>
            <th>Transit Distance</th>
            <td><strong>{{ number_format((float) ($dispatch->total_distance ?? 0), 1) }} km</strong></td>
            <th>Payment Status</th>
            <td>
                <strong style="text-transform: uppercase; color: {{ ($dispatch->payment_status ?? 'unpaid') === 'paid' ? '#16a34a' : '#ea580c' }};">
                    {{ $dispatch->payment_status ?? 'Unpaid' }}
                </strong>
            </td>
        </tr>
    </table>

    @php
        $stops = $dispatch->deliveryStops && $dispatch->deliveryStops->count() > 0 ? $dispatch->deliveryStops : ($dispatch->stops ?? collect([]));
    @endphp

    @if($stops->count() > 0)
        <div class="section-header">Delivery Stops & Consignee Manifest ({{ $stops->count() }})</div>
        <table class="content-table">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">#</th>
                    <th>Consignee / Recipient</th>
                    <th>Destination Stop Address</th>
                    <th style="width: 22%;">Phone Number</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stops as $index => $stop)
                <tr>
                    <td style="text-align: center; font-weight: 700;">{{ $stop->stop_order ?? ($index + 1) }}</td>
                    <td style="font-weight: 700;">{{ $stop->recipient_name }}</td>
                    <td>{{ $stop->address }}</td>
                    <td>{{ $stop->recipient_phone }}</td>
                    <td>
                        <span class="status-pill status-completed" style="font-size: 8px;">
                            {{ ucfirst($stop->status ?? 'Active') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="margin-top: 14px; text-align: right; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
        <span style="font-size: 11px; color: #64748b; margin-right: 12px; text-transform: uppercase; font-weight: 700;">Total Freight Consignment:</span>
        <strong style="font-size: 16px; color: #ea580c;">
            NPR {{ number_format((float) ($dispatch->grand_total ?? $dispatch->total_price ?? 0), 2) }}
        </strong>
    </div>
@endsection
