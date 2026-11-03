@extends('layouts.security')
@section('content')
    <h2>Add Good</h2>
    <form method="POST" action="{{ route('security.goods.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Item Name *</label>
            <input type="text" name="item_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Category *</label>
            <input type="text" name="category" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Quantity Available *</label>
            <input type="number" name="quantity_available" class="form-control" required min="0">
        </div>
        <div class="mb-3">
            <label>Unit Price *</label>
            <input type="number" step="0.01" name="unit_price" class="form-control" required min="0">
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="available">Available</option>
                <option value="rented">Rented</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>
        <!-- add other fields as needed -->
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@endsection