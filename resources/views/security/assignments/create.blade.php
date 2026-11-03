@extends('layouts.security')
@section('content')
    <h2>Create Assignment</h2>
    <form method="POST" action="{{ route('security.assignments.store') }}">
        @csrf
        <div class="mb-3">
            <label>Warehouse *</label>
            <select name="warehouse_id" class="form-control" required>
                @foreach($warehouses as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Personnel</label>
            <select name="personnel_id" class="form-control">
                <option value="">— Unassigned —</option>
                @foreach($personnel as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Start Date *</label>
            <input type="date" name="start_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control">
        </div>
        <div class="mb-3">
            <label>Shift *</label>
            <select name="shift" class="form-control" required>
                <option value="day">Day</option>
                <option value="night">Night</option>
                <option value="24_hours">24 Hours</option>
                <option value="custom">Custom</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Shift Start (HH:MM)</label>
            <input type="time" name="shift_start" class="form-control">
        </div>
        <div class="mb-3">
            <label>Shift End (HH:MM)</label>
            <input type="time" name="shift_end" class="form-control">
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Total Cost</label>
            <input type="number" step="0.01" name="total_cost" class="form-control" min="0">
        </div>
        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Assignment</button>
    </form>
@endsection