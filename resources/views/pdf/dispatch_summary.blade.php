<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dispatch Summary - {{ $dispatch->invoice_no }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        .document-title {
            font-size: 18px;
            margin-top: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .info-box {
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            font-size: 11px;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 14px;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">KTM Warehouse & Distribution Center</div>
        <div class="document-title">DISPATCH SUMMARY</div>
        <div>Generated: {{ $generated_date }}</div>
    </div>
    
    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Dispatch ID</div>
            <div class="info-value">#{{ $dispatch->invoice_no }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Status</div>
            <div class="info-value">{{ ucfirst($dispatch->status) }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Total Distance</div>
            <div class="info-value">{{ number_format($dispatch->total_distance, 2) }} km</div>
        </div>
        <div class="info-box">
            <div class="info-label">Total Amount</div>
            <div class="info-value">रू {{ number_format($dispatch->total_price, 2) }}</div>
        </div>
    </div>
    
    <h3>Delivery Stops</h3>
    <table>
        <thead>
            <tr><th>Stop #</th><th>Location</th><th>Recipient</th><th>Phone</th></tr>
        </thead>
        <tbody>
            @foreach($stops as $index => $stop)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $stop->address }}</td>
                <td>{{ $stop->recipient_name }}</td>
                <td>{{ $stop->recipient_phone }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="qr-code">
        {{-- QR code for tracking --}}
        <img src="data:image/png;base64,{{ $dispatch->tracking_qr ?? '' }}" width="100">
        <br>
        <small>Scan to track delivery</small>
    </div>
    
    <div class="footer">
        <div>{{ $company['name'] }} | PAN: {{ $company['pan'] }}</div>
        <div>{{ $company['address'] }} | Tel: {{ $company['phone'] }} | Email: {{ $company['email'] }}</div>
        <div>Thank you for choosing KTM-WDC Logistics</div>
    </div>
</body>
</html>
