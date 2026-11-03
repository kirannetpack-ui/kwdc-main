@extends('emails.layout')

@section('content')
<div class="animate-fade">
    <div style="margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin: 0 0 10px;">🙏 Namaste {{ $recipientName }}!</h2>
        <p style="color: #4b5563; font-size: 16px; margin: 0;">Status update on your {{ $type }} order.</p>
    </div>
    
    <div style="text-align: center; margin: 20px 0;">
        <div class="animate-pulse" style="display: inline-block; background: {{ $statusColor }}20; border-radius: 50%; padding: 20px;">
            <span style="font-size: 50px;">{{ $statusIcon }}</span>
        </div>
    </div>
    
    <div style="background: {{ $statusColor }}10; border-left: 4px solid {{ $statusColor }}; border-radius: 8px; padding: 20px; margin: 20px 0;">
        <h3 style="color: #1f2937; margin: 0 0 10px;">Status: {{ $statusText }}</h3>
        <p style="color: #4b5563; margin: 0;">{{ $statusMessage }}</p>
    </div>
    
    <div style="background: #f9fafb; border-radius: 12px; padding: 20px; margin: 20px 0;">
        <h4 style="color: #1f2937; margin: 0 0 10px;">Order Information</h4>
        <p style="margin: 5px 0;"><strong>Order Number:</strong> {{ $orderNumber }}</p>
        @if(isset($driverName))
        <p style="margin: 5px 0;"><strong>Driver:</strong> {{ $driverName }} ({{ $driverPhone }})</p>
        @endif
        @if(isset($estimatedArrival))
        <p style="margin: 5px 0;"><strong>Estimated Arrival:</strong> {{ $estimatedArrival }}</p>
        @endif
    </div>
    
    <div style="text-align: center; margin-top: 25px;">
        <a href="{{ $trackingUrl }}" style="display: inline-block; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 600;">Track Live →</a>
    </div>
</div>
@endsection