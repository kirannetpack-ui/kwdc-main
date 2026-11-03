@extends('layouts.app')

@section('title', 'Bank & QR Reconciliation | KTM-WDC Admin')
@section('header', 'Direct Bank & Interbank QR Settlements')

@section('content')
@php
    $totalCount = $transactions->total();
    $pendingCount = \App\Models\Transaction::whereIn('payment_method', ['bank_transfer', 'banking_qr'])->where('status', 'pending')->count();
    $completedCount = \App\Models\Transaction::whereIn('payment_method', ['bank_transfer', 'banking_qr'])->where('status', 'completed')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-3">
    <!-- Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Direct Bank & QR Reconciliation</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-building-columns text-[9px]"></i>
                {{ $pendingCount }} Awaiting Review
            </span>
        </div>
        <div>
            <a href="{{ route('admin.invoices.admin') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                <i class="fas fa-file-invoice"></i> All Invoices
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs px-3.5 py-2.5 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs px-3.5 py-2.5 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Main Table Container -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-4 shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 font-semibold text-[11px] uppercase tracking-wider">
                        <th class="py-2.5 px-3">Transaction / Method</th>
                        <th class="py-2.5 px-3">Invoice & Client</th>
                        <th class="py-2.5 px-3">Amount</th>
                        <th class="py-2.5 px-3">Bank Details / UTR</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Proof / Voucher</th>
                        <th class="py-2.5 px-3 text-right">Reconciliation Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                    @php
                        $details = is_array($tx->payment_details) ? $tx->payment_details : [];
                        $isQr = $tx->payment_method === 'banking_qr';
                        $status = strtolower($tx->status ?? 'pending');
                        $invoice = $tx->invoice;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <!-- Transaction & Method -->
                        <td class="py-3 px-3">
                            <div class="font-mono text-xs font-bold text-slate-900">{{ $tx->transaction_id }}</div>
                            <div class="inline-flex items-center gap-1 text-[10.5px] mt-0.5 font-medium {{ $isQr ? 'text-indigo-600' : 'text-emerald-700' }}">
                                <i class="fas {{ $isQr ? 'fa-qrcode' : 'fa-building-columns' }}"></i>
                                <span>{{ $isQr ? 'Fonepay / Banking QR' : 'Direct Bank Deposit' }}</span>
                            </div>
                        </td>

                        <!-- Invoice & Client -->
                        <td class="py-3 px-3">
                            <div class="font-semibold text-slate-800">
                                @if($invoice)
                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="text-orange-600 hover:underline">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">#{{ $tx->invoice_id }}</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500">{{ $tx->user->name ?? 'Valued Customer' }}</div>
                        </td>

                        <!-- Amount -->
                        <td class="py-3 px-3">
                            <div class="font-bold text-slate-900 text-xs">रू {{ number_format($tx->amount, 2) }}</div>
                            <div class="text-[10px] text-slate-400">{{ $tx->created_at->format('M d, Y H:i') }}</div>
                        </td>

                        <!-- Bank Details / UTR -->
                        <td class="py-3 px-3">
                            @if($isQr)
                                <div class="font-mono text-xs font-semibold text-slate-800">RRN: {{ $details['rrn_code'] ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-500">Bank: {{ $details['sender_bank'] ?? 'Mobile Banking' }}</div>
                            @else
                                <div class="font-semibold text-slate-800">{{ $details['bank_name'] ?? 'Bank Transfer' }}</div>
                                <div class="font-mono text-[11px] text-slate-600">Ref/UTR: {{ $details['reference_number'] ?? 'N/A' }}</div>
                                <div class="text-[10px] text-slate-400">Depositor: {{ $details['depositor_name'] ?? 'N/A' }}</div>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="py-3 px-3">
                            @if($status === 'completed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-check text-[8px]"></i> Reconciled
                                </span>
                            @elseif($status === 'failed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $tx->notes }}">
                                    <i class="fas fa-times text-[8px]"></i> Rejected
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fas fa-clock text-[8px]"></i> Pending Review
                                </span>
                            @endif
                        </td>

                        <!-- Proof / Voucher -->
                        <td class="py-3 px-3">
                            @if(!empty($details['voucher_path']))
                                <a href="{{ asset('storage/' . $details['voucher_path']) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold transition border border-slate-200">
                                    <i class="fas fa-paperclip text-slate-500"></i> View Slip
                                </a>
                            @else
                                <span class="text-slate-400 text-[11px] italic">No slip uploaded</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-3 text-right">
                            @if($status === 'pending')
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Approve -->
                                    <form method="POST" action="{{ route('admin.payments.bank-transfers.approve', $tx->id) }}" onsubmit="return confirm('Confirm settlement of NPR {{ number_format($tx->amount, 2) }}? This marks Invoice #{{ $invoice->invoice_number ?? $tx->invoice_id }} as PAID.')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10.5px] shadow-xs transition">
                                            <i class="fas fa-check text-[9px]"></i> Approve &amp; Settle
                                        </button>
                                    </form>

                                    <!-- Reject -->
                                    <button type="button" onclick="promptRejectBank({{ $tx->id }}, '{{ $tx->transaction_id }}')" class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 font-semibold text-[10.5px] transition">
                                        <i class="fas fa-times text-[9px]"></i> Reject
                                    </button>
                                </div>
                            @elseif($status === 'completed')
                                <span class="text-[11px] text-emerald-600 font-semibold">
                                    Receipt: <span class="font-mono">{{ $tx->receipt_no ?? 'Settled' }}</span>
                                </span>
                            @else
                                <span class="text-[11px] text-rose-500 italic truncate max-w-xs block" title="{{ $tx->notes }}">
                                    {{ Str::limit($tx->notes ?? 'Rejected by finance', 25) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-building-columns text-2xl text-slate-300 block mb-1"></i>
                            No direct bank or QR settlements submitted yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100">
            {{ $transactions->links() }}
        </div>
    </div>
</div>

<script>
function promptRejectBank(transactionId, transactionCode) {
    const reason = prompt(`Enter reason for rejecting transfer ${transactionCode}:`);
    if (reason && reason.trim()) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/payments/bank-transfers/${transactionId}/reject`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'rejection_reason';
        reasonInput.value = reason.trim();
        form.appendChild(reasonInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
