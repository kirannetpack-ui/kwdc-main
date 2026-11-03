<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
            margin: 0;
            padding: 24px;
        }
        .header {
            border-bottom: 3px solid #b91c1c;
            margin-bottom: 24px;
            padding-bottom: 16px;
        }
        .company {
            color: #b91c1c;
            font-size: 22px;
            font-weight: bold;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 8px;
        }
        .meta {
            margin-top: 8px;
        }
        .columns {
            display: table;
            margin-bottom: 24px;
            width: 100%;
        }
        .column {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .label {
            color: #6b7280;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        table {
            border-collapse: collapse;
            margin-top: 12px;
            width: 100%;
        }
        th {
            background: #b91c1c;
            color: #ffffff;
            font-size: 11px;
            padding: 8px;
            text-align: left;
        }
        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px;
        }
        .right {
            text-align: right;
        }
        .totals {
            margin-left: auto;
            margin-top: 18px;
            width: 260px;
        }
        .totals td {
            border: 0;
            padding: 5px 0;
        }
        .grand-total {
            border-top: 2px solid #111827;
            font-size: 15px;
            font-weight: bold;
        }
        .footer {
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 10px;
            margin-top: 32px;
            padding-top: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">{{ $company['name'] }}</div>
        <div>{{ $company['address'] }} | {{ $company['phone'] }} | {{ $company['email'] }}</div>
        <div class="title">Tax Invoice {{ $invoice->invoice_number }}</div>
        <div class="meta">PAN: {{ $company['pan'] }} | Status: {{ ucfirst($invoice->payment_status) }}</div>
    </div>

    <div class="columns">
        <div class="column">
            <div class="label">Bill To</div>
            <strong>{{ $invoice->client->name ?? 'Client' }}</strong><br>
            {{ $invoice->client->email ?? '' }}<br>
            {{ $invoice->billing_address ?: 'Kathmandu, Nepal' }}
        </div>
        <div class="column right">
            <div class="label">Invoice Date</div>
            {{ optional($invoice->created_at)->format('F d, Y') ?? now()->format('F d, Y') }}<br>
            <div class="label" style="margin-top: 8px;">Due Date</div>
            {{ optional($invoice->payment_due_date)->format('F d, Y') ?? 'Due on receipt' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items ?? [] as $item)
                <tr>
                    <td>{{ $item['description'] ?? 'Service' }}</td>
                    <td class="right">{{ $item['quantity'] ?? 1 }}</td>
                    <td class="right">Rs {{ number_format((float) ($item['unit_price'] ?? 0), 2) }}</td>
                    <td class="right">Rs {{ number_format((float) ($item['total'] ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td>Warehouse and distribution services</td>
                    <td class="right">1</td>
                    <td class="right">Rs {{ number_format((float) $invoice->subtotal, 2) }}</td>
                    <td class="right">Rs {{ number_format((float) $invoice->subtotal, 2) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="right">Rs {{ number_format((float) $invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>VAT {{ number_format((float) $invoice->tax_rate, 2) }}%</td>
            <td class="right">Rs {{ number_format((float) $invoice->tax_amount, 2) }}</td>
        </tr>
        <tr class="grand-total">
            <td>Total Due</td>
            <td class="right">Rs {{ number_format((float) $invoice->grand_total, 2) }}</td>
        </tr>
    </table>

    @if($invoice->notes)
        <p><strong>Notes:</strong> {{ $invoice->notes }}</p>
    @endif

    <div class="footer">
        {{ $company['name'] }} | PAN {{ $company['pan'] }} | Thank you for choosing KTM-WDC.
    </div>
</body>
</html>
