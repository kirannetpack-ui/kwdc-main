<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KTM-WDC | Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #101724;
            --muted: #62728a;
            --line: #dbe4ef;
            --gold: #f5a524;
            --green: #0f766e;
            --blue: #2563eb;
            --red: #dc2626;
            --paper: #ffffff;
            --soft: #f5f7fb;
            --radius: 28px;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                linear-gradient(120deg, rgba(16, 23, 36, .94), rgba(16, 23, 36, .78)),
                url("{{ asset('images/landing-logistics.svg') }}") right bottom / min(780px, 66vw) auto no-repeat,
                #101724;
        }

        a { color: inherit; }

        .auth-shell {
            width: min(1240px, calc(100% - 32px));
            min-height: 100vh;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(300px, .85fr) minmax(420px, 680px);
            gap: clamp(36px, 5vw, 76px);
            align-items: center;
            padding: 42px 0;
        }

        .brand-side { color: white; }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 34px;
            text-decoration: none;
            font-weight: 900;
            font-size: 22px;
        }

        .brand-mark {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            color: #111827;
            background: var(--gold);
            box-shadow: 0 18px 38px rgba(245, 165, 36, .24);
        }

        .brand-side h1 {
            max-width: 620px;
            margin: 0 0 20px;
            font-size: clamp(40px, 5.6vw, 70px);
            line-height: .98;
            letter-spacing: 0;
        }

        .brand-side p {
            max-width: 560px;
            margin: 0;
            color: #c9d3e3;
            font-size: 18px;
            line-height: 1.7;
        }

        .feature-list {
            display: grid;
            gap: 12px;
            margin-top: 30px;
            max-width: 520px;
        }

        .feature {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 12px;
            align-items: center;
            padding: 13px;
            border: 1px solid rgba(219, 228, 239, .16);
            border-radius: 22px;
            background: rgba(255, 255, 255, .08);
            color: #e5edf8;
            backdrop-filter: blur(12px);
        }

        .feature i {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            color: #101724;
            background: var(--gold);
        }

        .feature strong { display: block; font-size: 14px; }
        .feature span { color: #c9d3e3; font-size: 13px; line-height: 1.4; }

        .register-card {
            width: 100%;
            max-height: calc(100vh - 52px);
            overflow-y: auto;
            padding: clamp(30px, 4vw, 46px);
            border: 1px solid rgba(219, 228, 239, .92);
            border-radius: var(--radius);
            background: rgba(255, 255, 255, .97);
            box-shadow: 0 28px 80px rgba(0, 0, 0, .34);
            backdrop-filter: blur(18px);
        }

        .register-card::-webkit-scrollbar { width: 8px; }
        .register-card::-webkit-scrollbar-track { background: transparent; }
        .register-card::-webkit-scrollbar-thumb { background: rgba(98, 114, 138, .42); border-radius: 999px; }

        .card-head { margin-bottom: 26px; }
        .card-head span { color: var(--green); font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; }
        .card-head h2 { margin: 8px 0 8px; font-size: 31px; line-height: 1.08; letter-spacing: 0; }
        .card-head p { margin: 0; color: var(--muted); line-height: 1.55; }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #25364d;
            font-size: 14px;
            font-weight: 800;
        }

        .form-group label i { margin-right: 7px; color: var(--green); }

        input,
        select,
        textarea {
            width: 100%;
            min-height: 46px;
            padding: 12px 13px;
            border: 1px solid #cfd9e6;
            border-radius: 18px;
            color: var(--ink);
            font: inherit;
            background: #ffffff;
            transition: border-color .16s ease, box-shadow .16s ease;
        }

        textarea { min-height: 76px; resize: vertical; }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(245, 165, 36, .16);
        }

        .helper-text {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 5px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .role-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(138px, 1fr));
            gap: 10px;
            margin-top: 7px;
        }

        .role-option {
            position: relative;
            min-height: 116px;
            padding: 14px 12px;
            border: 1px solid #dbe4ef;
            border-radius: 22px;
            background: #ffffff;
            cursor: pointer;
            text-align: left;
            transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }

        .role-option:hover,
        .role-option.selected {
            border-color: var(--gold);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08), 0 0 0 4px rgba(245, 165, 36, .12);
            transform: translateY(-1px);
        }

        .role-option > i {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            margin-bottom: 10px;
            border-radius: 18px;
            color: white !important;
            background: var(--blue);
        }

        .role-option:nth-child(2) > i { background: var(--gold); color: #111827 !important; }
        .role-option:nth-child(3) > i { background: #7c3aed; }
        .role-option:nth-child(4) > i { background: var(--green); }
        .role-option:nth-child(5) > i { background: var(--red); }
        .role-name { display: block; color: var(--ink); font-size: 13px; font-weight: 900; }
        .role-desc { display: block; margin-top: 3px; color: var(--muted); font-size: 11px; line-height: 1.35; }
        .role-option input[type="radio"] { display: none; }

        .check-mark {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 22px;
            height: 22px;
            display: none;
            place-items: center;
            border-radius: 999px;
            color: #111827;
            background: var(--gold);
            font-size: 11px;
        }

        .role-option.selected .check-mark { display: grid; }

        .agency-fields {
            display: none;
            margin: 4px 0 18px;
            padding: 18px;
            border: 1px solid #cfe7e3;
            border-left: 4px solid var(--green);
            border-radius: 24px;
            background: #f0fdfa;
        }

        .agency-fields.show { display: block; }
        .agency-fields h4 { margin: 0 0 16px; color: #0f3f3a; font-size: 17px; font-weight: 900; }
        .agency-fields .form-group:last-child { margin-bottom: 0; }

        .terms-row {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin: 2px 0 18px;
            color: #45556f;
            font-size: 14px;
            line-height: 1.55;
            font-weight: 700;
        }

        .terms-row input { width: 16px; min-height: 16px; margin-top: 3px; accent-color: var(--gold); }
        .terms-row a, .switch-link a { color: var(--green); font-weight: 900; text-decoration: none; }

        .btn-register {
            width: 100%;
            min-height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 18px;
            color: #111827;
            background: var(--gold);
            font: inherit;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 16px 30px rgba(245, 165, 36, .24);
            transition: transform .16s ease, background-color .16s ease;
        }

        .btn-register:hover { background: #c77700; color: white; transform: translateY(-1px); }

        .switch-link {
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            text-align: center;
            font-size: 14px;
            font-weight: 700;
        }

        .error-box,
        .success-box {
            margin-bottom: 20px;
            padding: 13px 15px;
            border-radius: 18px;
            font-size: 14px;
        }

        .error-box { color: #991b1b; border: 1px solid #fecaca; background: #fef2f2; }
        .success-box { color: #065f46; border: 1px solid #a7f3d0; background: #ecfdf5; }
        .error-box ul { margin: 8px 0 0; padding-left: 20px; }

        @media (max-width: 980px) {
            .auth-shell {
                grid-template-columns: 1fr;
                align-items: start;
                gap: 30px;
                padding: 28px 0;
            }

            .register-card { max-height: none; }
            .brand-side h1 { font-size: 42px; }
            .brand-side p { font-size: 16px; }
        }

        @media (max-width: 640px) {
            .form-row { grid-template-columns: 1fr; gap: 0; }
            .register-card { padding: 24px; }
            .role-grid { grid-template-columns: 1fr; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/kwdc-auth.css') }}">
    <script src="{{ asset('js/kwdc-auth.js') }}" defer></script>
</head>
<body>
    <main class="auth-shell">
        <section class="brand-side">
            <a class="brand" href="{{ route('landing') }}">
                <span class="brand-mark"><i class="fas fa-warehouse"></i></span>
                <span>KTM-WDC</span>
            </a>
        </section>

        <section class="register-card">
            <div class="card-head">
                <h2>Create your account.</h2>
            </div>

            @if(session('success'))
            <div class="success-box">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="error-box">
                <strong><i class="fas fa-exclamation-triangle"></i> Please fix the following:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i>Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g. Rajesh Sharma" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i>Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="you@company.com" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i>Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="e.g. 9800000000" required>
                        <div class="helper-text"><i class="fas fa-info-circle"></i>For updates.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address"><i class="fas fa-map-pin"></i>Address</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}" placeholder="Your physical address (optional)">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i>Password</label>
                        <input type="password" name="password" id="password" placeholder="Min 8 characters" required>
                        <div class="helper-text"><i class="fas fa-shield-alt"></i>Minimum 8 characters.</div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation"><i class="fas fa-check-circle"></i>Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Re-enter password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user-tag"></i>I want to register as</label>
                    <div class="role-grid" id="roleSelector">
                        <label class="role-option {{ old('role', request('role', 'client')) == 'client' ? 'selected' : '' }}">
                            <input type="radio" name="role" value="client" {{ old('role', request('role', 'client')) == 'client' ? 'checked' : '' }}>
                            <span class="check-mark"><i class="fas fa-check"></i></span>
                            <i class="fas fa-user-circle"></i>
                            <span class="role-name">Client</span>
                            <span class="role-desc">Request services</span>
                        </label>
                        <label class="role-option {{ old('role', request('role', 'client')) == 'driver' ? 'selected' : '' }}">
                            <input type="radio" name="role" value="driver" {{ old('role', request('role', 'client')) == 'driver' ? 'checked' : '' }}>
                            <span class="check-mark"><i class="fas fa-check"></i></span>
                            <i class="fas fa-truck"></i>
                            <span class="role-name">Driver</span>
                            <span class="role-desc">Transport work</span>
                        </label>
                        <label class="role-option {{ old('role', request('role', 'client')) == 'property_owner' ? 'selected' : '' }}">
                            <input type="radio" name="role" value="property_owner" {{ old('role', request('role', 'client')) == 'property_owner' ? 'checked' : '' }}>
                            <span class="check-mark"><i class="fas fa-check"></i></span>
                            <i class="fas fa-warehouse"></i>
                            <span class="role-name">Property Owner</span>
                            <span class="role-desc">List space</span>
                        </label>
                        <label class="role-option {{ old('role', request('role', 'client')) == 'equipment_owner' ? 'selected' : '' }}">
                            <input type="radio" name="role" value="equipment_owner" {{ old('role', request('role', 'client')) == 'equipment_owner' ? 'checked' : '' }}>
                            <span class="check-mark"><i class="fas fa-check"></i></span>
                            <i class="fas fa-tools"></i>
                            <span class="role-name">Equipment Owner</span>
                            <span class="role-desc">Rental work</span>
                        </label>
                        <label class="role-option {{ old('role', request('role', 'client')) == 'security_agency' ? 'selected' : '' }}">
                            <input type="radio" name="role" value="security_agency" {{ old('role', request('role', 'client')) == 'security_agency' ? 'checked' : '' }}>
                            <span class="check-mark"><i class="fas fa-check"></i></span>
                            <i class="fas fa-shield-alt"></i>
                            <span class="role-name">Security Agency</span>
                            <span class="role-desc">Security work</span>
                        </label>
                    </div>
                    <div class="helper-text"><i class="fas fa-info-circle"></i>Select one role.</div>
                </div>

                <div id="agencyFields" class="agency-fields {{ old('role', request('role', 'client')) == 'security_agency' ? 'show' : '' }}">
                    <div class="form-row">
                        <div class="form-group"><label for="agency_name">Agency name</label><input id="agency_name" name="agency_name" maxlength="255" value="{{ old('agency_name') }}"></div>
                        <div class="form-group"><label for="registration_number">Registration number</label><input id="registration_number" name="registration_number" maxlength="100" value="{{ old('registration_number') }}"></div>
                    </div>
                </div>

                <div class="terms-group" style="margin: 18px 0 22px; display: flex; align-items: flex-start; gap: 10px; font-size: 12.5px; color: #475569; line-height: 1.5;">
                    <input type="checkbox" name="terms" id="terms" required style="margin-top: 3px; accent-color: #ea580c; width: 16px; height: 16px; border-radius: 4px; cursor: pointer;">
                    <label for="terms" style="cursor: pointer;">
                        I agree to the legally binding <a href="{{ route('terms-of-service') }}" target="_blank" style="color: #ea580c; text-decoration: underline; font-weight: 600;">Terms of Service</a> (including statutory Carrier Liability limits capped at NPR 250/kg under Muluki Civil Code 2074 & Transport Act 2049) and <a href="{{ route('privacy-policy') }}" target="_blank" style="color: #ea580c; text-decoration: underline; font-weight: 600;">Privacy Policy</a>.
                    </label>
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="switch-link">
                Already have an account? <a href="{{ route('login') }}">Sign in</a>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
                toggleAgencyFields();
            });
        });

        function toggleAgencyFields() {
            const selectedRole = document.querySelector('input[name="role"]:checked');
            const agencyFields = document.getElementById('agencyFields');
            if (selectedRole && selectedRole.value === 'security_agency') {
                agencyFields.classList.add('show');
                document.querySelectorAll('#agencyFields input, #agencyFields textarea').forEach(input => {
                    if (input.name && input.name !== 'phone' && input.name !== 'address') {
                        input.required = true;
                        input.disabled = false;
                    }
                });
            } else {
                agencyFields.classList.remove('show');
                document.querySelectorAll('#agencyFields input, #agencyFields textarea').forEach(input => {
                    input.required = false;
                    input.disabled = true;
                });
            }
        }

        document.addEventListener('DOMContentLoaded', toggleAgencyFields);
        document.querySelectorAll('input[name="role"]').forEach(input => input.addEventListener('change', () => {
            document.querySelectorAll('.role-option').forEach(option => option.classList.toggle('selected', option.querySelector('input').checked));
            toggleAgencyFields();
        }));
    </script>
</body>
</html>
