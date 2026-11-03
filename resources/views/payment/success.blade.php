@extends('layouts.app')

@section('title', 'Payment Successful')
@section('header', 'Payment Successful')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle text-success fa-5x mb-3"></i>
                    <h2 class="fw-bold text-success">Payment Successful!</h2>
                    <p class="text-muted">Your payment has been completed successfully.</p>
                    <p class="text-muted">A receipt has been sent to your email.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-orange mt-3">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary mt-3 ms-2">
                        <i class="fas fa-file-invoice me-2"></i>View Invoices
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection