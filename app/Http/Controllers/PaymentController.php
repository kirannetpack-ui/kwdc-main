<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Services\PaymentService;
use App\Services\AdminEmailService;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $adminEmailService;

    public function __construct(PaymentService $paymentService, AdminEmailService $adminEmailService)
    {
        $this->middleware('auth');
        $this->paymentService = $paymentService;
        $this->adminEmailService = $adminEmailService;
    }

    /**
     * Show payment page
     */
    public function index()
    {
        $userId = auth()->id();
        $invoices = Invoice::where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('client_id', $userId)
                    ->orWhereHas('warehouseRequest', function ($wr) use ($userId) {
                        $wr->where('client_id', $userId);
                    });
            })
            ->where(function ($query) {
                $query->where('payment_status', 'unpaid')
                    ->orWhere(function ($q) {
                        $q->whereNull('payment_status')
                          ->where('status', '!=', 'paid');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('payment.index', compact('invoices'));
    }

    /**
     * Initialize Khalti payment
     */
    public function khaltiInit(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'nullable|numeric|min:1',
        ]);

        $invoice = $this->payableInvoice((int) $request->invoice_id);
        $amount = $this->invoicePayableAmount($invoice);

        if ($request->filled('amount') && round((float) $request->amount, 2) !== $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount does not match the invoice total.',
            ], 422);
        }
        
        $result = $this->paymentService->initiateKhaltiPayment(
            $amount,
            $invoice->invoice_number,
            auth()->id(),
            $invoice->id
        );

        if ($result['success']) {
            $this->retirePendingTransactions($invoice, 'khalti');

            // Store transaction
            Transaction::create([
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id(),
                'amount' => $amount,
                'payment_method' => 'khalti',
                'transaction_id' => $result['data']['pidx'],
                'status' => 'pending',
                'payment_details' => $result['data'],
            ]);

            return response()->json([
                'success' => true,
                'payment_url' => $result['data']['payment_url'],
                'pidx' => $result['data']['pidx'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Payment initiation failed',
        ], 500);
    }

    /**
     * Verify Khalti payment
     */
    public function khaltiVerify(Request $request)
    {
        $pidx = trim((string) $request->input('pidx'));

        if (!$pidx) {
            return redirect()->route('payment.failure')
                ->with('error', 'Invalid payment request.');
        }

        $transaction = Transaction::where('transaction_id', $pidx)
            ->where('user_id', auth()->id())
            ->where('payment_method', 'khalti')
            ->first();

        if (!$transaction) {
            return redirect()->route('payment.failure')
                ->with('error', 'Payment transaction was not found.');
        }

        if ($transaction->isCompleted()) {
            return redirect()->route('payment.success')
                ->with('success', 'Payment already completed.');
        }

        if (!$transaction->isPending()) {
            return redirect()->route('payment.failure')
                ->with('error', 'Payment transaction is no longer payable.');
        }

        $result = $this->paymentService->verifyKhaltiPayment($pidx);

        if ($result['success'] && $result['status'] === 'Completed') {
            $providerAmount = ((float) Arr::get($result, 'data.total_amount', 0)) / 100;
            if ($providerAmount && round($providerAmount, 2) !== round((float) $transaction->amount, 2)) {
                $transaction->markAsFailed('Provider amount did not match the invoice transaction amount.');

                return redirect()->route('payment.failure')
                    ->with('error', 'Payment amount mismatch. Please contact support.');
            }

            $invoice = Invoice::where('id', $transaction->invoice_id)
                ->where('payment_status', 'unpaid')
                ->first();

            if (!$invoice) {
                $transaction->markAsFailed('Invoice is missing or no longer payable.');

                return redirect()->route('payment.failure')
                    ->with('error', 'Payment invoice is no longer payable.');
            }

            $this->completeVerifiedTransaction($transaction, $invoice, 'khalti', $result['data']);
            
            return redirect()->route('payment.success')
                ->with('success', 'Payment completed successfully!');
        }

        return redirect()->route('payment.failure')
            ->with('error', 'Payment verification failed. Please contact support.');
    }

    /**
     * Send payment receipt to customer
     */
    private function sendReceipt($transaction, $invoice)
    {
        try {
            \Mail::send('emails.payment-receipt', [
                'transaction' => $transaction,
                'invoice' => $invoice,
            ], function ($message) use ($transaction, $invoice) {
                $message->to($transaction->user->email, $transaction->user->name)
                        ->subject('Payment Receipt - ' . $invoice->invoice_number);
            });
        } catch (\Exception $e) {
            \Log::error('Receipt email failed: ' . $e->getMessage());
        }
    }

    /**
     * eSewa Payment Initiation
     */
    public function esewaInit(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'nullable|numeric|min:1',
        ]);

        $invoice = $this->payableInvoice((int) $request->invoice_id);
        $amount = $this->invoicePayableAmount($invoice);

        if ($request->filled('amount') && round((float) $request->amount, 2) !== $amount) {
            return redirect()->back()
                ->with('error', 'Payment amount does not match the invoice total.');
        }
        
        $result = $this->paymentService->initiateEsewaPayment(
            $amount,
            $invoice->invoice_number,
            $invoice->id
        );

        if ($result['success']) {
            $this->retirePendingTransactions($invoice, 'esewa');

            // Store transaction
            Transaction::create([
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id(),
                'amount' => $amount,
                'payment_method' => 'esewa',
                'transaction_id' => $result['pid'],
                'status' => 'pending',
                'payment_details' => [
                    'payment_url' => $result['url'],
                    'pid' => $result['pid'],
                ],
            ]);

            return redirect($result['url']);
        }

        return redirect()->back()
            ->with('error', 'Payment initiation failed. Please try again.');
    }

    /**
     * eSewa Success Callback
     */
    public function esewaSuccess(Request $request)
    {
        $pid = trim((string) $request->input('pid'));
        $refId = trim((string) $request->input('refId'));

        if (!$pid || !$refId) {
            return redirect()->route('payment.failure')
                ->with('error', 'Invalid payment request.');
        }
        
        $transaction = Transaction::where('transaction_id', $pid)
            ->where('user_id', auth()->id())
            ->where('payment_method', 'esewa')
            ->first();
        
        if (!$transaction) {
            return redirect()->route('payment.failure')
                ->with('error', 'Payment transaction was not found.');
        }

        if ($transaction->isCompleted()) {
            return redirect()->route('payment.success')
                ->with('success', 'Payment already completed.');
        }

        if (!$transaction->isPending()) {
            return redirect()->route('payment.failure')
                ->with('error', 'Payment transaction is no longer payable.');
        }

        $verification = $this->paymentService->verifyEsewaPayment(
            $pid,
            $refId,
            (float) $transaction->amount
        );

        if ($verification['success'] ?? false) {
            $invoice = Invoice::where('id', $transaction->invoice_id)
                ->where('payment_status', 'unpaid')
                ->first();

            if (!$invoice) {
                $transaction->markAsFailed('Invoice is missing or no longer payable.');

                return redirect()->route('payment.failure')
                    ->with('error', 'Payment invoice is no longer payable.');
            }

            $this->completeVerifiedTransaction($transaction, $invoice, 'esewa', $verification['data'] ?? [
                'refId' => $refId,
                'pid' => $pid,
            ]);

            return redirect()->route('payment.success')
                ->with('success', 'Payment completed successfully!');
        }

        $transaction->markAsFailed($verification['message'] ?? 'eSewa verification failed.');

        return redirect()->route('payment.failure')
            ->with('error', 'Payment verification failed. Please contact support.');
    }

    /**
     * eSewa Failure Callback
     */
    public function esewaFailure(Request $request)
    {
        return redirect()->route('payment.failure')
            ->with('error', 'Payment failed. Please try again.');
    }

    /**
     * Stripe Payment Initiation
     */
    public function stripeInit(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'nullable|numeric|min:1',
        ]);

        $invoice = $this->payableInvoice((int) $request->invoice_id);
        $amount = $this->invoicePayableAmount($invoice);

        if ($request->filled('amount') && round((float) $request->amount, 2) !== $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount does not match the invoice total.',
            ], 422);
        }

        $result = $this->paymentService->initiateStripePayment(
            $amount,
            $invoice->invoice_number,
            auth()->id(),
            $invoice->id
        );

        if ($result['success']) {
            $this->retirePendingTransactions($invoice, 'stripe');

            Transaction::create([
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id(),
                'amount' => $amount,
                'payment_method' => 'stripe',
                'transaction_id' => $result['session_id'],
                'status' => 'pending',
                'payment_details' => $result['data'],
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'payment_url' => $result['payment_url'],
                    'session_id' => $result['session_id'],
                ]);
            }

            return redirect($result['payment_url']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Stripe payment initiation failed.',
            ], 500);
        }

        return redirect()->back()->with('error', $result['message'] ?? 'Stripe payment initiation failed.');
    }

    /**
     * Stripe Success Callback
     */
    public function stripeSuccess(Request $request)
    {
        $sessionId = trim((string) $request->input('session_id'));

        if (!$sessionId) {
            return redirect()->route('payment.failure')
                ->with('error', 'Invalid payment session.');
        }

        $transaction = Transaction::where('transaction_id', $sessionId)
            ->where('user_id', auth()->id())
            ->where('payment_method', 'stripe')
            ->first();

        if (!$transaction) {
            return redirect()->route('payment.failure')
                ->with('error', 'Payment transaction was not found.');
        }

        if ($transaction->isCompleted()) {
            return redirect()->route('payment.success')
                ->with('success', 'Payment already completed.');
        }

        $verification = $this->paymentService->verifyStripePayment($sessionId);

        if ($verification['success'] ?? false) {
            $invoice = Invoice::where('id', $transaction->invoice_id)
                ->where('payment_status', 'unpaid')
                ->first();

            if (!$invoice) {
                $transaction->markAsFailed('Invoice is missing or no longer payable.');

                return redirect()->route('payment.failure')
                    ->with('error', 'Payment invoice is no longer payable.');
            }

            $this->completeVerifiedTransaction($transaction, $invoice, 'stripe', $verification['data'] ?? []);

            return redirect()->route('payment.success')
                ->with('success', 'Payment via Card (Stripe) completed successfully!');
        }

        $transaction->markAsFailed($verification['message'] ?? 'Stripe verification failed.');

        return redirect()->route('payment.failure')
            ->with('error', 'Payment verification failed. Please contact support.');
    }

    /**
     * Stripe Cancel Callback
     */
    public function stripeCancel(Request $request)
    {
        return redirect()->route('payment.failure')
            ->with('error', 'Card payment was cancelled.');
    }

    /**
     * Success Page
     */
    public function success()
    {
        return view('payment.success');
    }

    /**
     * Failure Page
     */
    public function failure()
    {
        return view('payment.failure');
    }

    /**
     * Get payment history
     */
    public function history()
    {
        $transactions = Transaction::where('user_id', auth()->id())
            ->with('invoice')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('payment.history', compact('transactions'));
    }

    /**
     * Submit Direct Bank Transfer
     */
    public function bankTransferSubmit(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'bank_name' => 'required|string|max:100',
            'depositor_name' => 'required|string|max:150',
            'reference_number' => 'required|string|max:100',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'voucher_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $invoice = $this->payableInvoice((int) $request->invoice_id);
        $amount = $this->invoicePayableAmount($invoice);

        $voucherPath = null;
        if ($request->hasFile('voucher_file')) {
            $voucherPath = $request->file('voucher_file')->store('payment-vouchers', 'public');
        }

        $transactionId = 'BANK-' . strtoupper(substr(uniqid(), -6)) . '-' . preg_replace('/[^A-Za-z0-9_-]/', '', $request->reference_number);

        $transaction = Transaction::create([
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'amount' => $amount,
            'tax' => 0,
            'payment_method' => 'bank_transfer',
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'notes' => $request->notes ?: ('Direct bank transfer via ' . $request->bank_name),
            'payment_details' => [
                'bank_name' => $request->bank_name,
                'depositor_name' => $request->depositor_name,
                'reference_number' => $request->reference_number,
                'transfer_date' => $request->transfer_date ?: now()->toDateString(),
                'voucher_path' => $voucherPath,
                'submitted_at' => now()->toIso8601String(),
            ],
        ]);

        try {
            $this->adminEmailService->notifyAdmin(
                '🏛️ Direct Bank Deposit Submitted: Invoice #' . $invoice->invoice_number,
                "A direct bank transfer of NPR " . number_format($amount, 2) . " via {$request->bank_name} (Ref: {$request->reference_number}) was submitted by " . auth()->user()->name . " and is awaiting finance reconciliation."
            );
        } catch (\Throwable $e) {
            \Log::warning('Bank transfer admin notification failed: ' . $e->getMessage());
        }

        return redirect()->route('payment.history')
            ->with('success', "Direct bank transfer request submitted successfully (Ref: {$request->reference_number}). Our finance department will reconcile and confirm receipt.");
    }

    /**
     * Submit Banking QR (Fonepay / NepalPay) Payment
     */
    public function bankingQrSubmit(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'rrn_code' => 'required|string|max:100',
            'sender_bank' => 'nullable|string|max:100',
            'voucher_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $invoice = $this->payableInvoice((int) $request->invoice_id);
        $amount = $this->invoicePayableAmount($invoice);

        $voucherPath = null;
        if ($request->hasFile('voucher_file')) {
            $voucherPath = $request->file('voucher_file')->store('payment-vouchers', 'public');
        }

        $transactionId = 'QR-' . strtoupper(substr(uniqid(), -6)) . '-' . preg_replace('/[^A-Za-z0-9_-]/', '', $request->rrn_code);

        $transaction = Transaction::create([
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'amount' => $amount,
            'tax' => 0,
            'payment_method' => 'banking_qr',
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'notes' => 'Banking QR payment via ' . ($request->sender_bank ?: 'Mobile Banking'),
            'payment_details' => [
                'rrn_code' => $request->rrn_code,
                'sender_bank' => $request->sender_bank ?: 'Mobile Banking App',
                'voucher_path' => $voucherPath,
                'merchant' => config('payment.banking.qr.merchant_name'),
                'submitted_at' => now()->toIso8601String(),
            ],
        ]);

        try {
            $this->adminEmailService->notifyAdmin(
                '📱 Banking QR Payment Submitted: Invoice #' . $invoice->invoice_number,
                "A Banking QR (Fonepay/NepalPay) payment of NPR " . number_format($amount, 2) . " (RRN: {$request->rrn_code}) was submitted by " . auth()->user()->name . "."
            );
        } catch (\Throwable $e) {
            \Log::warning('QR transfer admin notification failed: ' . $e->getMessage());
        }

        return redirect()->route('payment.history')
            ->with('success', "Banking QR payment recorded (RRN/Ref: {$request->rrn_code}). Reconciling against the interbank settlement ledger.");
    }

    /**
     * Admin: View Bank & QR Transfers
     */
    public function adminBankTransfers(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || ($user->is_admin ?? false) || $user->role === 'admin'), 403);

        $query = Transaction::with(['invoice', 'user'])
            ->whereIn('payment_method', ['bank_transfer', 'banking_qr']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('admin.payments.bank-transfers', compact('transactions'));
    }

    /**
     * Admin: Approve Bank / QR Transfer
     */
    public function adminApproveBankTransfer(Transaction $transaction)
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || ($user->is_admin ?? false) || $user->role === 'admin'), 403);

        $invoice = Invoice::find($transaction->invoice_id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Associated invoice not found.');
        }

        $this->completeVerifiedTransaction($transaction, $invoice, $transaction->payment_method, $transaction->payment_details ?? []);

        if ($invoice->client && !empty($invoice->client->phone)) {
            try {
                app(\App\Services\SmsService::class)->notifyPaymentCleared(
                    $invoice->client->phone,
                    $invoice->invoice_number,
                    (float) $transaction->amount
                );
            } catch (\Throwable $e) {
                \Log::warning('Payment cleared SMS notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Transaction {$transaction->transaction_id} approved. Invoice #{$invoice->invoice_number} is now Paid!");
    }

    /**
     * Admin: Reject Bank / QR Transfer
     */
    public function adminRejectBankTransfer(Request $request, Transaction $transaction)
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || ($user->is_admin ?? false) || $user->role === 'admin'), 403);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $transaction->markAsFailed($request->input('rejection_reason'));

        return redirect()->back()->with('success', "Transaction {$transaction->transaction_id} marked as rejected.");
    }

    private function payableInvoice(int $invoiceId): Invoice
    {
        return Invoice::where('id', $invoiceId)
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhere('client_id', auth()->id());
            })
            ->where('payment_status', 'unpaid')
            ->firstOrFail();
    }

    private function invoicePayableAmount(Invoice $invoice): float
    {
        $amount = $invoice->grand_total ?: ($invoice->amount ?? 0);

        return round((float) $amount, 2);
    }

    private function completeVerifiedTransaction(Transaction $transaction, Invoice $invoice, string $method, array $details): void
    {
        $transaction->markAsCompleted();
        $transaction->payment_details = $details;
        $transaction->save();

        $invoice->markAsPaid($method);

        $this->adminEmailService->notifyPaymentReceived($transaction, $invoice);
        $this->sendReceipt($transaction, $invoice);
    }

    private function retirePendingTransactions(Invoice $invoice, string $method): void
    {
        Transaction::where('invoice_id', $invoice->id)
            ->where('user_id', auth()->id())
            ->where('payment_method', $method)
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'notes' => 'Retired after a newer payment session was created.',
                'updated_at' => now(),
            ]);
    }
}
