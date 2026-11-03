<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to KTM-WDC</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #f59e0b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .btn:hover {
            background: #d97706;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 KTM-WDC</h1>
            <p style="margin: 5px 0 0; opacity: 0.9;">Welcome to Warehousing & Distribution Platform</p>
        </div>
        <div class="content">
            <h2>Hello {{ $user->name }},</h2>
            <p>Welcome to <strong>KTM-WDC</strong>! Your account has been successfully created.</p>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Account Details:</strong></p>
                <p style="margin: 5px 0;">Email: {{ $user->email }}</p>
                <p style="margin: 5px 0;">Role: {{ ucfirst($user->role) }}</p>
                <p style="margin: 5px 0;">User Code: {{ $user->user_code }}</p>
            </div>
            
            <p>You can now:</p>
            <ul>
                <li>Search and rent warehouses</li>
                <li>Create dispatch orders</li>
                <li>Track your deliveries in real-time</li>
                <li>Manage your inventory</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/dashboard" class="btn">Go to Dashboard</a>
            </div>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} KTM-WDC . All rights reserved.</p>
        </div>
    </div>
</body>
</html>