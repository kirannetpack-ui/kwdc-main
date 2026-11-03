<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Verification | KTM-WDC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", Roboto, sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 20px;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            padding: 36px 30px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            text-align: center;
        }
        .shield-icon {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            background: #fff7ed;
            border: 1px solid #ffedd5;
            color: #ea580c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 16px;
        }
        h1 { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        p { font-size: 13px; color: #64748b; margin-bottom: 24px; }
        .otp-input {
            width: 100%;
            font-size: 26px;
            letter-spacing: 12px;
            text-align: center;
            padding: 12px 16px;
            border-radius: 16px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            font-weight: 800;
            outline: none;
            transition: all 0.2s ease;
            margin-bottom: 20px;
        }
        .otp-input:focus {
            border-color: #ea580c;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15);
        }
        .btn-submit {
            width: 100%;
            background: #ea580c;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-submit:hover { background: #c2410c; transform: translateY(-1px); }
        .error-msg { color: #e11d48; font-size: 12px; margin-bottom: 12px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <div class="shield-icon">
            <i class="fas fa-shield-halved"></i>
        </div>
        <h1>Two-Factor Verification</h1>
        <p>Please enter the 6-digit security code dispatched to your registered Nepal mobile / email.</p>

        @if($errors->has('code'))
            <div class="error-msg">{{ $errors->first('code') }}</div>
        @endif

        <form method="POST" action="{{ route('two-factor.verify') }}">
            @csrf
            <input type="text" name="code" maxlength="6" autofocus placeholder="••••••" class="otp-input" required autocomplete="one-time-code">
            <button type="submit" class="btn-submit">
                <i class="fas fa-check-circle me-1"></i> Verify & Authenticate
            </button>
        </form>
    </div>
</body>
</html>
