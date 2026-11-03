<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KTM-WDC Notification</title>
    <style>
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-slide { animation: slideIn 0.5s ease-out; }
        .animate-pulse { animation: pulse 1s infinite; }
        .animate-fade { animation: fadeIn 0.8s ease-in; }
        .status-pending { background: #fef3c7; color: #d97706; border-left-color: #d97706; }
        .status-assigned { background: #dbeafe; color: #2563eb; border-left-color: #2563eb; }
        .status-on_the_way { background: #fed7aa; color: #ea580c; border-left-color: #ea580c; }
        .status-delivered { background: #d1fae5; color: #059669; border-left-color: #059669; }
        .status-completed { background: #d1fae5; color: #059669; border-left-color: #059669; }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 12px; padding: 30px; text-align: center; margin-bottom: 20px;">
            <div style="display: inline-block; background: white; border-radius: 50%; padding: 15px; margin-bottom: 15px;">
                <span style="font-size: 40px;">🏢</span>
            </div>
            <h1 style="color: white; margin: 0; font-size: 28px;">KTM-WDC</h1>
            <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0;">Warehouse & Distribution Connect</p>
        </div>
        
        <!-- Content -->
        <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            @yield('content')
        </div>
        
        <!-- Footer -->
        <div style="text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px;">
            <p>KTM-WDC - Warehouse & Distribution Connect</p>
            <p>Kathmandu, Nepal | support@ktm-wdc.com | +977 9800000000</p>
            <p>&copy; {{ date('Y') }} KTM-WDC. All rights reserved.</p>
        </div>
    </div>
</body>
</html>