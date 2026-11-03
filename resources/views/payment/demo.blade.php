@extends('layouts.app')
@section('title', 'Demo invoices')
@section('header', 'Demo invoices')
@section('content')
<div style="padding:24px;max-width:1000px;">
    <h2>Sample invoices</h2>
    <p>This demo does not accept money. Bank details and merchant QR codes will be added after the business account is ready.</p>
    <a href="{{ route('payment.history') }}">View sample payment history</a>
    <p>To test reconciliation, record a simulated transfer below, then sign in as administrator and open Bank / QR Transfers to approve or reject it. No real money is involved.</p>
    <table class="table mt-4"><thead><tr><th>Invoice</th><th>Amount (NPR)</th><th>Status</th><th>Try the workflow</th></tr></thead><tbody>
    @forelse($invoices as $invoice)
        <tr><td>{{ $invoice->invoice_number }}</td><td>{{ number_format((float)($invoice->grand_total ?: $invoice->amount), 2) }}</td><td>Sample — unpaid</td><td><form method="POST" action="{{ route('payment.demo.submit') }}">@csrf<input type="hidden" name="invoice_id" value="{{ $invoice->id }}"><button class="btn btn-outline-secondary" type="submit">Simulate bank transfer</button></form></td></tr>
    @empty
        <tr><td colspan="4">No unpaid sample invoices for this account.</td></tr>
    @endforelse
    </tbody></table>
</div>
@endsection
