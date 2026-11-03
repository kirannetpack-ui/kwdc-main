<!DOCTYPE html>
<html>
<head>
    <title>Box Label - {{ $box->batch_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .label {
            width: 300px;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 8px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #f59e0b;
        }
        .info {
            margin: 10px 0;
        }
        .info p {
            margin: 5px 0;
        }
        .qr-code {
            text-align: center;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .label {
                page-break-after: avoid;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body>
    <div class="label" style="{{ $box->isBanned() ? 'border: 3px solid #dc2626; background: #fff5f5;' : '' }}">
        <div class="header">
            <h2>KTM-WDC</h2>
            <p>Box Tracking Label</p>
            @if($box->isBanned())
            <div style="background: #dc2626; color: white; font-weight: bold; padding: 4px 8px; border-radius: 4px; margin-top: 6px; font-size: 11px;">
                🚨 QR CODE BANNED & REVOKED
            </div>
            @endif
        </div>
        
        <div class="info">
            <p><strong>Batch Number:</strong> {{ $box->batch_number }}</p>
            <p><strong>Box Number:</strong> {{ $box->box_number }}/{{ $box->total_boxes }}</p>
            <p><strong>Invoice:</strong> {{ $box->invoice_number }}</p>
            <p><strong>Shipper:</strong> {{ $box->shipper_name }}</p>
            <p><strong>Warehouse:</strong> {{ $box->warehouse->name ?? 'N/A' }}</p>
            <p><strong>Entry Date:</strong> {{ $box->entry_date->format('Y-m-d') }}</p>
            <p><strong>Status:</strong> {{ $box->isBanned() ? 'BANNED / REVOKED' : ucfirst($box->status) }}</p>
            @if($box->isBanned())
            <p style="color: #dc2626; font-size: 11px;"><strong>Ban Reason:</strong> {{ $box->ban_reason ?: 'Cargo hold / security impound.' }}</p>
            @endif
        </div>
        
        <div class="qr-code" id="qrcode">
            @if($box->isBanned())
            <div style="border: 2px dashed #dc2626; padding: 20px 10px; color: #dc2626; font-weight: bold; font-size: 14px;">
                VOID<br>CARGO RELEASE PROHIBITED
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p>{{ $box->isBanned() ? 'DO NOT SCAN - SECURITY REVOKED' : 'Scan QR code for tracking' }}</p>
            <p>www.ktm-wdc.com/track/{{ $box->id }}</p>
        </div>
    </div>
    
    <script>
        @if(!$box->isBanned())
        new QRCode(document.getElementById("qrcode"), {
            text: {{ json_encode($qrData) }},
            width: 150,
            height: 150
        });
        @endif
        
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>