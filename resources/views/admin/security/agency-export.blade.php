<!DOCTYPE html>
<html>
<head>
    <title>Security Agency Profile</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; border-bottom: 3px solid #f59e0b; padding-bottom: 20px; }
        .section { margin: 20px 0; }
        .section h2 { background: #f3f4f6; padding: 10px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f59e0b; color: white; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
        .badge-approved { background: #10b981; color: white; }
        .badge-pending { background: #f59e0b; color: white; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #ddd; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KTM-WDC Security Agency Profile</h1>
        <p>Generated on {{ now()->format('F j, Y, g:i a') }}</p>
    </div>

    <div class="section">
        <h2>Agency Information</h2>
        <table>
            <tr><th>Name</th><td>{{ $agency->agency_name }}</td></tr>
            <tr><th>Registration #</th><td>{{ $agency->registration_number }}</td></tr>
            <tr><th>License #</th><td>{{ $agency->license_number }}</td></tr>
            <tr><th>PAN/VAT</th><td>{{ $agency->pan_vat_number }}</td></tr>
            <tr><th>Established</th><td>{{ $agency->year_established }}</td></tr>
            <tr><th>Status</th><td><span class="badge badge-{{ $agency->status }}">{{ ucfirst($agency->status) }}</span></td></tr>
            <tr><th>Address</th><td>{{ $agency->address }}</td></tr>
            <tr><th>Phone</th><td>{{ $agency->phone }}</td></tr>
            <tr><th>Emergency</th><td>{{ $agency->emergency_phone }}</td></tr>
            <tr><th>Email</th><td>{{ $agency->email }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Services Offered</h2>
        <p>{{ $agency->services_offered }}</p>
    </div>

    <div class="section">
        <h2>Security Personnel ({{ $agency->personnel->count() }})</h2>
        <table>
            <thead>
                <tr><th>Name</th><th>Position</th><th>Phone</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($agency->personnel as $person)
                <tr>
                    <td>{{ $person->name }}</td>
                    <td>{{ $person->position }}</td>
                    <td>{{ $person->phone }}</td>
                    <td>{{ ucfirst($person->status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Security Goods & Equipment</h2>
        <table>
            <thead>
                <tr><th>Item</th><th>Category</th><th>Quantity</th><th>Price (NPR)</th></tr>
            </thead>
            <tbody>
                @foreach($agency->goods as $good)
                <tr>
                    <td>{{ $good->item_name }}</td>
                    <td>{{ $good->category }}</td>
                    <td>{{ $good->quantity_available }}</td>
                    <td>रु {{ number_format($good->unit_price) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Rate Card</h2>
        <table>
            <thead>
                <tr><th>Service</th><th>Rate Type</th><th>Rate (NPR)</th></tr>
            </thead>
            <tbody>
                @foreach($agency->rates as $rate)
                <tr>
                    <td>{{ $rate->service_type }}</td>
                    <td>{{ $rate->rate_type }}</td>
                    <td>रु {{ number_format($rate->rate) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        This is a system-generated document. For verification, contact KTM-WDC Admin.<br>
        &copy; {{ date('Y') }} KTM-WDC. All rights reserved.
    </div>
</body>
</html>