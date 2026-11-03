<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->check() ? auth()->id() : '' }}">
    <meta name="theme-color" content="#f97316">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="manifest" href="/manifest.json">
    <script src="{{ asset('js/kwdc-maps.js') }}" defer></script>
    <title>KTM-WDC - @yield('title', 'Warehouse & Distribution Connect')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts: Newsreader (Editorial Serif) & Plus Jakarta Sans (Crisp Modern Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400;1,6..72,500;1,6..72,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS & JS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
    
    <!-- Chart.js Global Engine -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/kwdc-ui.css') }}">
    @stack('styles')
    
    <style>
        * { font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        h1, h2, h3, .font-serif, .kwdc-serif { font-family: 'Newsreader', Georgia, serif; }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 286px;
            height: 100vh;
            color: white;
            z-index: 50;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s ease;
        }

        .sidebar-link {
            display: flex !important;
            align-items: center;
            gap: 12px;
            margin: 4px 12px;
            padding: 11px 14px;
            color: #b8c2d6;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            width: auto;
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            transition: color 0.18s ease, background-color 0.18s ease, transform 0.18s ease;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            color: #ffffff;
            background: rgba(245, 158, 11, 0.16);
        }

        .sidebar-link i { width: 20px; text-align: center; font-size: 15px; flex-shrink: 0; }
        .sidebar-link .badge { background: #ef4444; color: white; font-size: 10px; padding: 2px 8px; border-radius: 999px; margin-left: auto; flex-shrink: 0; }
        .section-header { font-size: 10px; font-weight: 800; color: #78869d; text-transform: uppercase; letter-spacing: 1px; padding: 16px 18px 5px; display: block !important; }

        .main-content { margin-left: 286px; min-height: 100vh; background-color: #f8fafc; }
        .top-bar {
            padding: 16px 26px;
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            background: rgba(255, 255, 255, 0.82) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        }
        .page-content { padding: 28px; max-width: 1460px; margin: 0 auto; }
        .menu-toggle { display: none; position: fixed; top: 16px; left: 16px; z-index: 60; background: #f59e0b; color: #111827; padding: 10px 13px; border-radius: 8px; cursor: pointer; border: none; font-size: 18px; box-shadow: 0 14px 28px rgba(15, 23, 42, .18); }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: 286px; }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .menu-toggle { display: block; }
            .top-bar { padding: 14px 16px 14px 70px; flex-wrap: wrap; }
            .top-bar h2 { font-size: 19px; }
            .page-content { padding: 18px 14px; }
        }

        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.56); z-index: 45; }
        .sidebar-overlay.active { display: block; }

        .notification-dropdown {
            width: min(380px, calc(100vw - 28px));
            max-height: 430px;
            overflow-y: auto;
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 18px 46px rgba(15, 23, 42, 0.18);
        }
        .notification-dropdown .dropdown-header,
        .notification-dropdown .dropdown-footer {
            background: #f8fafc;
            border-color: #e5e7eb;
        }
        .notification-dropdown .dropdown-header { padding: 12px 16px; font-weight: 800; color: #0f172a; }
        .notification-dropdown .dropdown-item { padding: 12px 16px; border-bottom: 1px solid #eef2f7; white-space: normal; line-height: 1.4; }
        .notification-dropdown .dropdown-item:hover { background: #f8fafc; }
        .notification-title { font-weight: 800; color: #0f172a; }
        .notification-text { font-size: 13px; color: #64748b; margin: 2px 0; }
        .notification-time { font-size: 11px; color: #94a3b8; }
        .notification-dropdown .dropdown-footer { padding: 10px; text-align: center; border-top: 1px solid #e5e7eb; }

        .sidebar .text-gray-400 { color: #cbd5e1 !important; }
        .sidebar .text-gray-500 { color: #dbe4ef !important; }
        .sidebar .section-header { color: #cbd5e1 !important; }
        .main-content .text-gray-500,
        .main-content .text-muted { color: #526174 !important; }
        .main-content .text-gray-600 { color: #334155 !important; }
        .main-content .text-gray-400 { color: #64748b !important; }
    </style>
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/kwdc-minimal.css') }}">
    <script src="{{ asset('js/kwdc-records.js') }}" defer></script>
</head>
<body class="kwdc-app-shell">
<div id="kwdc-page-progress" aria-hidden="true"></div>

<!-- Mobile Menu Toggle -->
<button class="menu-toggle" onclick="toggleMobileMenu()" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false"><i class="fas fa-bars" aria-hidden="true"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ============================================================ -->
<!-- SIDEBAR -->
<!-- ============================================================ -->
<div class="sidebar" id="sidebar">
    <div class="kwdc-sidebar-brand p-6">
        <div class="flex items-center space-x-3">
            <div class="kwdc-brand-icon w-10 h-10 rounded-xl bg-gradient-to-br from-[#D96B43] to-[#B85832] flex items-center justify-center shadow-lg shadow-[#D96B43]/25 flex-shrink-0">
                <i class="fas fa-warehouse text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-lg font-serif font-bold text-white tracking-tight leading-tight">KTM-WDC</h1>
                <p class="text-[11px] text-[#A39B92] font-medium">Logistics & Distribution</p>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="py-4">
        @auth
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i> {{ __('Dashboard') }}
        </a>

        <!-- ADMIN -->
        @if(Auth::user()->role === 'admin')
        <div class="section-header">{{ __('Management') }}</div>
        <a href="{{ route('admin.pending') }}" class="sidebar-link {{ request()->routeIs('admin.pending') ? 'active' : '' }}">
            <i class="fas fa-clock"></i> {{ __('Pending Approvals') }}
            @php $pendingCount = \App\Models\Warehouse::where('status', 'pending')->count(); @endphp
            @if($pendingCount > 0) <span class="badge">{{ $pendingCount }}</span> @endif
        </a>
        <a href="{{ route('admin.all-warehouses') }}" class="sidebar-link {{ request()->routeIs('admin.all-warehouses') ? 'active' : '' }}">
            <i class="fas fa-warehouse"></i> {{ __('All Warehouses') }}
            @php $warehouseCount = \App\Models\Warehouse::count(); @endphp
            @if($warehouseCount > 0) <span class="badge">{{ $warehouseCount }}</span> @endif
        </a>
        <a href="{{ route('admin.requests') }}" class="sidebar-link {{ request()->routeIs('admin.requests') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i> {{ __('Warehouse Requests') }}
        </a>
        <a href="{{ route('admin.security.agencies') }}" class="sidebar-link {{ request()->routeIs('admin.security.*') ? 'active' : '' }}">
            <i class="fas fa-shield-alt"></i> {{ __('Security Agencies') }}
        </a>
        <a href="{{ route('admin.analytics.predictive') }}" class="sidebar-link {{ request()->routeIs('admin.analytics.predictive') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> {{ __('Predictive Analytics') }}
        </a>
        <a href="{{ route('admin.analytics') }}" class="sidebar-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> {{ __('Analytics Dashboard') }}
        </a>
        <a href="{{ route('admin.vehicles') }}" class="sidebar-link {{ request()->routeIs('admin.vehicles') ? 'active' : '' }}">
            <i class="fas fa-truck"></i> {{ __('All Vehicles') }}
        </a>
        <a href="{{ route('admin.vehicles.compliance') }}" class="sidebar-link {{ request()->routeIs('admin.vehicles.compliance') ? 'active' : '' }}">
            <i class="fas fa-truck-shield"></i> {{ __('Fleet Compliance') }}
        </a>
        <a href="{{ route('tracking.index') }}" class="sidebar-link {{ request()->routeIs('tracking.index') ? 'active' : '' }}">
            <i class="fas fa-map-marked-alt"></i> {{ __('Live Tracking') }}
        </a>
        <a href="{{ route('admin.dispatch') }}" class="sidebar-link {{ request()->routeIs('admin.dispatch') ? 'active' : '' }}">
            <i class="fas fa-truck-moving"></i> {{ __('Dispatch Orders') }}
        </a>
        <a href="{{ route('admin.pickup') }}" class="sidebar-link {{ request()->routeIs('admin.pickup') ? 'active' : '' }}">
            <i class="fas fa-box-open"></i> {{ __('Pickup Requests') }}
        </a>
        <a href="{{ route('admin.clients') }}" class="sidebar-link {{ request()->routeIs('admin.clients') ? 'active' : '' }}">
            <i class="fas fa-users"></i> {{ __('All Clients') }}
        </a>
        <a href="{{ route('admin.drivers') }}" class="sidebar-link {{ request()->routeIs('admin.drivers') ? 'active' : '' }}">
            <i class="fas fa-id-card"></i> {{ __('Drivers') }}
        </a>
        <a href="{{ route('admin.equipment-list') }}" class="sidebar-link {{ request()->routeIs('admin.equipment-list') ? 'active' : '' }}">
            <i class="fas fa-tools"></i> {{ __('Equipment List') }}
        </a>
        <a href="{{ route('admin.property-owners') }}" class="sidebar-link {{ request()->routeIs('admin.property-owners') ? 'active' : '' }}">
            <i class="fas fa-building"></i> {{ __('Property Owners') }}
        </a>
        <a href="{{ route('admin.invoices') }}" class="sidebar-link {{ request()->routeIs('admin.invoices') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i> {{ __('Invoices') }}
        </a>
        <a href="{{ route('admin.payments.bank-transfers') }}" class="sidebar-link {{ request()->routeIs('admin.payments.bank-transfers*') ? 'active' : '' }}">
            <i class="fas fa-building-columns"></i> {{ __('Bank & QR Transfers') }}
        </a>
        <a href="{{ route('admin.margins.index') }}" class="sidebar-link {{ request()->routeIs('admin.margins.*') ? 'active' : '' }}">
            <i class="fas fa-percent"></i> {{ __('Margins & Tiers') }}
        </a>
        <a href="{{ route('admin.reports') }}" class="sidebar-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> {{ __('Reports') }}
        </a>
        <a href="{{ route('admin.partner-earnings') }}" class="sidebar-link {{ request()->routeIs('admin.partner-earnings') ? 'active' : '' }}">
            <i class="fas fa-hand-holding-usd"></i> {{ __('Partner Earnings') }}
        </a>
        @endif

        <!-- CLIENT -->
        @if(Auth::user()->role === 'client')
        <div class="section-header">{{ __('Client Zone') }}</div>
        <a href="{{ route('my-requests.index') }}" class="sidebar-link {{ request()->routeIs('my-requests.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i> {{ __('My Requests') }}
        </a>
        <a href="{{ route('my-requests.create') }}" class="sidebar-link {{ request()->routeIs('my-requests.create') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i> {{ __('Create New') }}
        </a>
        <a href="{{ route('dispatch.index') }}" class="sidebar-link {{ request()->routeIs('dispatch.*') ? 'active' : '' }}">
            <i class="fas fa-truck"></i> {{ __('Dispatch Orders') }}
        </a>
        <a href="{{ route('pickup.index') }}" class="sidebar-link {{ request()->routeIs('pickup.*') ? 'active' : '' }}">
            <i class="fas fa-box-open"></i> {{ __('Pickup Requests') }}
        </a>
        <a href="{{ route('tracking.index') }}" class="sidebar-link {{ request()->routeIs('tracking.index') ? 'active' : '' }}">
            <i class="fas fa-map-marked-alt"></i> {{ __('Track My Shipments') }}
        </a>
        <a href="{{ route('stock.index') }}" class="sidebar-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i> {{ __('My Stock') }}
        </a>
        <a href="{{ route('client.proposals.index') }}" class="sidebar-link {{ request()->routeIs('client.proposals.*') ? 'active' : '' }}">
            <i class="fas fa-file-contract"></i> {{ __('My Proposals') }}
        </a>
        <a href="{{ route('client.equipment.requests') }}" class="sidebar-link {{ request()->routeIs('client.equipment.*') ? 'active' : '' }}">
            <i class="fas fa-tools"></i> {{ __('Equipment Requests') }}
        </a>
        <a href="{{ route('invoices.client-index') }}" class="sidebar-link {{ request()->routeIs('invoices.client-index') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i> {{ __('My Invoices') }}
        </a>
        <a href="{{ route('profile.contacts') }}" class="sidebar-link {{ request()->routeIs('profile.contacts') ? 'active' : '' }}">
            <i class="fas fa-address-book"></i> {{ __('Manage Contacts') }}
        </a>
        @endif

        <!-- DRIVER -->
        @if(Auth::user()->role === 'driver')
        <div class="section-header">{{ __('Driver Zone') }}</div>
        <a href="{{ route('driver.dashboard') }}" class="sidebar-link {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> {{ __('Driver Dashboard') }}
        </a>
        <a href="{{ route('driver.jobs') }}" class="sidebar-link {{ request()->routeIs('driver.jobs') ? 'active' : '' }}">
            <i class="fas fa-tasks"></i> {{ __('My Jobs') }}
        </a>
        <a href="{{ route('driver.available-jobs') }}" class="sidebar-link {{ request()->routeIs('driver.available-jobs') ? 'active' : '' }}">
            <i class="fas fa-search"></i> {{ __('Available Jobs') }}
        </a>
        <a href="{{ route('driver.pickups') }}" class="sidebar-link {{ request()->routeIs('driver.pickups') ? 'active' : '' }}">
            <i class="fas fa-warehouse"></i> {{ __('Pickup Jobs') }}
        </a>
        <a href="{{ route('tracking.index') }}" class="sidebar-link {{ request()->routeIs('tracking.index') ? 'active' : '' }}">
            <i class="fas fa-map-marked-alt"></i> {{ __('My Live Jobs') }}
        </a>
        <a href="{{ route('driver.vehicles.index') }}" class="sidebar-link {{ request()->routeIs('driver.vehicles.*') ? 'active' : '' }}">
            <i class="fas fa-truck"></i> {{ __('My Vehicles') }}
        </a>
        <a href="{{ route('driver.rates') }}" class="sidebar-link {{ request()->routeIs('driver.rates.*') ? 'active' : '' }}">
            <i class="fas fa-tag"></i> {{ __('My Rates') }}
        </a>
        <a href="{{ route('driver.earnings') }}" class="sidebar-link {{ request()->routeIs('driver.earnings') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> {{ __('My Earnings') }}
        </a>
        @endif

        <!-- EQUIPMENT OWNER -->
        @if(Auth::user()->role === 'equipment_owner')
        <div class="section-header">{{ __('Equipment Zone') }}</div>
        <a href="{{ route('equipment.dashboard') }}" class="sidebar-link {{ request()->routeIs('equipment.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> {{ __('Equipment Dashboard') }}
        </a>
        <a href="{{ route('equipment.register') }}" class="sidebar-link {{ request()->routeIs('equipment.register') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i> {{ __('Register Equipment') }}
        </a>
        <a href="{{ route('equipment.list') }}" class="sidebar-link {{ request()->routeIs('equipment.list') ? 'active' : '' }}">
            <i class="fas fa-tools"></i> {{ __('My Equipment') }}
        </a>
        <a href="{{ route('equipment.jobs.index') }}" class="sidebar-link {{ request()->routeIs('equipment.jobs.*') ? 'active' : '' }}">
            <i class="fas fa-briefcase"></i> {{ __('All Jobs') }}
        </a>
        <a href="{{ route('equipment.jobs.requests') }}" class="sidebar-link {{ request()->routeIs('equipment.jobs.requests') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i> {{ __('Job Requests') }}
        </a>
        <a href="{{ route('equipment.jobs.active') }}" class="sidebar-link {{ request()->routeIs('equipment.jobs.active') ? 'active' : '' }}">
            <i class="fas fa-play-circle"></i> {{ __('Active Jobs') }}
        </a>
        <a href="{{ route('equipment.jobs.history') }}" class="sidebar-link {{ request()->routeIs('equipment.jobs.history') ? 'active' : '' }}">
            <i class="fas fa-history"></i> {{ __('Job History') }}
        </a>
        <a href="{{ route('equipment.jobs.earnings') }}" class="sidebar-link {{ request()->routeIs('equipment.jobs.earnings') ? 'active' : '' }}">
            <i class="fas fa-wallet"></i> {{ __('Earnings') }}
        </a>
        @endif

        <!-- PROPERTY OWNER -->
        @if(Auth::user()->role === 'property_owner')
        <div class="section-header">{{ __('Property Owner Zone') }}</div>
        <a href="{{ route('warehouses.index') }}" class="sidebar-link {{ request()->routeIs('warehouses.index') ? 'active' : '' }}">
            <i class="fas fa-warehouse"></i> {{ __('My Warehouses') }}
        </a>
        <a href="{{ route('warehouses.create') }}" class="sidebar-link {{ request()->routeIs('warehouses.create') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i> {{ __('Add New Warehouse') }}
        </a>
        <a href="{{ route('property.pending') }}" class="sidebar-link {{ request()->routeIs('property.pending') ? 'active' : '' }}">
            <i class="fas fa-clock"></i> {{ __('Pending') }}
        </a>
        <a href="{{ route('property.approved') }}" class="sidebar-link {{ request()->routeIs('property.approved') ? 'active' : '' }}">
            <i class="fas fa-check-circle"></i> {{ __('Approved') }}
        </a>
        <a href="{{ route('property.rejected') }}" class="sidebar-link {{ request()->routeIs('property.rejected') ? 'active' : '' }}">
            <i class="fas fa-times-circle"></i> {{ __('Rejected') }}
        </a>
        <a href="{{ route('property.requests.index') }}" class="sidebar-link {{ request()->routeIs('property.requests.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i> {{ __('Lease Requests') }}
        </a>
        <a href="{{ route('property.analytics') }}" class="sidebar-link {{ request()->routeIs('property.analytics') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> {{ __('Analytics') }}
        </a>
        @endif

        <!-- SECURITY AGENCY -->
        @if(Auth::user()->role === 'security_agency')
        <div class="section-header">{{ __('Agency Zone') }}</div>
        <a href="{{ route('security.dashboard') }}" class="sidebar-link {{ request()->routeIs('security.dashboard') ? 'active' : '' }}">
            <i class="fas fa-shield-alt"></i> {{ __('Agency Dashboard') }}
        </a>
        <a href="{{ route('security.personnel.index') }}" class="sidebar-link {{ request()->routeIs('security.personnel.*') ? 'active' : '' }}">
            <i class="fas fa-user-plus"></i> {{ __('Guards & Personnel') }}
        </a>
        <a href="{{ route('security.goods.index') }}" class="sidebar-link {{ request()->routeIs('security.goods.*') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i> {{ __('My Goods') }}
        </a>
        <a href="{{ route('security.assignments.index') }}" class="sidebar-link {{ request()->routeIs('security.assignments.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i> {{ __('Active Deployments') }}
        </a>
        @endif

        <!-- ACCOUNT -->
        <div class="section-header">{{ __('Profile') }}</div>
        @if(Auth::user()->role === 'security_agency')
            <a href="{{ route('security.profile') }}" class="sidebar-link {{ request()->routeIs('security.profile*') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i> {{ __('Agency Profile') }}
            </a>
        @else
            <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i> {{ __('My Profile') }}
            </a>
        @endif
        <a href="{{ route('notifications.index') }}" class="sidebar-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
            <i class="fas fa-bell"></i> {{ __('Notifications') }}
            @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
            @if($unreadCount > 0) <span class="badge">{{ $unreadCount }}</span> @endif
        </a>
        <a href="{{ route('reminders.index') }}" class="sidebar-link {{ request()->routeIs('reminders.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> {{ __('Reminder Calendar') }}
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="sidebar-link w-full"><i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}</button>
        </form>
        @else
        <div class="px-5 py-4 text-xs text-[#A39B92] space-y-3">
            <p class="text-[#CBD5E1]">{{ __('Access your logistics account') }}</p>
            <a href="{{ route('login') }}" class="sidebar-link !text-white !bg-[#D96B43] hover:!bg-[#C35832] text-center justify-center rounded-xl shadow-sm">
                <i class="fas fa-sign-in-alt"></i> {{ __('Sign In') }}
            </a>
        </div>
        @endauth
    </nav>
</div>

<!-- ============================================================ -->
<!-- MAIN CONTENT -->
<!-- ============================================================ -->
<div class="main-content">
    <div class="top-bar">
        <!-- LEFT: BREADCRUMB & LIVE INDICATOR -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#D96B43] to-[#B85832] text-white flex items-center justify-center font-bold text-sm shadow-xs shadow-[#D96B43]/20">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="flex items-center gap-1.5 text-xs font-semibold">
                    <span class="text-[#8A8379] uppercase tracking-wider">{{ __('Portal') }}</span>
                    <i class="fas fa-chevron-right text-[9px] text-[#C4BCB3]"></i>
                    <span class="text-[#24201D] font-serif font-bold tracking-tight text-sm" id="kwdc-topbar-header">{{ __($__env->yieldContent('header', 'Dashboard')) }}</span>
                </div>
                <div class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#EFF6F1] text-[#3D7357] border border-[#D4E7DC]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#3D7357] animate-pulse"></span>
                    <span>{{ __('Live Hub') }}</span>
                </div>
            </div>
        </div>

        <!-- CENTER: QUICK SEARCH / COMMAND PILL -->
        <div class="hidden lg:flex items-center">
            <button type="button" onclick="event.stopPropagation(); if(window.openKwdcAssistant){window.openKwdcAssistant();}else{document.getElementById('voiceLaunchBtn')?.click();}" id="kwdcTopbarSearchBtn" class="kwdc-topbar-search" aria-label="{{ __('Search or Ask AI...') }}">
                <i class="fas fa-wand-magic-sparkles text-[11px] text-[#D96B43] flex-shrink-0"></i>
                <span class="truncate">{{ __('Search or Ask AI...') }}</span>
                <span class="kwdc-kbd">Ctrl+K</span>
            </button>
        </div>

        <!-- RIGHT: CALENDAR, NOTIFICATIONS & USER PROFILE MENU -->
        <div class="flex items-center gap-3">
            <!-- LANGUAGE SWITCHER (EN / नेपाली) -->
            @php $currentLocale = app()->getLocale(); @endphp
            <div class="inline-flex items-center rounded-full bg-[#FFFFFF] border border-[#E8E2D8] p-0.5 shadow-2xs">
                <a href="{{ route('locale.switch', 'en') }}" class="px-2.5 py-0.5 rounded-full text-xs font-bold transition text-decoration-none {{ $currentLocale === 'en' ? 'bg-[#D96B43] text-white shadow-xs' : 'text-[#57524C] hover:text-[#24201D]' }}" title="Switch to English">
                    EN
                </a>
                <a href="{{ route('locale.switch', 'ne') }}" class="px-2.5 py-0.5 rounded-full text-xs font-bold transition text-decoration-none {{ $currentLocale === 'ne' ? 'bg-[#D96B43] text-white shadow-xs' : 'text-[#57524C] hover:text-[#24201D]' }}" title="नेपाली भाषामा हेर्नुहोस्">
                    नेपाली
                </a>
            </div>

            <!-- CALENDAR DATE PILL -->
            <div class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#FFFFFF] border border-[#E8E2D8] text-xs font-semibold text-[#57524C] shadow-2xs">
                <i class="far fa-calendar-alt text-[#D96B43]"></i>
                <span>{{ now()->format('D, M j, Y') }}</span>
            </div>

            <!-- NOTIFICATION BELL WITH DROPDOWN -->
            <div class="dropdown">
                <a class="w-9 h-9 rounded-full bg-[#FFFFFF] hover:bg-[#FAF8F5] text-[#57524C] hover:text-[#24201D] flex items-center justify-center transition border border-[#E8E2D8] text-decoration-none relative shadow-2xs" href="#" id="notificationDropdownToggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell text-sm"></i>
                    @auth
                        @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                        @if($unreadCount > 0)
                            <span class="badge bg-[#D96B43] text-white rounded-full" id="unreadCount" style="position: absolute; top: -3px; right: -3px; font-size: 9px; min-width: 17px; height: 17px; padding: 0 4px; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 2px #fff;">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    @endauth
                </a>
                <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdownToggle">
                    <li class="dropdown-header"><i class="fas fa-bell me-2"></i>Notifications</li>
                    @auth
                        @if(auth()->user()->notifications->count() > 0)
                            @foreach(auth()->user()->notifications->take(10) as $notification)
                                <li>
                                    <a href="{{ $notification->data['url'] ?? '#' }}" class="dropdown-item notification-item" data-id="{{ $notification->id }}">
                                        <div>
                                            <div class="notification-title">{{ $notification->data['title'] ?? 'Notification' }}</div>
                                            <div class="notification-text">{{ $notification->data['message'] ?? '' }}</div>
                                            <div class="notification-time">{{ $notification->created_at->diffForHumans() }}</div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                            <li class="dropdown-footer">
                                <a href="{{ route('notifications.index') }}" class="text-decoration-none small">View All</a>
                            </li>
                        @else
                            <li class="dropdown-item text-center text-muted">No notifications</li>
                        @endif
                    @endauth
                </ul>
            </div>

            <!-- USER QUICK PROFILE BADGE & DROPDOWN -->
            @auth
            <div class="dropdown">
                <a href="#" class="kwdc-topbar-profile" id="userMenuToggle" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset(auth()->user()->profile_photo) }}" class="kwdc-topbar-avatar" alt="Avatar">
                    @else
                        <div class="kwdc-topbar-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="kwdc-topbar-userinfo">
                        <span class="kwdc-topbar-name">{{ auth()->user()->name }}</span>
                        <span class="kwdc-topbar-role">{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</span>
                    </div>
                    <i class="fas fa-chevron-down kwdc-topbar-chevron"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end kwdc-topbar-menu" aria-labelledby="userMenuToggle">
                    <li class="kwdc-menu-header">
                        <div class="kwdc-menu-user-row">
                            <div class="kwdc-menu-user-avatar">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="kwdc-menu-user-meta">
                                <span class="kwdc-menu-user-name">{{ auth()->user()->name }}</span>
                                <span class="kwdc-menu-user-email">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </li>
                    <li><a class="kwdc-menu-item" href="{{ route('profile.edit') }}"><i class="fas fa-user-gear"></i> Account Settings</a></li>
                    <li><a class="kwdc-menu-item" href="{{ route('notifications.index') }}"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a class="kwdc-menu-item" href="{{ route('reminders.index') }}"><i class="fas fa-calendar-check"></i> Reminder Calendar</a></li>
                    <li class="kwdc-menu-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                            @csrf
                            <button type="submit" class="kwdc-menu-item kwdc-menu-item-logout"><i class="fas fa-arrow-right-from-bracket"></i> Sign Out</button>
                        </form>
                    </li>
                </ul>
            </div>
            @else
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#D96B43] hover:bg-[#C35832] text-white text-xs font-semibold shadow-2xs transition">
                <i class="fas fa-sign-in-alt text-xs"></i>
                <span>Sign In</span>
            </a>
            @endauth
        </div>
    </div>
    
    @if(session('success')) <div class="alert alert-success mx-4 mt-4">{{ session('success') }} <button type="button" class="float-right" onclick="this.parentElement.style.display='none'">&times;</button></div> @endif
    @if(session('error')) <div class="alert alert-danger mx-4 mt-4">{{ session('error') }} <button type="button" class="float-right" onclick="this.parentElement.style.display='none'">&times;</button></div> @endif
    @if(session('warning')) <div class="alert alert-warning mx-4 mt-4">{{ session('warning') }} <button type="button" class="float-right" onclick="this.parentElement.style.display='none'">&times;</button></div> @endif
    @if(session('info')) <div class="alert alert-info mx-4 mt-4">{{ session('info') }} <button type="button" class="float-right" onclick="this.parentElement.style.display='none'">&times;</button></div> @endif
    
    <div class="page-content" id="kwdc-page-content">
        @yield('content')
        <footer class="kwdc-portal-footer mt-12 pt-6 pb-8 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-700"><i class="fas fa-warehouse text-orange-500 mr-1"></i> KTM-WDC</span>
                <span>&bull;</span>
                <span>&copy; {{ date('Y') }} Warehouse & Distribution Connect</span>
            </div>
            <div class="flex items-center gap-5 font-medium">
                <a href="{{ route('privacy-policy') }}" class="hover:text-orange-600 transition">Privacy Policy</a>
                <a href="{{ route('terms-of-service') }}" class="hover:text-orange-600 transition">Terms of Service</a>
                <a href="{{ route('compliance') }}" class="hover:text-orange-600 transition">Compliance & Safety</a>
            </div>
        </footer>
    </div>
</div>

<!-- ============================================================ -->
<!-- SCRIPTS -->
<!-- ============================================================ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/kwdc-flow.js') }}" defer></script>
<script>
    function openMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.querySelector('.menu-toggle');
        sidebar.classList.add('mobile-open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Close navigation');
        sidebar.querySelector('a')?.focus();
    }
    function closeMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.querySelector('.menu-toggle');
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Open navigation');
        toggle.focus();
    }
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar.classList.contains('mobile-open')) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    }
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && document.getElementById('sidebar').classList.contains('mobile-open')) {
            closeMobileMenu();
        }
    });
    // Overlay click always closes (never re-opens)
    document.getElementById('sidebarOverlay').addEventListener('click', closeMobileMenu);
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert-success');
        alerts.forEach(function(alert) { 
            setTimeout(function() { 
                alert.style.transition = 'opacity 0.5s ease'; 
                alert.style.opacity = '0'; 
                setTimeout(function() { alert.style.display = 'none'; }, 500); 
            }, 5000); 
        });
        // Sidebar links close the menu on mobile
        const links = document.querySelectorAll('.sidebar-link');
        links.forEach(function(link) { 
            link.addEventListener('click', function() { 
                if (window.innerWidth <= 768) { closeMobileMenu(); } 
            }); 
        });

        // ===== MARK NOTIFICATION AS READ =====
        const notificationItems = document.querySelectorAll('.notification-item');
        notificationItems.forEach(item => {
            item.addEventListener('click', function(e) {
                const id = this.dataset.id;
                if (id) {
                    fetch('/notifications/' + id + '/mark-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            // Remove badge count
                            const badge = document.getElementById('unreadCount');
                            if (badge) {
                                let count = parseInt(badge.textContent) || 0;
                                count--;
                                if (count > 0) {
                                    badge.textContent = count;
                                } else {
                                    badge.style.display = 'none';
                                }
                            }
                        }
                    })
                    .catch(error => console.error('Error marking notification as read:', error));
                }
            });
        });
    });

    // ===== REAL-TIME NOTIFICATIONS (Laravel Echo) =====
    @auth
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
    if (userId) {
        const loadScript = (src) => new Promise((resolve, reject) => {
            const existing = document.querySelector(`script[src="${src}"]`);
            if (existing) {
                existing.addEventListener('load', resolve, { once: true });
                existing.addEventListener('error', reject, { once: true });
                if (existing.dataset.loaded === 'true') resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = () => {
                script.dataset.loaded = 'true';
                resolve();
            };
            script.onerror = reject;
            document.head.appendChild(script);
        });

        const startRealtimeNotifications = async () => {
            try {
                if (typeof window.Echo === 'undefined') {
                    await loadScript('https://js.pusher.com/8.2.0/pusher.min.js');
                    window.Pusher = window.Pusher || window.pusher;
                    await loadScript('https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js');
                }

                if (typeof window.Echo === 'undefined' && typeof Echo !== 'undefined') {
                    window.Echo = Echo;
                }

                if (typeof window.Echo === 'function') {
                    window.Echo = new window.Echo({
                        broadcaster: 'reverb',
                        key: @json(config('broadcasting.connections.reverb.key')),
                        wsHost: @json(config('broadcasting.connections.reverb.options.host', 'localhost')),
                        wsPort: @json((int) config('broadcasting.connections.reverb.options.port', 8080)),
                        forceTLS: @json((bool) config('broadcasting.connections.reverb.options.useTLS', false)),
                        enabledTransports: ['ws', 'wss'],
                    });
                }

                if (window.Echo?.private) {
                    setupEcho();
                }
            } catch (error) {
                console.warn('Realtime notifications are unavailable on this page.', error);
            }
        };

        startRealtimeNotifications();

        function setupEcho() {
            window.Echo.private('notifications.' + userId)
                .notification((notification) => {
                    // Add to dropdown
                    const dropdown = document.querySelector('.notification-dropdown');
                    if (dropdown) {
                        const newItem = document.createElement('li');
                        newItem.innerHTML = `
                            <a href="${notification.url || '#'}" class="dropdown-item notification-item">
                                <div>
                                    <div class="notification-title">${notification.title || 'New'}</div>
                                    <div class="notification-text">${notification.message || ''}</div>
                                    <div class="notification-time">Just now</div>
                                </div>
                            </a>
                        `;
                        // Insert after header
                        const header = dropdown.querySelector('.dropdown-header');
                        if (header) {
                            dropdown.insertBefore(newItem, header.nextSibling);
                        } else {
                            dropdown.prepend(newItem);
                        }
                        // Update badge
                        const badge = document.getElementById('unreadCount');
                        if (badge) {
                            let count = parseInt(badge.textContent) || 0;
                            badge.textContent = count + 1;
                            badge.style.display = 'flex';
                        } else {
                            const toggle = document.querySelector('#notificationDropdownToggle');
                            if (toggle) {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'badge bg-danger rounded-pill';
                                newBadge.id = 'unreadCount';
                                newBadge.style.cssText = 'position: absolute; top: -5px; right: -5px; font-size: 10px; min-width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;';
                                newBadge.textContent = '1';
                                toggle.appendChild(newBadge);
                            }
                        }
                    }
                });
        }
    }
    @endauth
</script>
@stack('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- ============================================================ -->
<!-- KWDC ASSISTANT & TOPBAR PROFILE STYLES                      -->
<!-- ============================================================ -->
<style>
    /* Topbar Profile Component */
    .kwdc-topbar-profile {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        padding: 3px 10px 3px 4px;
        border-radius: 9999px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        text-decoration: none !important;
        cursor: pointer;
        transition: all 0.16s ease;
    }
    .kwdc-topbar-profile:hover,
    .kwdc-topbar-profile[aria-expanded="true"] {
        background: #f8fafc;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    .kwdc-topbar-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        object-fit: cover;
        background: #0f172a;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .kwdc-topbar-userinfo {
        display: flex;
        flex-direction: column;
        text-align: left;
        line-height: 1.15;
    }
    .kwdc-topbar-name {
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        max-width: 120px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kwdc-topbar-role {
        font-size: 10px;
        font-weight: 500;
        color: #64748b;
        text-transform: capitalize;
    }
    .kwdc-topbar-chevron {
        font-size: 9px;
        color: #94a3b8;
        margin-left: 2px;
        transition: transform 0.18s ease;
    }
    .kwdc-topbar-profile[aria-expanded="true"] .kwdc-topbar-chevron {
        transform: rotate(180deg);
    }
    .kwdc-topbar-menu {
        border-radius: 14px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.12) !important;
        padding: 6px !important;
        min-width: 210px !important;
        background: #ffffff !important;
        margin-top: 6px !important;
    }
    .kwdc-menu-header {
        padding: 8px 10px 10px;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 4px;
    }
    .kwdc-menu-user-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .kwdc-menu-user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #0f172a;
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .kwdc-menu-user-meta {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .kwdc-menu-user-name {
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kwdc-menu-user-email {
        font-size: 11px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kwdc-menu-item {
        display: flex !important;
        align-items: center;
        gap: 9px;
        padding: 7px 10px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        color: #334155 !important;
        border-radius: 8px !important;
        text-decoration: none !important;
        transition: all 0.15s ease !important;
        border: 0 !important;
        background: transparent !important;
        width: 100%;
        text-align: left;
    }
    .kwdc-menu-item i {
        font-size: 12px;
        width: 16px;
        text-align: center;
        color: #94a3b8;
    }
    .kwdc-menu-item:hover {
        background: #f8fafc !important;
        color: #0f172a !important;
    }
    .kwdc-menu-item:hover i {
        color: #f97316;
    }
    .kwdc-menu-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 4px 6px;
    }
    .kwdc-menu-item-logout {
        color: #dc2626 !important;
    }
    .kwdc-menu-item-logout i {
        color: #dc2626 !important;
    }
    .kwdc-menu-item-logout:hover {
        background: #fef2f2 !important;
        color: #b91c1c !important;
    }

    /* Floating AI Copilot Orb - Minimalist */
    .kwdc-assistant {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 99999;
    }
    .kwdc-assistant-launch {
        width: 52px !important;
        height: 52px !important;
        border-radius: 50% !important;
        background: #0f172a !important;
        color: #f97316 !important;
        border: 1.5px solid rgba(249, 115, 22, 0.45) !important;
        box-shadow: 0 10px 25px -4px rgba(15, 23, 42, 0.35), 0 0 16px rgba(249, 115, 22, 0.2) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 19px !important;
        cursor: pointer !important;
        padding: 0 !important;
        margin: 0 !important;
        outline: none !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    .kwdc-assistant-launch:hover {
        transform: translateY(-2px) scale(1.06) !important;
        background: #1e293b !important;
        border-color: #f97316 !important;
        color: #ffffff !important;
        box-shadow: 0 14px 32px -4px rgba(15, 23, 42, 0.45), 0 0 24px rgba(249, 115, 22, 0.4) !important;
    }
    .kwdc-assistant-launch:active {
        transform: translateY(0) scale(0.96) !important;
    }

    /* Minimalist AI Chat Window */
    .kwdc-assistant-panel {
        width: 380px;
        max-width: calc(100vw - 32px);
        height: 540px;
        max-height: calc(100vh - 100px);
        display: none;
        flex-direction: column;
        position: absolute;
        bottom: 66px;
        right: 0;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(15, 23, 42, 0.04);
        animation: kwdcPanelIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes kwdcPanelIn {
        from { opacity: 0; transform: translateY(10px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Clean Header */
    .kwdc-assistant-header {
        padding: 12px 16px;
        background: #0f172a;
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .kwdc-assistant-title {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }
    .kwdc-assistant-icon-wrap {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(249, 115, 22, 0.2);
        border: 1px solid rgba(249, 115, 22, 0.4);
        color: #f97316;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }
    .kwdc-assistant-meta {
        display: flex;
        flex-direction: column;
        line-height: 1.15;
    }
    .kwdc-assistant-name {
        font-size: 13px;
        font-weight: 700;
        color: #ffffff;
    }
    .kwdc-assistant-sub {
        font-size: 10.5px;
        color: #94a3b8;
    }
    .kwdc-assistant-actions {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
    }
    .kwdc-lang-toggle {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 7px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
        color: #cbd5e1;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .kwdc-lang-toggle:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
    }
    .kwdc-lang-toggle .lang-opt.active {
        color: #f97316;
    }
    .kwdc-lang-toggle .lang-divider {
        color: #64748b;
        font-size: 10px;
    }
    .kwdc-assistant-btn {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: transparent;
        border: 0;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .kwdc-assistant-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    /* Messages Stream */
    .kwdc-assistant-messages {
        flex: 1;
        overflow-y: auto;
        padding: 14px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 10px;
        scroll-behavior: smooth;
    }
    .kwdc-assistant-messages::-webkit-scrollbar {
        width: 4px;
    }
    .kwdc-assistant-messages::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }
    .kwdc-assistant-msg {
        display: flex;
        flex-direction: column;
        max-width: 88%;
        animation: kwdcMsgIn 0.16s ease-out;
    }
    @keyframes kwdcMsgIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .kwdc-assistant-msg.user-msg {
        align-self: flex-end;
        align-items: flex-end;
    }
    .kwdc-assistant-msg.assistant-msg {
        align-self: flex-start;
        align-items: flex-start;
    }
    .kwdc-assistant-bubble {
        padding: 9px 13px;
        border-radius: 15px;
        line-height: 1.45;
        font-size: 12.5px;
        white-space: pre-line;
        word-break: break-word;
    }
    .assistant-msg .kwdc-assistant-bubble {
        background: #ffffff;
        color: #0f172a;
        border: 1px solid #e2e8f0;
        border-top-left-radius: 3px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }
    .user-msg .kwdc-assistant-bubble {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #ffffff;
        border-top-right-radius: 3px;
        font-weight: 500;
        box-shadow: 0 2px 6px rgba(249, 115, 22, 0.2);
    }

    /* Minimalist Quick Action Chips */
    .kwdc-quick-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 8px;
        width: 100%;
    }
    .kwdc-chip-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.14s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    .kwdc-chip-btn:hover {
        background: #fff7ed;
        border-color: #f97316;
        color: #ea580c;
        transform: translateY(-1px);
    }

    /* Footer & Input Capsule */
    .kwdc-assistant-footer {
        padding: 10px 12px 10px;
        border-top: 1px solid #e2e8f0;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .kwdc-assistant-hint {
        font-size: 11px;
        color: #64748b;
        font-weight: 500;
        text-align: center;
        min-height: 16px;
    }
    .kwdc-assistant-input-capsule {
        display: flex;
        align-items: center;
        gap: 4px;
        border: 1.5px solid #e2e8f0;
        border-radius: 9999px;
        background: #f8fafc;
        padding: 3px 4px 3px 6px;
        transition: all 0.16s ease;
    }
    .kwdc-assistant-input-capsule:focus-within {
        border-color: #f97316;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
    }
    .kwdc-mic-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e2e8f0;
        border: none;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11.5px;
        cursor: pointer;
        transition: all 0.14s ease;
        flex-shrink: 0;
    }
    .kwdc-mic-btn:hover {
        background: #cbd5e1;
        color: #0f172a;
    }
    .kwdc-mic-btn.listening {
        background: #ef4444;
        color: #ffffff;
        animation: kwdcMicPulse 1.2s infinite;
    }
    @keyframes kwdcMicPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    .kwdc-input-clean {
        flex: 1;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
        min-width: 0;
        padding: 4px 4px;
        font-size: 12.5px;
        color: #0f172a;
    }
    .kwdc-input-clean:focus {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }
    .kwdc-send-btn {
        width: 30px;
        height: 30px;
        border: 0;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 11.5px;
        flex-shrink: 0;
        transition: all 0.16s ease;
    }
    .kwdc-send-btn:hover {
        transform: scale(1.05);
    }
    @media (max-width: 640px) {
        .kwdc-assistant {
            right: 16px;
            bottom: 16px;
        }
        .kwdc-assistant-panel {
            right: -6px;
            width: calc(100vw - 20px);
            height: min(520px, calc(100vh - 80px));
        }
    }
</style>

<div id="voice-assistant-container" class="kwdc-assistant">
    <div class="kwdc-assistant-panel" id="voiceChatWindow">
        <!-- Clean Minimalist Header -->
        <div class="kwdc-assistant-header">
            <div class="kwdc-assistant-title">
                <div class="kwdc-assistant-icon-wrap">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <div class="kwdc-assistant-meta">
                    <span class="kwdc-assistant-name">KWDC Assistant</span>
                    <span class="kwdc-assistant-sub">Logistics Copilot</span>
                </div>
            </div>
            <div class="kwdc-assistant-actions">
                <button type="button" id="voiceLangToggle" class="kwdc-lang-toggle" title="Switch Language">
                    <span class="lang-opt active" id="langOptEn">EN</span>
                    <span class="lang-divider">/</span>
                    <span class="lang-opt" id="langOptNp">NP</span>
                </button>
                <select id="voiceLangSelect" style="display:none;">
                    <option value="en" selected>EN</option>
                    <option value="np">NP</option>
                </select>
                <button id="voiceSpeechToggle" class="kwdc-assistant-btn" aria-label="Toggle voice responses" title="Voice audio response"><i class="fas fa-volume-up"></i></button>
                <button id="assistantClearBtn" class="kwdc-assistant-btn" aria-label="Reset chat" title="Reset chat"><i class="fas fa-rotate-left"></i></button>
                <button id="voiceCloseBtn" class="kwdc-assistant-btn" aria-label="Close" title="Close"><i class="fas fa-xmark"></i></button>
            </div>
        </div>

        <!-- Messages Stream -->
        <div class="kwdc-assistant-messages" id="voiceMessages">
            <div class="kwdc-assistant-msg assistant-msg">
                <div class="kwdc-assistant-bubble">
                    Namaste! I am your KWDC Logistics Copilot. How can I assist you with pickups, dispatches, tracking, or reminders today?
                </div>
                <div class="kwdc-quick-chips" id="assistantQuickActions">
                    <button type="button" class="kwdc-chip-btn" data-prompt="Create a pickup from Boudha to Bhaktapur">📦 Pickup</button>
                    <button type="button" class="kwdc-chip-btn" data-prompt="Create a dispatch from Kathmandu to Pokhara">🚚 Dispatch</button>
                    <button type="button" class="kwdc-chip-btn" data-prompt="Track my dispatch order">📍 Tracking</button>
                    <button type="button" class="kwdc-chip-btn" data-prompt="Remind me to call the driver tomorrow at 5 PM">⏰ Reminder</button>
                </div>
            </div>
        </div>

        <!-- Minimalist Footer -->
        <div class="kwdc-assistant-footer">
            <div class="kwdc-assistant-hint" id="voiceStatus">Type a request or tap mic to speak</div>
            <div class="kwdc-assistant-input-capsule">
                <button id="voiceToggleBtn" class="kwdc-mic-btn" aria-label="Toggle microphone" title="Voice Input"><i class="fas fa-microphone"></i></button>
                <input type="text" id="voiceTextInput" class="kwdc-input-clean" placeholder="Ask anything (e.g. 'Pickup to Thamel')...">
                <button id="voiceSendBtn" class="kwdc-send-btn" aria-label="Send message" title="Send"><i class="fas fa-arrow-up"></i></button>
            </div>
        </div>
    </div>

    <!-- Minimalist Floating Orb -->
    <button id="voiceLaunchBtn" class="kwdc-assistant-launch" aria-label="Open KWDC AI Copilot" title="AI Copilot">
        <i class="fas fa-wand-magic-sparkles"></i>
    </button>
</div>

<script src="{{ asset('js/voice-assistant.js') }}"></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW registration error:', err));
        });
    }
</script>
@stack('scripts')
</body>
</html>
