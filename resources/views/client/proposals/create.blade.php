@extends('layouts.app')

@section('title', 'Create Proposal')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Create New Proposal</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('client.proposals.store') }}" method="POST">
                @csrf
                
                <input type="hidden" name="warehouse_request_id" value="{{ $warehouseRequest->id }}">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Warehouse</label>
                            <input type="text" class="form-control" value="{{ $warehouseRequest->warehouse->name ?? 'N/A' }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" value="{{ $warehouseRequest->warehouse->city ?? 'N/A' }}, {{ $warehouseRequest->warehouse->state ?? 'N/A' }}" disabled>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Proposed Price (रु) <span class="text-danger">*</span></label>
                            <input type="number" name="proposed_price" class="form-control @error('proposed_price') is-invalid @enderror" 
                                   step="0.01" min="0" required>
                            @error('proposed_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Valid Until</label>
                            <input type="date" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror">
                            @error('valid_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                              rows="4" placeholder="Describe your proposal..."></textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Submit Proposal
                    </button>
                    <a href="{{ route('client.proposals.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection