@extends('layouts.app')

@section('title', 'Edit Proposal')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Proposal #{{ $proposal->id }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('client.proposals.update', $proposal->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Warehouse</label>
                            <input type="text" class="form-control" value="{{ $proposal->warehouse->name ?? 'N/A' }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" value="{{ $proposal->warehouse->city ?? 'N/A' }}, {{ $proposal->warehouse->state ?? 'N/A' }}" disabled>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Proposed Price (रु) <span class="text-danger">*</span></label>
                            <input type="number" name="proposed_price" class="form-control @error('proposed_price') is-invalid @enderror" 
                                   step="0.01" min="0" value="{{ old('proposed_price', $proposal->proposed_price) }}" required>
                            @error('proposed_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Valid Until</label>
                            <input type="date" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror" 
                                   value="{{ old('valid_until', $proposal->valid_until ? $proposal->valid_until->format('Y-m-d') : '') }}">
                            @error('valid_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                              rows="4" placeholder="Describe your proposal...">{{ old('description', $proposal->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Proposal
                    </button>
                    <a href="{{ route('client.proposals.show', $proposal->id) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection