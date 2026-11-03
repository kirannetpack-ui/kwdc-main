<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | KTM-WDC Logistics & Compliance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #0b0f17;
            --bg-card: #111827;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border: #1e293b;
            --accent-orange: #f97316;
            --accent-glow: rgba(249, 115, 22, 0.15);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-primary);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }
        header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(11, 15, 23, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 16px 24px;
        }
        .nav-container {
            max-width: 1080px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 18px;
            color: #ffffff;
            text-decoration: none;
        }
        .logo span { color: var(--accent-orange); }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .nav-links a:hover { color: #ffffff; }
        .btn-portal {
            background: var(--accent-orange);
            color: #ffffff !important;
            padding: 8px 18px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        .btn-portal:hover {
            background: #ea580c;
            transform: translateY(-1px);
        }
        .legal-hero {
            max-width: 1080px;
            margin: 48px auto 32px auto;
            padding: 0 24px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 9999px;
            background: rgba(249, 115, 22, 0.12);
            border: 1px solid rgba(249, 115, 22, 0.3);
            color: var(--accent-orange);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(32px, 4vw, 44px);
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            margin-bottom: 12px;
        }
        .hero-meta {
            font-size: 14px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .content-wrap {
            max-width: 1080px;
            margin: 0 auto 80px auto;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 48px;
        }
        @media (max-width: 860px) {
            .content-wrap { grid-template-columns: 1fr; }
            .legal-sidebar { display: none; }
        }
        .legal-sidebar {
            position: sticky;
            top: 90px;
            height: fit-content;
        }
        .sidebar-menu {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .sidebar-menu a {
            padding: 8px 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .sidebar-menu a:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.04);
        }
        .sidebar-menu a.active {
            color: var(--accent-orange);
            background: rgba(249, 115, 22, 0.1);
        }
        .legal-body {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        @media (max-width: 640px) {
            .legal-body { padding: 24px 18px; }
        }
        .legal-body h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            margin: 32px 0 12px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }
        .legal-body h2:first-of-type { margin-top: 0; }
        .legal-body h3 {
            font-size: 16px;
            font-weight: 600;
            color: #e2e8f0;
            margin: 20px 0 8px 0;
        }
        .legal-body p {
            color: #cbd5e1;
            font-size: 14.5px;
            margin-bottom: 16px;
        }
        .legal-body ul, .legal-body ol {
            margin: 12px 0 20px 24px;
            color: #cbd5e1;
            font-size: 14.5px;
        }
        .legal-body li {
            margin-bottom: 8px;
        }
        .callout {
            background: rgba(249, 115, 22, 0.08);
            border-left: 4px solid var(--accent-orange);
            padding: 16px 20px;
            border-radius: 0 12px 12px 0;
            margin: 24px 0;
            color: #fed7aa;
            font-size: 14px;
        }
        footer {
            border-top: 1px solid var(--border);
            padding: 40px 24px;
            background: #080c14;
        }
        .footer-inner {
            max-width: 1080px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 13px;
            color: var(--text-muted);
        }
        .footer-links {
            display: flex;
            gap: 20px;
        }
        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.15s;
        }
        .footer-links a:hover { color: #ffffff; }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="{{ route('landing') }}" class="logo">
                <i class="fas fa-warehouse text-orange-500"></i>
                <span>KTM</span>-WDC
            </a>
            <div class="nav-links">
                <a href="{{ route('landing') }}"><i class="fas fa-home"></i> Home</a>
                <a href="{{ route('landing') }}#services">Services</a>
                <a href="{{ route('privacy-policy') }}">Privacy</a>
                <a href="{{ route('terms-of-service') }}">Terms</a>
                <a href="{{ route('compliance') }}">Compliance</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-portal">Console &rarr;</a>
                @else
                    <a href="{{ route('login') }}" class="btn-portal">Partner Login</a>
                @endauth
            </div>
        </div>
    </header>

    <div class="legal-hero">
        <div class="badge">@yield('badge', 'Legal & Compliance')</div>
        <h1>@yield('heading')</h1>
        <div class="hero-meta">
            <span><i class="fas fa-calendar-check text-orange-500"></i> Effective: September 1, 2026</span>
            <span>&bull;</span>
            <span><i class="fas fa-shield-halved text-emerald-500"></i> Jurisdiction: Kathmandu, Nepal</span>
            <span>&bull;</span>
            <span><i class="fas fa-file-contract"></i> Version 2.4</span>
        </div>
    </div>

    <div class="content-wrap">
        <aside class="legal-sidebar">
            <nav class="sidebar-menu">
                <strong style="font-size: 11px; text-transform: uppercase; color: #64748b; margin-bottom: 6px; padding-left: 12px;">Legal Documents</strong>
                <a href="{{ route('privacy-policy') }}" class="{{ request()->routeIs('privacy-policy') ? 'active' : '' }}">
                    <i class="fas fa-user-shield me-2"></i> Privacy Policy
                </a>
                <a href="{{ route('terms-of-service') }}" class="{{ request()->routeIs('terms-of-service') ? 'active' : '' }}">
                    <i class="fas fa-scale-balanced me-2"></i> Terms of Service
                </a>
                <a href="{{ route('compliance') }}" class="{{ request()->routeIs('compliance') ? 'active' : '' }}">
                    <i class="fas fa-truck-ramp-box me-2"></i> Carrier & Safety Compliance
                </a>
                @auth
                <div style="height: 1px; background: var(--border); margin: 8px 0;"></div>
                <a href="{{ route('dashboard') }}">
                    <i class="fas fa-arrow-left me-2"></i> Return to Portal
                </a>
                @endauth
            </nav>
        </aside>

        <main class="legal-body">
            @yield('content')
        </main>
    </div>

    <footer>
        <div class="footer-inner">
            <div>&copy; {{ date('Y') }} KTM-WDC Logistics Platform. Registered under Department of Commerce & Logistics, Nepal.</div>
            <div class="footer-links">
                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                <a href="{{ route('terms-of-service') }}">Terms of Service</a>
                <a href="{{ route('compliance') }}">Compliance Standards</a>
                <a href="{{ route('landing') }}#partners">Partners</a>
            </div>
        </div>
    </footer>
</body>
</html>
