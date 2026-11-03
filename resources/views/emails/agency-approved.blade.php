<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Agency Approved</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; }
        .header { border-bottom: 3px solid #10b981; padding-bottom: 20px; }
        .content { line-height: 1.6; }
        .success { background: #d1fae5; padding: 15px; border-left: 4px solid #10b981; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1 style="color:#10b981;">✅ Agency Approved!</h1></div>
        <div class="content">
            <div class="success">
                <strong>Congratulations, {{ $agency->agency_name }}!</strong><br>
                Your security agency has been approved by the KTM-WDC admin team.
            </div>
            <p>You can now:</p>
            <ul>
                <li>Log in to your dashboard and manage your services</li>
                <li>Add security personnel and equipment</li>
                <li>Receive security assignment requests</li>
                <li>Generate reports and invoices</li>
            </ul>
            <p>Visit your dashboard at: <a href="{{ config('app.url') }}/dashboard">{{ config('app.url') }}</a></p>
            <p>Thank you for partnering with KTM-WDC!</p>
        </div>
    </div>
</body>
</html>