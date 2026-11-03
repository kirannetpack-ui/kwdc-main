@extends('layouts.app')

@section('title', 'Payment Failed')
@section('header', 'Payment Failed')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-times-circle text-danger fa-5x mb-3"></i>
                    <h2 class="fw-bold text-danger">Payment Failed!</h2>
                    <p class="text-muted">{{ session('error', 'Something went wrong. Please try again.') }}</p>
                    <a href="{{ route('invoices.index') }}" class="btn btn-orange mt-3">
                        <i class="fas fa-file-invoice me-2"></i>View Invoices
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary mt-3 ms-2">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection