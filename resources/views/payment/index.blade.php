@extends('layouts.app')

@section('title', 'Make Payment | KTM-WDC')
@section('header', 'Make Payment')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[#E8E2D8]">
        <div>
            <h2 class="text-2xl font-serif font-bold text-[#24201D] tracking-tight" style="font-family: 'Newsreader', Georgia, serif;">Settle Pending Invoices</h2>
            <p class="text-sm text-[#645D56] mt-1">Select an outstanding invoice to pay securely using Khalti or eSewa.</p>
        </div>
        <div>
            <a href="{{ route('payment.history') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-[#24201D] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition shadow-2xs">
                <i class="fas fa-history text-xs text-[#D96B43]"></i>
                <span>Payment History</span>
            </a>
        </div>
    </div>

    <!-- Feedback Banner Slot -->
    <div id="paymentAlert" class="hidden rounded-xl p-4 text-sm font-medium transition-all duration-200"></div>

    @if(session('error'))
        <div class="rounded-xl p-4 bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-500 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="rounded-xl p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Invoices List -->
    <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl shadow-2xs overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E8E2D8] bg-[#FAF8F5] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-receipt text-[#D96B43]"></i>
                <span class="text-xs font-bold uppercase tracking-wider text-[#645D56]">Outstanding Invoices</span>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#D96B43]/10 text-[#D96B43] border border-[#D96B43]/20">
                {{ $invoices->count() }} Pending
            </span>
        </div>

        <div class="p-6">
            @if($invoices->count() > 0)
                <div class="space-y-4">
                    @foreach($invoices as $invoice)
                    @php
                        $invAmount = (float) ($invoice->grand_total ?? $invoice->amount ?? 0);
                        $dueDate = $invoice->payment_due_date ?? $invoice->due_date;
                    @endphp
                    <div class="border border-[#E8E2D8] rounded-xl p-5 bg-[#FAF8F5]/50 hover:bg-[#FAF8F5] transition flex flex-col md:flex-row md:items-center justify-between gap-5">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm font-bold text-[#24201D]">
                                    {{ $invoice->invoice_number ?? ('INV-' . $invoice->id) }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                            </div>

                            <p class="text-xs text-[#645D56]">
                                <i class="fas fa-calendar-alt mr-1 text-[#8C827A]"></i>
                                Due: {{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d, Y') : 'Due upon receipt' }}
                            </p>

                            <div class="text-lg font-bold text-[#D96B43] pt-1">
                                रू {{ number_format($invAmount, 2) }}
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Khalti Button -->
                            <button type="button" 
                                    onclick="payWithKhalti({{ $invoice->id }}, {{ $invAmount }})" 
                                    id="khalti-btn-{{ $invoice->id }}"
                                    class="btn-khalti inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold shadow-sm transition">
                                <i class="fas fa-wallet text-sm text-white"></i>
                                <span class="text-white">Pay with Khalti</span>
                            </button>

                            <!-- eSewa Button -->
                            <form action="{{ route('payment.esewa.init') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                                <input type="hidden" name="amount" value="{{ $invAmount }}">
                                <button type="submit" 
                                        class="btn-esewa inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold shadow-sm transition">
                                    <i class="fas fa-money-bill-wave text-sm text-white"></i>
                                    <span class="text-white">Pay with eSewa</span>
                                </button>
                            </form>

                            <!-- Stripe (Card / International) Button -->
                            <form action="{{ route('payment.stripe.init') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                                <input type="hidden" name="amount" value="{{ $invAmount }}">
                                <button type="submit" 
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold shadow-sm transition bg-slate-900 hover:bg-slate-800 text-white border border-slate-700/60">
                                    <i class="fas fa-credit-card text-xs text-indigo-400"></i>
                                    <span>Card (Stripe)</span>
                                </button>
                            </form>

                            <!-- Direct Bank Transfer Button -->
                            <button type="button" 
                                    onclick="openBankTransferModal({{ $invoice->id }}, '{{ $invoice->invoice_number ?? ('INV-' . $invoice->id) }}', {{ $invAmount }})" 
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold shadow-sm transition bg-emerald-700 hover:bg-emerald-800 text-white border border-emerald-600">
                                <i class="fas fa-building-columns text-xs text-emerald-200"></i>
                                <span>Direct Bank</span>
                            </button>

                            <!-- Interbank Banking QR Button -->
                            <button type="button" 
                                    onclick="openBankingQrModal({{ $invoice->id }}, '{{ $invoice->invoice_number ?? ('INV-' . $invoice->id) }}', {{ $invAmount }})" 
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold shadow-sm transition bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-500">
                                <i class="fas fa-qrcode text-xs text-indigo-200"></i>
                                <span>Banking QR</span>
                            </button>

                            <!-- View Details -->
                            <a href="{{ route('invoices.show', $invoice->id) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-semibold text-[#645D56] hover:text-[#24201D] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition">
                                <i class="fas fa-eye text-xs"></i>
                                <span>Details</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-2xl">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-[#24201D]">All Invoices Settled</h3>
                        <p class="text-xs text-[#645D56]">You do not have any pending invoices requiring settlement right now.</p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#D96B43] hover:bg-[#C35832] text-white text-xs font-semibold shadow-sm transition">
                            <i class="fas fa-arrow-left"></i>
                            <span>Return to Dashboard</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ==================== DIRECT BANK TRANSFER MODAL ==================== -->
<div id="bankTransferModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-4 sm:p-6 flex items-center justify-center">
    <div class="relative w-full max-w-2xl bg-white border border-slate-200/80 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-building-columns"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Direct Commercial Bank Transfer</h3>
                    <p class="text-xs text-slate-500">Corporate wire, counter deposit, or ConnectIPS interbank transfer</p>
                </div>
            </div>
            <button type="button" onclick="closeBankTransferModal()" class="w-8 h-8 rounded-lg hover:bg-slate-200/60 text-slate-400 hover:text-slate-600 flex items-center justify-center transition">
                <i class="fas fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('payment.bank.submit') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="invoice_id" id="bankModalInvoiceId">

            <!-- Invoice & Amount Capsule -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Settling Invoice</span>
                    <span class="font-mono text-xs font-bold text-slate-900" id="bankModalInvoiceNumber">INV-202611-0000</span>
                </div>
                <div class="text-right">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Payable Amount</span>
                    <span class="text-base font-bold text-emerald-700" id="bankModalAmount">रू 0.00</span>
                </div>
            </div>

            <!-- Official Bank Accounts List -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                    <i class="fas fa-landmark text-emerald-600 mr-1"></i> Official KTM-WDC Bank Accounts
                </label>
                <div class="space-y-2">
                    <!-- Account 1: Nabil Bank -->
                    <div class="p-3 rounded-xl border border-slate-200 bg-white hover:border-emerald-300 transition flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-slate-900">Nabil Bank Ltd.</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">Corporate Primary</span>
                            </div>
                            <div class="font-mono text-xs text-slate-700 font-bold mt-0.5">A/C: 01100175023910</div>
                            <div class="text-[10px] text-slate-400">Durbar Marg Main Branch | SWIFT: NARBNPKA</div>
                        </div>
                        <button type="button" onclick="copyAccountNo('01100175023910', this)" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 text-[11px] font-semibold transition border border-slate-200">
                            <i class="fas fa-copy mr-1"></i> Copy A/C
                        </button>
                    </div>

                    <!-- Account 2: Global IME Bank -->
                    <div class="p-3 rounded-xl border border-slate-200 bg-white hover:border-emerald-300 transition flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-slate-900">Global IME Bank Ltd.</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700">Cargo Hub</span>
                            </div>
                            <div class="font-mono text-xs text-slate-700 font-bold mt-0.5">A/C: 00101010045623</div>
                            <div class="text-[10px] text-slate-400">New Road Branch | SWIFT: GLBLNPKA</div>
                        </div>
                        <button type="button" onclick="copyAccountNo('00101010045623', this)" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 text-[11px] font-semibold transition border border-slate-200">
                            <i class="fas fa-copy mr-1"></i> Copy A/C
                        </button>
                    </div>

                    <!-- Account 3: NIC Asia Bank -->
                    <div class="p-3 rounded-xl border border-slate-200 bg-white hover:border-emerald-300 transition flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-slate-900">NIC Asia Bank Ltd.</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700">Corporate Branch</span>
                            </div>
                            <div class="font-mono text-xs text-slate-700 font-bold mt-0.5">A/C: 109200548123901</div>
                            <div class="text-[10px] text-slate-400">Thamel Corporate Branch | SWIFT: NICAIPKA</div>
                        </div>
                        <button type="button" onclick="copyAccountNo('109200548123901', this)" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 text-[11px] font-semibold transition border border-slate-200">
                            <i class="fas fa-copy mr-1"></i> Copy A/C
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transfer Input Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target KTM-WDC Bank <span class="text-rose-500">*</span></label>
                    <select name="bank_name" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="Nabil Bank Ltd.">Nabil Bank Ltd. (A/C: 01100175023910)</option>
                        <option value="Global IME Bank Ltd.">Global IME Bank Ltd. (A/C: 00101010045623)</option>
                        <option value="NIC Asia Bank Ltd.">NIC Asia Bank Ltd. (A/C: 109200548123901)</option>
                        <option value="Other Bank / ConnectIPS">Other Bank / ConnectIPS Interbank</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Depositor / Sender Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="depositor_name" required placeholder="Name on your bank account" value="{{ auth()->user()->name }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Bank UTR / Reference / Voucher No. <span class="text-rose-500">*</span></label>
                    <input type="text" name="reference_number" required placeholder="e.g. NBL-98124501 or UTR123456" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 font-mono bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Transfer Date</label>
                    <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Deposit Voucher Upload -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Upload Bank Deposit Slip / Transfer Screenshot (Optional)</label>
                <input type="file" name="voucher_file" accept="image/*,application/pdf" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                <p class="text-[10.5px] text-slate-400 mt-1">Accepted formats: JPG, PNG, PDF (Max 5MB).</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeBankTransferModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Cancel
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-sm transition">
                    <i class="fas fa-check"></i>
                    <span>Submit Bank Deposit for Verification</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== BANKING QR (FONEPAY / NEPALPAY) MODAL ==================== -->
<div id="bankingQrModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-4 sm:p-6 flex items-center justify-center">
    <div class="relative w-full max-w-lg bg-white border border-slate-200/80 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-indigo-50/70 border-b border-indigo-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shadow-xs">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Direct Banking QR Payment</h3>
                    <p class="text-xs text-slate-500">Scan via any Nepal Mobile Banking App (Fonepay / NepalPay)</p>
                </div>
            </div>
            <button type="button" onclick="closeBankingQrModal()" class="w-8 h-8 rounded-lg hover:bg-slate-200/60 text-slate-400 hover:text-slate-600 flex items-center justify-center transition">
                <i class="fas fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="p-6 space-y-4 text-center">
            <!-- Dynamic QR Code Card -->
            <div class="mx-auto w-64 p-4 rounded-2xl border-2 border-indigo-200 bg-white shadow-md space-y-2">
                <div class="flex items-center justify-center gap-2 pb-1 border-b border-slate-100 text-[11px] font-bold text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Fonepay &amp; NepalPay Interbank QR</span>
                </div>

                <!-- Realistic Universal QR Code Stand -->
                <div class="bg-white p-2 flex items-center justify-center rounded-xl">
                    <svg viewBox="0 0 200 200" width="180" height="180" class="mx-auto">
                        <!-- Corner Markers -->
                        <rect x="10" y="10" width="45" height="45" rx="6" fill="#1e1b4b" />
                        <rect x="20" y="20" width="25" height="25" rx="3" fill="#ffffff" />
                        <rect x="27" y="27" width="11" height="11" fill="#4338ca" />

                        <rect x="145" y="10" width="45" height="45" rx="6" fill="#1e1b4b" />
                        <rect x="155" y="20" width="25" height="25" rx="3" fill="#ffffff" />
                        <rect x="162" y="27" width="11" height="11" fill="#4338ca" />

                        <rect x="10" y="145" width="45" height="45" rx="6" fill="#1e1b4b" />
                        <rect x="20" y="155" width="25" height="25" rx="3" fill="#ffffff" />
                        <rect x="27" y="162" width="11" height="11" fill="#4338ca" />

                        <!-- Data Modules Matrix -->
                        <rect x="70" y="15" width="12" height="12" rx="2" fill="#312e81" />
                        <rect x="90" y="25" width="12" height="12" rx="2" fill="#312e81" />
                        <rect x="110" y="15" width="12" height="12" rx="2" fill="#312e81" />

                        <rect x="20" y="75" width="12" height="12" rx="2" fill="#312e81" />
                        <rect x="40" y="90" width="12" height="12" rx="2" fill="#312e81" />
                        <rect x="20" y="110" width="12" height="12" rx="2" fill="#312e81" />

                        <rect x="145" y="75" width="12" height="12" rx="2" fill="#312e81" />
                        <rect x="165" y="90" width="12" height="12" rx="2" fill="#312e81" />
                        <rect x="150" y="110" width="12" height="12" rx="2" fill="#312e81" />

                        <rect x="75" y="145" width="12" height="12" rx="2" fill="#312e81" />
                        <rect x="95" y="165" width="12" height="12" rx="2" fill="#312e81" />
                        <rect x="120" y="150" width="12" height="12" rx="2" fill="#312e81" />

                        <!-- Center Logo Badge -->
                        <circle cx="100" cy="100" r="24" fill="#ffffff" stroke="#4f46e5" stroke-width="2" />
                        <text x="100" y="105" text-anchor="middle" font-family="'Newsreader', Georgia, serif" font-weight="bold" font-size="14" fill="#ea580c">KTM</text>
                    </svg>
                </div>

                <div class="pt-1 text-[11px] text-slate-500 font-semibold">
                    Merchant: <span class="text-slate-900 font-bold">KTM-WDC LOGISTICS PVT LTD</span><br>
                    PAN: <span class="font-mono text-slate-700">609823412</span>
                </div>
            </div>

            <!-- Amount readout -->
            <div class="text-center">
                <span class="text-xs text-slate-500 block">Verified Amount to Pay</span>
                <span class="text-xl font-extrabold text-indigo-700 font-serif" id="qrModalAmount">रू 0.00</span>
                <span class="text-[11px] text-slate-400 font-mono block mt-0.5" id="qrModalInvoiceNumber">INV-0000</span>
            </div>

            <!-- Supported Networks Chips -->
            <div class="flex flex-wrap items-center justify-center gap-1.5 text-[10px] text-slate-600 font-medium">
                <span class="px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200">Nabil Smart</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200">Global Smart+</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200">MoBank</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200">connectIPS</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200">All 20+ Banks</span>
            </div>

            <!-- Submission Form -->
            <form action="{{ route('payment.banking-qr.submit') }}" method="POST" enctype="multipart/form-data" class="pt-3 border-t border-slate-100 text-left space-y-3">
                @csrf
                <input type="hidden" name="invoice_id" id="qrModalInvoiceId">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Bank Transaction Reference / RRN (Retrieved from Mobile App) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="rrn_code" required placeholder="e.g. 12-digit RRN: 981240129482" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 font-mono bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Your Mobile Banking App</label>
                        <input type="text" name="sender_bank" placeholder="e.g. Global Smart, Nabil Smart" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Screenshot (Optional)</label>
                        <input type="file" name="voucher_file" accept="image/*,application/pdf" class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeBankingQrModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition">
                        <i class="fas fa-check"></i>
                        <span>Confirm QR Settlement</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAlert(message, type = 'error') {
    const alertBox = document.getElementById('paymentAlert');
    if (!alertBox) return;
    
    alertBox.className = type === 'error' 
        ? 'rounded-xl p-4 bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-3 transition-all duration-200' 
        : 'rounded-xl p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 transition-all duration-200';
    
    const icon = type === 'error' ? 'fa-exclamation-circle text-red-500' : 'fa-check-circle text-emerald-500';
    alertBox.innerHTML = `<i class="fas ${icon} text-base"></i><span>${message}</span>`;
    alertBox.classList.remove('hidden');
    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function openBankTransferModal(invoiceId, invoiceNumber, amount) {
    document.getElementById('bankModalInvoiceId').value = invoiceId;
    document.getElementById('bankModalInvoiceNumber').textContent = invoiceNumber;
    document.getElementById('bankModalAmount').textContent = 'रू ' + Number(amount).toLocaleString('en-IN', {minimumFractionDigits: 2});
    
    const modal = document.getElementById('bankTransferModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeBankTransferModal() {
    const modal = document.getElementById('bankTransferModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function copyAccountNo(accountNumber, button) {
    navigator.clipboard.writeText(accountNumber).then(() => {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-1 text-emerald-600"></i> Copied!';
        button.classList.add('bg-emerald-100', 'text-emerald-800');
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('bg-emerald-100', 'text-emerald-800');
        }, 2000);
    });
}

function openBankingQrModal(invoiceId, invoiceNumber, amount) {
    document.getElementById('qrModalInvoiceId').value = invoiceId;
    document.getElementById('qrModalInvoiceNumber').textContent = invoiceNumber;
    document.getElementById('qrModalAmount').textContent = 'रू ' + Number(amount).toLocaleString('en-IN', {minimumFractionDigits: 2});
    
    const modal = document.getElementById('bankingQrModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeBankingQrModal() {
    const modal = document.getElementById('bankingQrModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function payWithKhalti(invoiceId, amount) {
    const btn = document.getElementById('khalti-btn-' + invoiceId);
    if (!btn) return;

    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Initializing...';

    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = tokenMeta ? tokenMeta.content : '';

    fetch('{{ route("payment.khalti.init") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            invoice_id: invoiceId,
            amount: amount
        })
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Payment initiation failed with server error.');
        }
        return data;
    })
    .then(data => {
        if (data.success && data.payment_url) {
            window.location.href = data.payment_url;
        } else {
            showAlert(data.message || 'Unable to initiate Khalti checkout. Please try again.');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Khalti checkout error:', error);
        showAlert(error.message || 'Network error during checkout initialization. Please verify your connection.');
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
}
</script>
@endsection