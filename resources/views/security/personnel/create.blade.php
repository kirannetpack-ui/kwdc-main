@extends('layouts.security')

@section('title', 'Add Personnel')

@section('content')
<div class="container">
    <h2>Add New Security Personnel</h2>
    <a href="{{ route('security.personnel.index') }}" class="btn btn-secondary mb-3">&larr; Back to List</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('security.personnel.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full Name *</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="employee_id" class="form-label">Employee ID</label>
                <input type="text" name="employee_id" id="employee_id" class="form-control" value="{{ old('employee_id') }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Phone *</label>
                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="citizenship_number" class="form-label">Citizenship Number</label>
                <input type="text" name="citizenship_number" id="citizenship_number" class="form-control" value="{{ old('citizenship_number') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="dob" class="form-label">Date of Birth</label>
                <input type="date" name="dob" id="dob" class="form-control" value="{{ old('dob') }}">
            </div>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea name="address" id="address" class="form-control" rows="2">{{ old('address') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="position" class="form-label">Position</label>
                <input type="text" name="position" id="position" class="form-control" value="{{ old('position') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="on_leave" {{ old('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="qualifications" class="form-label">Qualifications</label>
            <textarea name="qualifications" id="qualifications" class="form-control" rows="2">{{ old('qualifications') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="training_certificate" class="form-label">Training Certificate</label>
                <input type="text" name="training_certificate" id="training_certificate" class="form-control" value="{{ old('training_certificate') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="training_expiry" class="form-label">Training Expiry Date</label>
                <input type="date" name="training_expiry" id="training_expiry" class="form-control" value="{{ old('training_expiry') }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="form-check">
                    <input type="checkbox" name="has_vehicle" id="has_vehicle" class="form-check-input" value="1" {{ old('has_vehicle') ? 'checked' : '' }}>
                    <label for="has_vehicle" class="form-check-label">Has Vehicle</label>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="vehicle_type" class="form-label">Vehicle Type</label>
                <input type="text" name="vehicle_type" id="vehicle_type" class="form-control" value="{{ old('vehicle_type') }}">
            </div>
        </div>

        <div class="mb-3">
            <label for="shift_availability" class="form-label">Shift Availability (JSON)</label>
            <input type="text" name="shift_availability" id="shift_availability" class="form-control" value="{{ old('shift_availability') }}" placeholder='e.g. {"day":true,"night":false}'>
            <small class="text-muted">Optional: provide a JSON object.</small>
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Photo</label>
            <input type="file" name="photo" id="photo" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Save Personnel</button>
    </form>
</div>
@endsection