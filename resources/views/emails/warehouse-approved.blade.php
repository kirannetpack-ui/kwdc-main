@extends('emails.layout')

@section('content')
@php
    $recipient = $recipientName ?? $warehouse->user->name ?? $warehouse->owner_name ?? 'Property Owner';
    $name = $warehouseName ?? $warehouse->name ?? 'Warehouse Facility';
    $location = $warehouseLocation ?? $warehouse->location ?? $warehouse->address ?? 'Kathmandu, Nepal';
    $targetUrl = $dashboardUrl ?? url('/property/dashboard');
@endphp
<div class="animate-fade">
    <div style="margin-bottom: 25px; text-align: center;">
        <div class="animate-pulse" style="display: inline-block; background: #d1fae5; border-radius: 50%; padding: 16px; margin-bottom: 12px;">
            <span style="font-size: 40px;">✅</span>
        </div>
        <h2 style="color: #1f2937; margin: 0 0 8px;">🙏 Namaste {{ $recipient }}!</h2>
        <p style="color: #4b5563; font-size: 16px; margin: 0;">Congratulations! Your warehouse listing has been approved and is now active.</p>
    </div>
    
    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 12px; padding: 20px; margin: 20px 0;">
        <h3 style="color: #1f2937; margin: 0 0 15px; font-size: 15px; text-transform: uppercase; letter-spacing: 0.05em;">Facility Details</h3>
        <p style="margin: 6px 0;"><strong>Name:</strong> {{ $name }}</p>
        <p style="margin: 6px 0;"><strong>Location:</strong> {{ $location }}</p>
        <p style="margin: 6px 0;"><strong>Status:</strong> <span style="display: inline-block; background: #d1fae5; color: #059669; font-weight: 600; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Approved & Live</span></p>
    </div>
    
    <div style="text-align: center; margin-top: 25px;">
        <a href="{{ $targetUrl }}" style="display: inline-block; background: #d96b43; color: white; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 600;">View Property Dashboard →</a>
    </div>
</div>
@endsection