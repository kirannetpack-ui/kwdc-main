<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to KTM-WDC</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #f59e0b; padding-bottom: 20px; }
        .header h1 { color: #1e3c72; }
        .content { line-height: 1.6; color: #374151; }
        .highlight { background: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; margin: 20px 0; }
        .footer { margin-top: 30px; text-align: center; color: #9ca3af; font-size: 14px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>KTM-WDC</h1>
            <p>Security Partner Program</p>
        </div>
        <div class="content">
            <h2>Welcome to the KTM-WDC Partner Network, {{ $agency->agency_name }}!</h2>
            <p>We are excited to have you on board as a security partner.</p>
            <div class="highlight">
                <strong>🔑 Your registration is currently under review.</strong><br>
                Our admin team will verify your credentials and notify you upon approval.
            </div>
            <p>With KTM-WDC, you can:</p>
            <ul>
                <li>List your security services and equipment</li>
                <li>Manage your personnel and assignments</li>
                <li>Collaborate with warehouses for security needs</li>
                <li>Access incident reporting and compliance tracking</li>
            </ul>
            <p>We will reach out to you within 24 hours. If you have any questions, reply to this email.</p>
            <p>— The KTM-WDC Team</p>
        </div>
        <div class="footer">&copy; {{ date('Y') }} KTM-WDC. All rights reserved.</div>
    </div>
</body>
</html>