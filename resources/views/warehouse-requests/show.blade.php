@extends('layouts.app')
@section('title', 'Request #'.$record->id)
@section('header', 'Warehouse Request')
@section('content')
<div class="record-detail">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <h2>Request #{{ $record->id }}</h2>
        <span class="badge bg-{{ $record->status === 'approved' ? 'success' : ($record->status === 'rejected' ? 'danger' : 'secondary') }}">{{ ucfirst($record->status) }}</span>
    </div>
    <section class="detail-section">
        <h3>Request details</h3>
        <dl class="detail-grid">
            <div><dt>Client</dt><dd>{{ $record->client?->name ?? 'Unavailable' }}</dd></div>
            <div><dt>Warehouse</dt><dd>{{ $record->assignedWarehouse?->name ?? $record->warehouse?->name ?? 'Not assigned' }}</dd></div>
            <div><dt>Area</dt><dd>{{ number_format($record->required_area ?? $record->space_required ?? 0) }} sq ft</dd></div>
            <div><dt>Duration</dt><dd>{{ $record->duration_months ?? 'Not specified' }} months</dd></div>
            <div><dt>Start date</dt><dd>{{ $record->preferred_start_date?->format('M j, Y') ?? 'Not specified' }}</dd></div>
            <div><dt>Submitted</dt><dd>{{ $record->created_at?->format('M j, Y, g:i A') }}</dd></div>
            <div><dt>Contact</dt><dd>{{ $record->contact_person ?? $record->client?->name }}</dd></div>
            <div><dt>Phone</dt><dd>{{ $record->contact_phone ?? $record->client?->phone ?? 'Not provided' }}</dd></div>
            <div><dt>Monthly rent</dt><dd>{{ $record->monthly_rent !== null ? 'NPR '.number_format($record->monthly_rent, 2) : 'Not agreed' }}</dd></div>
        </dl>
    </section>
    <section class="detail-section"><h3>Purpose</h3><p>{{ $record->purpose ?: 'No additional details.' }}</p></section>
    @if($canReview && $record->status === 'pending')
    <form method="POST" action="{{ route('warehouse-requests.decide', $record->id) }}" class="d-flex gap-3">
        @csrf
        <button class="btn btn-primary" name="decision" value="approved"><i class="fas fa-check me-2"></i>Approve request</button>
        <button class="btn btn-outline-danger" name="decision" value="rejected">Reject</button>
    </form>
    @endif
</div>
@endsection
