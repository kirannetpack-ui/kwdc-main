@extends('layouts.app')

@section('title', 'Security Agency Review')
@section('header', 'Agency Review')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $agency->agency_name }}</h2>
            <p class="text-muted mb-0">Security Agency Verification</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.security.agencies') }}" class="btn btn-outline-secondary">Back to Agencies</a>
            @if($agency->status !== 'approved')
                <form action="{{ route('admin.security.agency.approve', $agency) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Approve Agency</button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light">
            <strong>Agency Information</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><strong>Agency Name:</strong><br>{{ $agency->agency_name }}</div>
                <div class="col-md-6"><strong>Registration Number:</strong><br>{{ $agency->registration_number }}</div>
                <div class="col-md-6"><strong>License Number:</strong><br>{{ $agency->license_number ?? 'N/A' }}</div>
                <div class="col-md-6"><strong>PAN / VAT Number:</strong><br>{{ $agency->pan_vat_number ?? 'N/A' }}</div>
                <div class="col-md-6"><strong>Year Established:</strong><br>{{ $agency->year_established ?? 'N/A' }}</div>
                <div class="col-md-6"><strong>Services Offered:</strong><br>{{ $agency->services_offered ?? 'N/A' }}</div>
                <div class="col-md-6"><strong>Phone:</strong><br>{{ $agency->phone ?? 'N/A' }}</div>
                <div class="col-md-6"><strong>Emergency Phone:</strong><br>{{ $agency->emergency_phone ?? 'N/A' }}</div>
                <div class="col-md-6"><strong>Email:</strong><br>{{ $agency->email ?? 'N/A' }}</div>
                <div class="col-md-6"><strong>Status:</strong><br><span class="badge bg-{{ $agency->status === 'approved' ? 'success' : ($agency->status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($agency->status) }}</span></div>
                <div class="col-12"><strong>Address:</strong><br>{{ $agency->address ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light">
            <strong>Uploaded Compliance Documents</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6>Registration Certificate</h6>
                        @if($agency->registration_certificate_path)
                            <a href="{{ route('documents.private.show', ['path' => $agency->registration_certificate_path]) }}" target="_blank" class="btn btn-sm btn-primary">View Document</a>
                        @else
                            <p class="text-muted mb-0">Not uploaded</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6>License Certificate</h6>
                        @if($agency->license_certificate_path)
                            <a href="{{ route('documents.private.show', ['path' => $agency->license_certificate_path]) }}" target="_blank" class="btn btn-sm btn-primary">View Document</a>
                        @else
                            <p class="text-muted mb-0">Not uploaded</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6>PAN / VAT Certificate</h6>
                        @if($agency->pan_vat_certificate_path)
                            <a href="{{ route('documents.private.show', ['path' => $agency->pan_vat_certificate_path]) }}" target="_blank" class="btn btn-sm btn-primary">View Document</a>
                        @else
                            <p class="text-muted mb-0">Not uploaded</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($agency->admin_notes)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light">
            <strong>Admin Notes</strong>
        </div>
        <div class="card-body">
            {{ $agency->admin_notes }}
        </div>
    </div>
    @endif
</div>
@endsection
