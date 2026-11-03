@extends('emails.layout')

@section('content')
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            💳
        </div>
        <h2 style="color: #1f2937; margin: 0 0 6px; font-size: 22px;">Payment Receipt</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">Thank you for your payment. Your transaction has been processed and confirmed.</p>
    </div>

    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Invoice Number:</td>
                <td style="padding: 8px 0; color: #1f2937; font-weight: 600; text-align: right;">{{ $invoice->invoice_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Transaction ID:</td>
                <td style="padding: 8px 0; color: #1f2937; font-family: monospace; font-weight: 600; text-align: right;">{{ $transaction->transaction_id ?? $transaction->id ?? 'TXN-'.time() }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Payment Gateway:</td>
                <td style="padding: 8px 0; color: #1f2937; font-weight: 600; text-align: right; text-transform: uppercase;">{{ $transaction->payment_method ?? 'Digital Gateway' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Date & Time:</td>
                <td style="padding: 8px 0; color: #1f2937; text-align: right;">{{ isset($transaction->created_at) && is_object($transaction->created_at) ? $transaction->created_at->format('M d, Y - h:i A') : date('M d, Y - h:i A') }}</td>
            </tr>
            <tr style="border-top: 1px dashed #d1c7b7;">
                <td style="padding: 12px 0 4px; font-size: 16px; font-weight: 700; color: #1f2937;">Total Paid:</td>
                <td style="padding: 12px 0 4px; font-size: 18px; font-weight: 700; color: #d96b43; text-align: right;">NPR {{ number_format($transaction->amount ?? $invoice->total_amount ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px;">
        <p style="margin: 0; color: #166534; font-size: 13px; line-height: 1.5;">
            <strong>✓ Status: Confirmed & Settled</strong><br>
            A formal VAT invoice and receipt voucher have been archived into your client ledger.
        </p>
    </div>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ url('/client/invoices') }}" style="display: inline-block; background: #d96b43; color: white; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;">View Invoices & Receipts →</a>
    </div>
</div>
@endsection
