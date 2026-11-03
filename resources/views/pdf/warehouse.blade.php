@extends('pdf.layout')

@section('title', 'Warehouse Registration Certificate - ' . $warehouse->name)
@section('doc_title', 'Warehouse Registration Certificate')
@section('doc_ref', 'WH-' . str_pad($warehouse->id, 5, '0', STR_PAD_LEFT))

@section('content')
    <div style="margin-bottom: 16px;">
        <h2 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 800; color: #0f172a;">{{ $warehouse->name }}</h2>
        <p style="margin: 0; font-size: 11px; color: #64748b;">
            <strong>Facility Address:</strong> {{ $warehouse->address ?: ($warehouse->location ?: 'Kathmandu Valley, Nepal') }}
            @if($warehouse->city) &bull; {{ $warehouse->city }} @endif
        </p>
    </div>

    <div class="section-header">Facility & Ownership Profile</div>

    <table class="content-table">
        <tr>
            <th>Registered Owner</th>
            <td style="font-weight: 700; color: #0f172a;">
                {{ optional($warehouse->user)->name ?? optional($warehouse->owner)->name ?? 'Gita Property Demo' }}
            </td>
        </tr>
        <tr>
            <th>Owner Contact</th>
            <td>{{ $warehouse->contact_number ?: '+977-9807778899' }} @if($warehouse->email) &bull; {{ $warehouse->email }} @endif</td>
        </tr>
        <tr>
            <th>Certification Status</th>
            <td>
                @if($warehouse->status == 'approved')
                    <span class="status-pill status-approved">Certified & Approved</span>
                @elseif($warehouse->status == 'pending')
                    <span class="status-pill status-pending">Pending Verification</span>
                @else
                    <span class="status-pill status-rejected">{{ ucfirst($warehouse->status) }}</span>
                @endif
                @if($warehouse->approved_at)
                    <span style="font-size: 9px; color: #64748b; margin-left: 8px;">Approved on: {{ $warehouse->approved_at->format('M d, Y') }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Total Storage Area</th>
            <td>
                <strong>{{ number_format((float) $warehouse->area_sqft, 2) }} sq ft</strong>
                @if($warehouse->area_sqm)
                    <span style="color: #64748b;">({{ number_format((float) $warehouse->area_sqm, 2) }} sq m)</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Base Commercial Rate</th>
            <td>
                <strong style="color: #ea580c; font-size: 12px;">NPR {{ number_format((float) $warehouse->price_per_sqft, 2) }}</strong>
                <span style="font-size: 9px; color: #64748b;">per sq ft / month</span>
            </td>
        </tr>
        <tr>
            <th>Cold Storage Climate</th>
            <td>
                @if($warehouse->cold_storage)
                    <span class="tag-pill" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;">Temperature Controlled</span>
                    <span style="font-size: 10px; color: #0f172a;">Range: {{ $warehouse->temperature_min ?? 0 }}&deg;C to {{ $warehouse->temperature_max ?? 10 }}&deg;C</span>
                    @if($warehouse->humidity_control) &bull; Active Humidity Control @endif
                @else
                    <span>Standard Dry Ambient Storage</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Security & Safety</th>
            <td>
                <span>CCTV Cameras: <strong>{{ $warehouse->cctv_count ?? 4 }}</strong></span> &bull;
                <span>On-Site Guards: <strong>{{ $warehouse->guards_count ?? 2 }}</strong></span> &bull;
                <span>Extinguishers: <strong>{{ $warehouse->fire_extinguishers ?? 3 }}</strong></span>
            </td>
        </tr>
        <tr>
            <th>Logistics Facilities</th>
            <td>
                @if(is_array($warehouse->facilities) && count($warehouse->facilities) > 0)
                    @foreach($warehouse->facilities as $facility)
                        <span class="tag-pill">{{ $facility }}</span>
                    @endforeach
                @elseif($warehouse->facilities)
                    <span class="tag-pill">{{ $warehouse->facilities }}</span>
                @else
                    <span class="tag-pill">CCTV</span>
                    <span class="tag-pill">Loading Dock</span>
                    <span class="tag-pill">Guard Post</span>
                    <span class="tag-pill">Parking</span>
                @endif
            </td>
        </tr>
    </table>

    @if($warehouse->description)
        <div class="section-header">Operational Description</div>
        <p style="font-size: 10px; color: #334155; line-height: 1.6; margin: 4px 0 16px 0; background: #f8fafc; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 4px;">
            {{ $warehouse->description }}
        </p>
    @endif
@endsection
