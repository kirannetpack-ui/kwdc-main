<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="KTM-WDC connects commercial warehouse space, freight deliveries, heavy equipment, and trusted teams across Nepal.">
    <meta property="og:title" content="KTM-WDC | Nepal's Commercial Logistics & Warehousing Portal">
    <meta property="og:description" content="Enterprise commercial warehousing, GPS freight dispatch, heavy machinery rentals, and licensed security post management across Nepal.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#f97316">
    <link rel="manifest" href="/manifest.json">
    <title>KTM-WDC | Warehouse & Distribution Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400;1,6..72,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kwdc-public.css') }}?v=4.2">
    <style>

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            padding: 60px 0 30px 0;
        }
        @media (max-width: 768px) {
            .footer-grid { grid-template-columns: 1fr; gap: 30px; }
        }
        .footer-col h4 {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .footer-col ul a {
            color: #64748b;
            text-decoration: none;
            font-size: 13.5px;
            transition: color 0.15s;
        }
        .footer-col ul a:hover {
            color: #ea580c;
        }
        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 0 40px 0;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #94a3b8;
            flex-wrap: wrap;
            gap: 16px;
        }
    </style>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-header">
        <nav class="public-nav shell" aria-label="Main navigation">
            <a class="wordmark" href="{{ route('landing') }}"><i class="fas fa-warehouse text-orange-500" aria-hidden="true"></i> KTM-WDC</a>
            <div class="nav-sections">
                <a href="#services">{{ __('Services') }}</a>
                <a href="#partners">{{ __('Network Roles') }}</a>
                <a href="{{ route('compliance') }}">{{ __('Compliance') }}</a>
                <a href="{{ route('privacy-policy') }}">{{ __('Legal') }}</a>
            </div>
            <div class="nav-actions">
                @php $currentLocale = app()->getLocale(); @endphp
                <div style="display: inline-flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 2px; background: #fff; margin-right: 8px;">
                    <a href="{{ route('locale.switch', 'en') }}" style="padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-decoration: none; {{ $currentLocale === 'en' ? 'background: #ea580c; color: #fff;' : 'color: #64748b;' }}">EN</a>
                    <a href="{{ route('locale.switch', 'ne') }}" style="padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-decoration: none; {{ $currentLocale === 'ne' ? 'background: #ea580c; color: #fff;' : 'color: #64748b;' }}">नेपाली</a>
                </div>
                <a href="{{ route('login') }}">{{ __('Log in') }}</a>
                <a class="button button-dark" href="{{ route('register') }}">{{ __('Get started') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </nav>
    </header>

    <main id="main">
        <section class="hero">
            <img class="hero-image" src="{{ asset('images/logistics-campus.png') }}" alt="Illustrated warehouse campus with delivery vehicles" fetchpriority="high" width="1536" height="1024">
            <div class="shell hero-shell">
                <div class="hero-content">
                    <span class="eyebrow"><i class="fas fa-wand-magic-sparkles text-xs" style="color: #D96B43;"></i> Warehouse &amp; Distribution Connect</span>
                    <h1>KTM-WDC</h1>
                    <p class="hero-title">{{ __('Space to grow. Room to move.') }}</p>
                    <p class="hero-copy">{{ __('Commercial warehousing, live freight dispatch and trusted fleet operations across Nepal.') }}</p>
                    <div class="hero-actions">
                        <a class="button button-dark" href="{{ route('register') }}">{{ __('Find your workspace') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                        <a class="button button-outline" href="{{ route('login') }}">{{ __('Partner Portal') }} <i class="fas fa-sign-in-alt" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
            <span class="illustration-credit">Commercial Logistics Network</span>
        </section>


        <section class="services shell" id="services">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Integrated Operations</span>
                    <h2>Every move, in one place.</h2>
                </div>
                <a class="text-link" href="{{ route('login') }}">Open your portal <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="service-grid">
                <article>
                    <span class="service-icon green"><i class="fas fa-warehouse" aria-hidden="true"></i></span>
                    <h3>Commercial Storage</h3>
                    <p>Verified dry &amp; cold storage with loading docks, CCTV, and instant lease booking.</p>
                    <a href="{{ route('register', ['role' => 'client']) }}">Explore warehousing <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </article>
                <article>
                    <span class="service-icon gold"><i class="fas fa-truck" aria-hidden="true"></i></span>
                    <h3>Live Freight Dispatch</h3>
                    <p>Multi-stop route planning, assigned vetted drivers, and real-time GPS tracking.</p>
                    <a href="{{ route('register', ['role' => 'client']) }}">Plan a delivery <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </article>
                <article>
                    <span class="service-icon blue"><i class="fas fa-boxes-stacked" aria-hidden="true"></i></span>
                    <h3>Equipment &amp; Security</h3>
                    <p>Industrial forklifts, certified operators, and on-duty security guard posts.</p>
                    <a href="{{ route('register') }}">Connect your team <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </article>
            </div>
        </section>

        <section class="partners" id="partners">
            <div class="shell partner-layout">
                <div>
                    <span class="eyebrow">Commercial Network</span>
                    <h2>Your expertise.<br>A shared workspace.</h2>
                    <p>Register as an enterprise partner or operational driver.</p>
                </div>
                <div class="partner-list">
                    @foreach(['driver' => ['Drivers & Fleet Owners', 'fa-truck-fast'], 'property_owner' => ['Warehouse Facility Owners', 'fa-building'], 'equipment_owner' => ['Machinery & Equipment Owners', 'fa-tractor'], 'security_agency' => ['Licensed Security Agencies', 'fa-shield-halved']] as $role => $details)
                    <a href="{{ route('register', ['role' => $role]) }}">
                        <i class="fas {{ $details[1] }}" aria-hidden="true"></i>
                        <span>{{ $details[0] }}</span>
                        <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="closing shell">
            <h2>Ready for your next move?</h2>
            <a class="button button-dark" href="{{ route('register') }}">Create an account <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </section>
    </main>

    <footer class="shell" style="border-top: 1px solid var(--claude-border); margin-top: 40px;">
        <div class="footer-grid">
            <div class="footer-col">
                <a class="wordmark" href="{{ route('landing') }}"><i class="fas fa-warehouse text-[#D96B43]"></i> KTM-WDC</a>
                <p style="color: var(--claude-text-body); font-size: 13px; line-height: 1.6; margin-top: 12px;">
                    Nepal's premier integrated logistics platform connecting warehousing capacity, highway freight dispatch, certified equipment, and facility security.
                </p>
                <p style="color: var(--claude-text-muted); font-size: 12px; margin-top: 8px;">
                    <strong>Central Office:</strong> Tinkune &amp; Baluwatar Freight Corridors, Kathmandu, Nepal<br>
                    <strong>Helpline:</strong> +977-1-5912400 &bull; support@kwdc.test
                </p>
            </div>
            <div class="footer-col">
                <h4>Solutions</h4>
                <ul>
                    <li><a href="{{ route('register', ['role' => 'client']) }}">Warehouse Booking</a></li>
                    <li><a href="{{ route('register', ['role' => 'client']) }}">Live Freight Dispatch</a></li>
                    <li><a href="{{ route('register', ['role' => 'equipment_owner']) }}">Machinery Rental</a></li>
                    <li><a href="{{ route('register', ['role' => 'security_agency']) }}">Security Posts</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Network Partners</h4>
                <ul>
                    <li><a href="{{ route('register', ['role' => 'driver']) }}">Driver Registration</a></li>
                    <li><a href="{{ route('register', ['role' => 'property_owner']) }}">List Your Warehouse</a></li>
                    <li><a href="{{ route('login') }}">Partner Portal Login</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Legal &amp; Compliance</h4>
                <ul>
                    <li><a href="{{ route('privacy-policy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('terms-of-service') }}">Terms of Service</a></li>
                    <li><a href="{{ route('compliance') }}">Carrier &amp; Safety Compliance</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom" style="border-top: 1px solid var(--claude-border-subtle); color: var(--claude-text-muted);">
            <span>&copy; {{ date('Y') }} KTM-WDC (Warehouse &amp; Distribution Connect). All rights reserved.</span>
            <div style="display: flex; gap: 16px;">
                <a href="{{ route('privacy-policy') }}" style="color: inherit; text-decoration: none;">Privacy</a>
                <a href="{{ route('terms-of-service') }}" style="color: inherit; text-decoration: none;">Terms</a>
                <a href="{{ route('compliance') }}" style="color: inherit; text-decoration: none;">Compliance</a>
            </div>
        </div>
    </footer>
</body>
</html>
