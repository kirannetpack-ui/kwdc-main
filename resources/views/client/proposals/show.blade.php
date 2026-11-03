@extends('layouts.app')

@section('title', 'Proposal Details')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Proposal #{{ $proposal->id }}</h5>
            <span class="badge bg-{{ $proposal->status_badge }}">{{ $proposal->status_text }}</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Warehouse Details</h6>
                    <table class="table">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $proposal->warehouse->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Location:</strong></td>
                            <td>{{ $proposal->warehouse->city ?? 'N/A' }}, {{ $proposal->warehouse->state ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Area:</strong></td>
                            <td>{{ $proposal->warehouse->area ?? 'N/A' }} sq ft</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Proposal Details</h6>
                    <table class="table">
                        <tr>
                            <td><strong>Proposed Price:</strong></td>
                            <td class="text-primary">रु {{ number_format($proposal->proposed_price, 2) }}</td>
                        </tr>
                        @if($proposal->negotiated_price)
                        <tr>
                            <td><strong>Negotiated Price:</strong></td>
                            <td class="text-warning">रु {{ number_format($proposal->negotiated_price, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Valid Until:</strong></td>
                            <td>{{ $proposal->valid_until ? $proposal->valid_until->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td><span class="badge bg-{{ $proposal->status_badge }}">{{ $proposal->status_text }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $proposal->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($proposal->accepted_at)
                        <tr>
                            <td><strong>Accepted:</strong></td>
                            <td>{{ $proposal->accepted_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            @if($proposal->description)
            <div class="mt-3">
                <h6>Description</h6>
                <p>{{ $proposal->description }}</p>
            </div>
            @endif

            @if($proposal->negotiation_message)
            <div class="mt-3">
                <h6>Negotiation Message</h6>
                <div class="alert alert-info">{{ $proposal->negotiation_message }}</div>
            </div>
            @endif

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('client.proposals.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Proposals
                </a>
                
                @if($proposal->status == 'pending')
                    <a href="{{ route('client.proposals.edit', $proposal->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <a href="{{ route('client.proposals.accept', $proposal->id) }}" 
                       class="btn btn-success"
                       onclick="return confirm('Are you sure you want to accept this proposal?')">
                        <i class="fas fa-check me-2"></i>Accept
                    </a>
                    <a href="{{ route('client.proposals.reject', $proposal->id) }}" 
                       class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to reject this proposal?')">
                        <i class="fas fa-times me-2"></i>Reject
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection