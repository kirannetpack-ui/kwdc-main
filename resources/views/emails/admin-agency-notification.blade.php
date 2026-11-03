<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Agency Registration</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; }
        .header { border-bottom: 3px solid #f59e0b; padding-bottom: 20px; }
        .content { line-height: 1.6; }
        .details { background: #f9fafb; padding: 15px; border-radius: 6px; margin: 20px 0; }
        .btn { display: inline-block; background: #f59e0b; color: #fff; padding: 10px 25px; border-radius: 6px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔔 New Security Agency Registration</h1></div>
        <div class="content">
            <p>A new security agency has registered on KTM-WDC.</p>
            <div class="details">
                <p><strong>Agency:</strong> {{ $agency->agency_name }}</p>
                <p><strong>Contact:</strong> {{ $agency->email }} / {{ $agency->phone }}</p>
                <p><strong>Services:</strong> {{ $agency->services_offered }}</p>
                <p><strong>Status:</strong> <span style="color:#f59e0b;">Pending</span></p>
            </div>
            <a href="{{ config('app.url') }}/admin/security/agencies/{{ $agency->id }}" class="btn">Review Agency</a>
        </div>
    </div>
</body>
</html>
