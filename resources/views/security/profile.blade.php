@extends('layouts.security')

@section('title', 'Agency Profile')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Security Agency Profile</h2>
            <p class="text-muted mb-0">Update your agency details and required Nepal government compliance documents.</p>
        </div>
        <a href="{{ route('security.dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('security.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light">
                <strong>Agency Information</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Agency Name *</label>
                        <input type="text" name="agency_name" class="form-control" value="{{ old('agency_name', $agency->agency_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Registration Number *</label>
                        <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $agency->registration_number) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $agency->license_number) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">PAN / VAT Number</label>
                        <input type="text" name="pan_vat_number" class="form-control" value="{{ old('pan_vat_number', $agency->pan_vat_number) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Year Established</label>
                        <input type="number" name="year_established" class="form-control" min="1900" max="{{ date('Y') }}" value="{{ old('year_established', $agency->year_established) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Services Offered</label>
                        <input type="text" name="services_offered" class="form-control" value="{{ old('services_offered', $agency->services_offered) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Agency Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $agency->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emergency Phone</label>
                        <input type="text" name="emergency_phone" class="form-control" value="{{ old('emergency_phone', $agency->emergency_phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Agency Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $agency->email ?? Auth::user()->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Certifications</label>
                        <input type="text" name="certifications" class="form-control" value="{{ old('certifications', $agency->certifications) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Agency Address</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', $agency->address) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light">
                <strong>Required Government Documents</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Registration Certificate</label>
                        <input type="file" name="registration_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        @if($agency->registration_certificate_path)
                            <a href="{{ route('documents.private.show', ['path' => $agency->registration_certificate_path]) }}" target="_blank" class="btn btn-sm btn-link mt-2 p-0">View current file</a>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">License Certificate</label>
                        <input type="file" name="license_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        @if($agency->license_certificate_path)
                            <a href="{{ route('documents.private.show', ['path' => $agency->license_certificate_path]) }}" target="_blank" class="btn btn-sm btn-link mt-2 p-0">View current file</a>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PAN / VAT Certificate</label>
                        <input type="file" name="pan_vat_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        @if($agency->pan_vat_certificate_path)
                            <a href="{{ route('documents.private.show', ['path' => $agency->pan_vat_certificate_path]) }}" target="_blank" class="btn btn-sm btn-link mt-2 p-0">View current file</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4">Save Agency Profile</button>
        </div>
    </form>
</div>
@endsection
