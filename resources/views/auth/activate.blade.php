<x-guest-layout>
    <style>
        .activation-head {
            margin-bottom: 34px;
        }

        .activation-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            color: #0f766e;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .activation-head h1 {
            margin: 0 0 12px;
            font-size: clamp(32px, 4vw, 44px);
        }

        .activation-head p {
            margin: 0;
            font-size: 16px;
        }

        .notice {
            display: flex;
            gap: 12px;
            margin-bottom: 22px;
            padding: 18px 20px;
            border: 1px solid #bfdbfe;
            border-radius: 22px;
            color: #1d4ed8;
            background: #eff6ff;
            line-height: 1.5;
            font-weight: 700;
        }

        .demo-code-box {
            margin-bottom: 22px;
            padding: 22px;
            border: 1px solid #f5d38a;
            border-radius: 24px;
            background: #fffbeb;
        }

        .demo-code-box span {
            display: block;
            color: #92400e;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .demo-code-box strong {
            display: block;
            margin-top: 8px;
            color: #101724;
            font-size: 34px;
            line-height: 1;
            letter-spacing: .18em;
        }

        .activation-form {
            display: grid;
            gap: 18px;
        }

        .field-help {
            margin-top: 7px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.45;
        }

        .code-input {
            font-size: 22px !important;
            font-weight: 900 !important;
            letter-spacing: .28em;
            text-align: center;
        }

        .error-text {
            margin: 7px 0 0;
            color: #dc2626;
            font-size: 13px;
            font-weight: 800;
        }

        .activation-actions {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-top: 4px;
        }

        .activate-button,
        .resend-button {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
        }

        .resend-button {
            color: #0f766e !important;
            border: 1px solid #cfe7e3 !important;
            background: #f0fdfa !important;
            box-shadow: none !important;
        }

        .activation-footer {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #dbe4ef;
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
        }
    </style>

    <div class="activation-head">
        <span class="activation-kicker"><i class="fas fa-shield-check"></i> Verification</span>
        <h1>Activate your account</h1>
        <p>Enter your 6-digit code.</p>
    </div>

    @if(session('status'))
        <div class="notice">
            <i class="fas fa-circle-info"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('activation.verify') }}" class="activation-form" id="activation-form">
        @csrf
        <div>
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email">
            @error('email') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="activation_code">Activation code</label>
            <input id="activation_code" class="code-input" name="activation_code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required placeholder="000000">
            <p class="field-help">Use the newest code.</p>
            @error('activation_code') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div class="activation-actions">
            <button type="submit" class="activate-button">
                <i class="fas fa-check-circle"></i> Activate account
            </button>
            <button type="submit" formaction="{{ route('activation.resend') }}" formnovalidate class="resend-button">
                <i class="fas fa-paper-plane"></i> Send a new code
            </button>
        </div>
    </form>

    <div class="activation-footer">
        KTM-WDC will never ask for your password to verify email.
    </div>
</x-guest-layout>
