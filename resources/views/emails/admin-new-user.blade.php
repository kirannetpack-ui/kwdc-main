<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User Registration</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #f59e0b; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #1e3c72; font-size: 28px; margin: 0; }
        .content { color: #374151; line-height: 1.6; }
        .user-details { background: #f9fafb; padding: 15px; border-radius: 6px; margin: 20px 0; }
        .user-details th { text-align: left; padding: 8px 10px; }
        .user-details td { padding: 8px 10px; }
        .btn { display: inline-block; background: #f59e0b; color: #fff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; color: #9ca3af; font-size: 14px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 New User Registration</h1>
        </div>

        <div class="content">
            <p>A new user has just registered on <strong>KTM-WDC</strong>.</p>

            <div class="user-details">
                <table width="100%">
                    <tr><th>Name</th><td>{{ $user->name }}</td></tr>
                    <tr><th>Email</th><td>{{ $user->email }}</td></tr>
                    <tr><th>Role</th><td>{{ ucfirst($user->role) }}</td></tr>
                    <tr><th>User Code</th><td>{{ $user->user_code }}</td></tr>
                    <tr><th>Registered At</th><td>{{ $user->created_at->format('F j, Y, g:i a') }}</td></tr>
                    <tr><th>Status</th><td><span style="color: #f59e0b; font-weight: bold;">Pending Approval</span></td></tr>
                </table>
            </div>

            <p>Please log in to the admin panel to review and approve this user.</p>

            <a href="{{ config('app.url') }}/admin/users" class="btn">Go to Admin Panel</a>

            <p style="margin-top: 20px;">If you are not expecting this registration, you can ignore this email.</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} KTM-WDC. Admin Notification.
        </div>
    </div>
</body>
</html>