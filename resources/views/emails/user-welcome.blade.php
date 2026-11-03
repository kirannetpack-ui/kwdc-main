<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to KTM-WDC</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #f59e0b; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #1e3c72; font-size: 28px; margin: 0; }
        .header .sub { color: #6b7280; font-size: 16px; }
        .content { color: #374151; line-height: 1.6; }
        .content h2 { color: #1e3c72; font-size: 22px; margin-top: 0; }
        .highlight { background: #fef3c7; padding: 15px; border-radius: 6px; border-left: 4px solid #f59e0b; margin: 20px 0; }
        .services { list-style: none; padding: 0; }
        .services li { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .services li:last-child { border-bottom: none; }
        .footer { margin-top: 30px; text-align: center; color: #9ca3af; font-size: 14px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
        .btn { display: inline-block; background: #f59e0b; color: #fff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>KTM-WDC</h1>
            <div class="sub">Warehouse & Distribution Connect</div>
        </div>

        <div class="content">
            <h2>🙏 Namaste {{ $user->name }},</h2>
            <p>Welcome to <strong>KTM-WDC</strong> – Nepal’s premier logistics and warehouse platform.</p>

            <div class="highlight">
                <strong>🔑 Your account is currently <u>pending approval</u>.</strong><br>
                You will be able to log in and use all features as soon as our admin team verifies your registration.
            </div>

            <p>While you wait, explore the services we offer:</p>
            <ul class="services">
                <li>🚛 <strong>Dispatch Management</strong> – Real‑time tracking of shipments</li>
                <li>🏭 <strong>Warehouse Leasing</strong> – Find and manage warehouses</li>
                <li>📦 <strong>Stock & Box Tracking</strong> – QR‑based inventory</li>
                <li>📊 <strong>Analytics & Reports</strong> – Data‑driven insights</li>
                <li>📱 <strong>Mobile‑first Dashboard</strong> – Access on the go</li>
                <li>🔐 <strong>Secure Payments</strong> – Khalti, eSewa and more</li>
            </ul>

            <p>We will notify you via email once your account is approved.</p>
            <p>If you have any questions, feel free to reply to this email or contact our support team.</p>

            <p style="margin-top: 30px;">Thank you for choosing KTM-WDC!</p>
            <p>— The KTM-WDC Team</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} KTM-WDC. All rights reserved.<br>
            Kathmandu, Nepal
        </div>
    </div>
</body>
</html>