<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KTM-WDC Admin Notification</title>
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
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .message-box {
            background: #f8f9fa;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .message-box p {
            margin: 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .data-table .label {
            font-weight: 600;
            color: #555;
            width: 40%;
        }
        .data-table .value {
            width: 60%;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
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
            padding: 10px 20px;
            background: #f59e0b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn:hover {
            background: #d97706;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🏢 KTM-WDC</h1>
            <p>Admin Notification</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <h2>Dear Admin,</h2>
            
            <div class="message-box">
                <p>{{ $content ?? 'No message provided' }}</p>
            </div>
            
            @if(!empty($data))
                <h3 style="margin: 20px 0 10px;">📋 Details:</h3>
                <table class="data-table">
                    @foreach($data as $key => $value)
                        <tr>
                            <td class="label">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                            <td class="value">
                                @if(is_array($value) || is_object($value))
                                    {{ json_encode($value) }}
                                @else
                                    <strong>{{ $value }}</strong>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ config('app.url') }}" class="btn">
                    Go to Admin Dashboard
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>This is an automated notification from <strong>KTM-WDC</strong></p>
            <p style="margin: 5px 0 0;">© {{ date('Y') }} KTM-WDC. All rights reserved.</p>
            <p style="margin: 5px 0 0; font-size: 11px;">
                This email was sent to the administrator of KTM-WDC.
            </p>
        </div>
    </div>
</body>
</html>