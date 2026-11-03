@extends('emails.layout')

@section('content')
@php
    $clientName = $invoice->client->name ?? $invoice->user->name ?? 'Valued Client';
    $created = isset($invoice->created_at) && is_object($invoice->created_at) ? $invoice->created_at->format('M d, Y') : date('M d, Y');
    $dueDate = isset($invoice->payment_due_date) && is_object($invoice->payment_due_date) 
        ? $invoice->payment_due_date->format('M d, Y') 
        : (isset($invoice->due_date) && !empty($invoice->due_date) ? date('M d, Y', strtotime($invoice->due_date)) : 'Upon Receipt');
    $total = $invoice->grand_total ?? $invoice->total_amount ?? $invoice->amount ?? 0;
    $status = $invoice->payment_status ?? $invoice->status ?? 'Unpaid';
    $viewUrl = url('/client/invoices/' . ($invoice->id ?? '1'));
@endphp
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            📄
        </div>
        <h2 style="color: #1f2937; margin: 0 0 6px; font-size: 22px;">Invoice Notification</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">Namaste {{ $clientName }}, thank you for partnering with KTM-WDC Logistics.</p>
    </div>

    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Invoice Number:</td>
                <td style="padding: 7px 0; color: #1f2937; font-weight: 700; text-align: right; font-family: monospace;">{{ $invoice->invoice_number ?? 'INV-NEW' }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Billing Date:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $created }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Payment Due Date:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $dueDate }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Payment Status:</td>
                <td style="padding: 7px 0; text-align: right;">
                    <span style="display: inline-block; background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                        {{ ucfirst($status) }}
                    </span>
                </td>
            </tr>
            <tr style="border-top: 1px dashed #d1c7b7;">
                <td style="padding: 10px 0 4px; font-size: 15px; font-weight: 700; color: #1f2937;">Total Amount Due:</td>
                <td style="padding: 10px 0 4px; font-size: 16px; font-weight: 700; color: #d96b43; text-align: right;">NPR {{ number_format((float)$total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ $viewUrl }}" style="display: inline-block; background: #d96b43; color: white; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;">View & Pay Invoice Online →</a>
    </div>
</div>
@endsection