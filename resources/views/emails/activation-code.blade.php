@extends('emails.layout')

@section('content')
    @php
        $activationUrl = route('activation.notice', ['email' => $user->email]);
    @endphp

    <p style="margin: 0 0 10px; color: #f59e0b; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;">
        Email verification
    </p>

    <h2 style="margin: 0; color: #111827; font-size: 26px; line-height: 1.25;">
        Your KTM-WDC verification code
    </h2>

    <p style="margin: 18px 0 0; color: #374151; font-size: 15px; line-height: 1.65;">
        Hello {{ $user->name }},
    </p>

    <p style="margin: 10px 0 0; color: #374151; font-size: 15px; line-height: 1.65;">
        Use the code below to verify your email address and finish securing your KTM-WDC account.
    </p>

    <div style="margin: 26px 0; padding: 22px; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; text-align: center;">
        <div style="color: #64748b; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 10px;">
            Verification code
        </div>
        <div style="display: inline-block; padding: 14px 20px; background: #111827; color: #ffffff; border-radius: 8px; font-size: 32px; line-height: 1; font-weight: 800; letter-spacing: 8px;">
            {{ $activationCode }}
        </div>
        <p style="margin: 14px 0 0; color: #64748b; font-size: 13px;">
            This code expires in 30 minutes.
        </p>
    </div>

    <p style="margin: 0 0 20px; color: #374151; font-size: 15px; line-height: 1.65;">
        You can enter the code on the activation page linked below. For your security, KTM-WDC will never ask for your password or payment details to verify this email.
    </p>

    <p style="margin: 0 0 24px;">
        <a href="{{ $activationUrl }}" style="display: inline-block; background: #f59e0b; color: #ffffff; text-decoration: none; font-weight: 800; padding: 13px 18px; border-radius: 8px;">
            Open activation page
        </a>
    </p>

    <div style="border-top: 1px solid #e5e7eb; padding-top: 18px;">
        <p style="margin: 0; color: #64748b; font-size: 13px; line-height: 1.6;">
            If you did not create this account, ignore this email. The code will expire automatically and no account access will be granted.
        </p>
    </div>
@endsection
