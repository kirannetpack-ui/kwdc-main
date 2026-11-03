<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KTM-WDC | Secure Access</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400;1,6..72,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #24201D;
            --muted: #57524C;
            --line: #E8E2D8;
            --terracotta: #D96B43;
            --terracotta-hover: #C35832;
            --paper: #FFFFFF;
            --soft: #FAF8F5;
            --radius: 24px;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 12% 12%, rgba(217, 107, 67, .05), transparent 340px),
                radial-gradient(circle at 88% 16%, rgba(184, 115, 42, .04), transparent 420px),
                linear-gradient(135deg, #FAF8F5 0%, #F5F0E8 100%);
        }

        a { color: inherit; }

        .guest-wrap {
            width: min(1160px, calc(100% - 36px));
            min-height: 100vh;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(320px, .92fr) minmax(360px, 520px);
            gap: clamp(36px, 6vw, 86px);
            align-items: center;
            padding: 48px 0;
        }

        .brand-panel { color: var(--ink); }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 36px;
            color: var(--ink);
            text-decoration: none;
            font-family: 'Newsreader', Georgia, serif;
            font-weight: 600;
            font-size: 24px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            color: #ffffff;
            background: var(--terracotta);
            box-shadow: 0 4px 14px rgba(217, 107, 67, .28);
        }

        .brand-panel h1 {
            max-width: 650px;
            margin: 0 0 18px;
            font-size: clamp(46px, 6vw, 72px);
            line-height: .98;
            letter-spacing: 0;
        }

        .brand-panel p {
            max-width: 570px;
            margin: 0;
            color: #d4deec;
            font-size: 18px;
            line-height: 1.7;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            max-width: 620px;
            margin-top: 34px;
        }

        .mini-card {
            min-height: 118px;
            padding: 16px;
            border: 1px solid rgba(219, 228, 239, .16);
            border-radius: 10px;
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(14px);
        }

        .mini-card i {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            margin-bottom: 12px;
            border-radius: 16px;
            color: #101724;
            background: var(--gold);
        }

        .mini-card strong { display: block; color: white; font-size: 14px; }
        .mini-card span { display: block; margin-top: 5px; color: #cbd5e1; font-size: 12px; line-height: 1.4; }

        .guest-card {
            width: 100%;
            padding: clamp(28px, 4vw, 44px);
            border: 1px solid rgba(219, 228, 239, .95);
            border-radius: var(--radius);
            background: rgba(255, 255, 255, .98);
            box-shadow: 0 34px 100px rgba(15, 23, 42, .24);
        }

        .guest-card h1,
        .guest-card h2 {
            margin-top: 0;
            color: var(--ink);
            line-height: 1.05;
            letter-spacing: 0;
        }

        .guest-card p {
            color: var(--muted);
            line-height: 1.62;
        }

        .guest-card label {
            display: block;
            margin-bottom: 8px;
            color: #26364d;
            font-size: 14px;
            font-weight: 800;
        }

        .guest-card input,
        .guest-card select,
        .guest-card textarea {
            width: 100%;
            min-height: 48px;
            border: 1px solid #cfd9e6;
            border-radius: 18px;
            padding: 12px 14px;
            color: var(--ink);
            font: inherit;
            background: #ffffff;
        }

        .guest-card input:focus,
        .guest-card select:focus,
        .guest-card textarea:focus {
            outline: none;
            border-color: var(--terracotta);
            box-shadow: 0 0 0 3.5px rgba(217, 107, 67, .16);
        }

        .guest-card button,
        .guest-card .inline-flex {
            min-height: 48px;
            border-radius: 9999px !important;
            font: inherit;
            font-weight: 600 !important;
        }

        .guest-card button[type="submit"],
        .guest-card .bg-gray-800,
        .guest-card .bg-orange-500 {
            color: #ffffff !important;
            border: 0 !important;
            background: var(--terracotta) !important;
            box-shadow: 0 4px 14px rgba(217, 107, 67, .28);
        }

        .guest-card .text-orange-600,
        .guest-card a {
            color: var(--terracotta) !important;
            font-weight: 600;
        }

        @media (max-width: 920px) {
            body {
                background:
                    radial-gradient(circle at 20% 4%, rgba(245, 165, 36, .18), transparent 280px),
                    linear-gradient(180deg, #101724 0%, #172236 44%, #f7f9fc 44%, #f7f9fc 100%);
            }

            .guest-wrap {
                grid-template-columns: 1fr;
                gap: 30px;
                align-items: start;
                padding: 30px 0;
            }

            .brand-panel h1 { font-size: 42px; }
            .brand-panel p { font-size: 16px; }
        }

        @media (max-width: 620px) {
            .guest-wrap { width: min(100% - 28px, 1160px); }
            .mini-grid { grid-template-columns: 1fr; }
            .guest-card { border-radius: 24px; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/kwdc-auth.css') }}">
    <script src="{{ asset('js/kwdc-auth.js') }}" defer></script>
</head>
<body>
    <main class="guest-wrap">
        <section class="brand-panel">
            <a class="brand" href="{{ route('landing') }}">
                <span class="brand-icon"><i class="fas fa-warehouse"></i></span>
                <span>KTM-WDC</span>
            </a>
        </section>

        <section class="guest-card">
            {{ $slot }}
        </section>
    </main>
</body>
</html>
