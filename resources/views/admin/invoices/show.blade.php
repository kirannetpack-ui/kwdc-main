@extends('layouts.app')

@section('title', 'Invoice #' . ($invoice->invoice_number ?? $invoice->id))
@section('header', 'Invoice')

@section('content')
@php
    $isAdmin = auth()->user()->isAdmin() || (auth()->user()->is_admin ?? false) || auth()->user()->role === 'admin';
    $backRoute = $isAdmin ? route('admin.invoices') : (route('invoices.index'));
    $client = $invoice->client ?? optional($invoice->warehouseRequest)->client ?? auth()->user();
    $warehouse = $invoice->warehouse ?? optional($invoice->warehouseRequest)->warehouse;
    $status = strtolower($invoice->status ?? $invoice->payment_status ?? 'pending');
    $isPaid = in_array($status, ['paid', 'completed']);
    $amount = (float) ($invoice->grand_total ?? $invoice->amount ?? 0);
    $subtotal = (float) ($invoice->subtotal ?? $amount);
    $tax = (float) ($invoice->tax_amount ?? 0);
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Top Bar Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <a href="{{ $backRoute }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-orange-600 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Invoices
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.download', $invoice->id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-pdf text-red-500 mr-2"></i> Download PDF
            </a>
            @if($isAdmin && !$isPaid)
                <form method="POST" action="{{ route('admin.invoices.mark-paid', $invoice->id) }}" class="inline-block">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition shadow-sm">
                        <i class="fas fa-check-circle mr-2"></i> Mark as Paid
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-green-600"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Invoice Sheet (Apple-like Clean Surface) -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-12 space-y-8">
        <!-- Invoice Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 border-b border-gray-100 pb-8">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-8 h-8 rounded-lg bg-orange-600 text-white flex items-center justify-center font-bold text-sm">K</span>
                    <span class="text-xl font-bold tracking-tight text-gray-900">KTM-WDC</span>
                </div>
                <p class="text-xs uppercase font-bold text-gray-400 tracking-wider">Logistics & Warehousing Portal</p>
                <p class="text-xs text-gray-500 mt-1">Kathmandu, Nepal</p>
            </div>
            <div class="sm:text-right">
                <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wider mb-2
                    {{ $isPaid ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                    <i class="fas {{ $isPaid ? 'fa-check-circle' : 'fa-clock' }} mr-1.5 self-center"></i>
                    {{ $isPaid ? 'Paid' : 'Pending Payment' }}
                </span>
                <h1 class="text-2xl font-bold font-mono text-gray-900">{{ $invoice->invoice_number ?? ('INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT)) }}</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Issued: {{ $invoice->created_at?->format('M d, Y') ?? now()->format('M d, Y') }}
                </p>
                @if($invoice->due_date || $invoice->payment_due_date)
                    <p class="text-xs text-gray-500">
                        Due: {{ \Carbon\Carbon::parse($invoice->due_date ?? $invoice->payment_due_date)->format('M d, Y') }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Billing Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm">
            <div>
                <p class="text-xs font-bold uppercase text-gray-400 tracking-wider mb-2">Billed To</p>
                <p class="font-bold text-gray-900 text-base">{{ $client->name ?? 'Valued Client' }}</p>
                <p class="text-gray-600 mt-1">{{ $client->email ?? '' }}</p>
                @if($client->phone)
                    <p class="text-gray-600">{{ $client->phone }}</p>
                @endif
                @if($client->address)
                    <p class="text-gray-500 mt-0.5">{{ $client->address }}</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-bold uppercase text-gray-400 tracking-wider mb-2">Service Details</p>
                @if($warehouse)
                    <p class="font-semibold text-gray-900">{{ $warehouse->name }}</p>
                    <p class="text-gray-600">{{ $warehouse->address ?? 'Nepal' }}</p>
                @endif
                @if($invoice->warehouse_request_id)
                    <p class="text-gray-500 mt-1">
                        Reference Request: 
                        <a href="{{ route('warehouse-requests.show', $invoice->warehouse_request_id) }}" class="text-orange-600 hover:underline font-mono">
                            #{{ $invoice->warehouse_request_id }}
                        </a>
                    </p>
                @endif
                <p class="text-gray-500 mt-1">{{ $invoice->description ?? 'Warehouse storage & logistics reservation' }}</p>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-xs uppercase font-bold text-gray-400 tracking-wider">
                        <th class="py-3">Item / Service</th>
                        <th class="py-3 text-right">Qty</th>
                        <th class="py-3 text-right">Rate</th>
                        <th class="py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-4">
                            <p class="font-semibold text-gray-900">{{ $invoice->description ?? 'Logistics & Warehouse Service' }}</p>
                            <p class="text-xs text-gray-500">{{ $warehouse ? $warehouse->name : 'Standard operational tier' }}</p>
                        </td>
                        <td class="py-4 text-right text-gray-600">1</td>
                        <td class="py-4 text-right text-gray-600">NPR {{ number_format($subtotal, 2) }}</td>
                        <td class="py-4 text-right font-medium text-gray-900">NPR {{ number_format($subtotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Calculation & Total Summary -->
        <div class="flex justify-end pt-4 border-t border-gray-100">
            <div class="w-full sm:w-72 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span>NPR {{ number_format($subtotal, 2) }}</span>
                </div>
                @if($tax > 0)
                    <div class="flex justify-between text-gray-600">
                        <span>Tax / VAT</span>
                        <span>NPR {{ number_format($tax, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-bold text-base text-gray-900 pt-3 border-t border-gray-200">
                    <span>Grand Total</span>
                    <span class="text-orange-600">NPR {{ number_format($amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Actions for Pending Client Invoices -->
        @if(!$isPaid && !$isAdmin)
            <div class="pt-6 border-t border-gray-100 bg-orange-50/60 -mx-8 -mb-8 p-8 rounded-b-3xl space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900">Complete Payment</h3>
                        <p class="text-xs text-gray-600">Select a secure local payment method to settle this invoice.</p>
                    </div>
                    <span class="text-lg font-bold text-orange-600">NPR {{ number_format($amount, 2) }}</span>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('payment.index') }}?invoice_id={{ $invoice->id }}&amount={{ $amount }}" class="inline-flex items-center px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                        <i class="fas fa-wallet mr-2"></i> Pay with Khalti
                    </a>
                    <a href="{{ route('payment.index') }}?invoice_id={{ $invoice->id }}&amount={{ $amount }}&gateway=esewa" class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                        <i class="fas fa-money-bill-wave mr-2"></i> Pay with eSewa
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
