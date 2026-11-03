@php
        $user = Auth::user();
    $role = $user->role ?? 'client';
    
    // Check if notifications table has is_read column
    $unreadCount = 0;
    try {
        $unreadCount = \App\Models\Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    } catch (\Exception $e) {
        // Column doesn't exist yet - skip
    }
    
    $pendingCount = \App\Models\Warehouse::where('status', 'pending')->count();
@endphp


<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}">
            <div class="brand-icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">KTM-WDC</span>
                <span class="brand-tagline">Warehouse & Distribution</span>
            </div>
        </a>
    </div>

    <!-- User Profile -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f59e0b&color=fff&size=50" 
                 alt="{{ $user->name }}">
            <span class="user-status online"></span>
        </div>
        <div class="user-info">
            <div class="user-name">{{ $user->name }}</div>
            <div class="user-email">{{ $user->email }}</div>
            <div class="user-role">
                <span class="role-badge">{{ ucfirst($role) }}</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <div class="nav-section">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- ============================================================ -->
        <!-- CLIENT SECTION -->
        <!-- ============================================================ -->
        @if(in_array($role, ['client', 'admin']))
        <div class="nav-section">
            <div class="nav-header">CLIENT</div>
            
            <a href="{{ route('my-requests.index') }}" class="nav-link {{ request()->routeIs('my-requests.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>My Requests</span>
                @php
                    $pendingRequests = \App\Models\WarehouseRequest::where('client_id', $user->id)
                        ->where('status', 'pending')
                        ->count();
                @endphp
                @if($pendingRequests > 0)
                    <span class="nav-badge">{{ $pendingRequests }}</span>
                @endif
            </a>

            <a href="{{ route('my-requests.create') }}" class="nav-link {{ request()->routeIs('my-requests.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                <span>New Request</span>
            </a>

            <a href="{{ route('stock.index') }}" class="nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                <i class="fas fa-cubes"></i>
                <span>My Stock</span>
                @php
                    $stockCount = \App\Models\Stock::where('user_id', $user->id)->count();
                @endphp
                @if($stockCount > 0)
                    <span class="nav-badge">{{ $stockCount }}</span>
                @endif
            </a>

            <a href="{{ route('client.proposals') }}" class="nav-link {{ request()->routeIs('client.proposals') ? 'active' : '' }}">
                <i class="fas fa-file-contract"></i>
                <span>My Proposals</span>
            </a>

            <a href="{{ route('client.equipment.requests') }}" class="nav-link {{ request()->routeIs('client.equipment.requests') ? 'active' : '' }}">
    <i class="fas fa-tools"></i>
    <span>Equipment Requests</span>
    @php
        $equipmentCount = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('equipment_requests')) {
                $equipmentCount = \App\Models\EquipmentRequest::where('client_id', $user->id)
                    ->where('status', 'pending')
                    ->count();
            }
        } catch (\Exception $e) {
            $equipmentCount = 0;
        }
    @endphp
    @if($equipmentCount > 0)
        <span class="nav-badge">{{ $equipmentCount }}</span>
    @endif
</a>


            <a href="{{ route('pickup.index') }}" class="nav-link {{ request()->routeIs('pickup.*') ? 'active' : '' }}">
                <i class="fas fa-truck"></i>
                <span>Pickups</span>
                @php
                    $pickupCount = \App\Models\PickupRequest::where('client_id', $user->id)
                        ->where('status', 'pending')
                        ->count();
                @endphp
                @if($pickupCount > 0)
                    <span class="nav-badge">{{ $pickupCount }}</span>
                @endif
            </a>

            <a href="{{ route('dispatch.index') }}" class="nav-link {{ request()->routeIs('dispatch.*') ? 'active' : '' }}">
                <i class="fas fa-truck-moving"></i>
                <span>Dispatch Orders</span>
            </a>

            <a href="{{ route('invoices.client-index') }}" class="nav-link {{ request()->routeIs('invoices.client-index') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i>
                <span>My Invoices</span>
                @php
                    $invoiceCount = \App\Models\Invoice::where('client_id', $user->id)
                        ->where('status', 'pending')
                        ->count();
                @endphp
                @if($invoiceCount > 0)
                    <span class="nav-badge">{{ $invoiceCount }}</span>
                @endif
            </a>

            <a href="{{ route('client.driver.rates') }}" class="nav-link {{ request()->routeIs('client.driver.rates') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave"></i>
                <span>Driver Rates</span>
            </a>

            <a href="{{ route('client.reports') }}" class="nav-link {{ request()->routeIs('client.reports') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </div>
        @endif

        <!-- ============================================================ -->
        <!-- DRIVER SECTION -->
        <!-- ============================================================ -->
        @if(in_array($role, ['driver', 'admin']))
        <div class="nav-section">
            <div class="nav-header">DRIVER</div>
            
            <a href="{{ route('driver.dashboard') }}" class="nav-link {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Driver Dashboard</span>
            </a>

            <a href="{{ route('driver.available-jobs') }}" class="nav-link {{ request()->routeIs('driver.available-jobs') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Available Jobs</span>
                @php
                    $availableJobs = \App\Models\DispatchOrder::where('status', 'pending')->count();
                @endphp
                @if($availableJobs > 0)
                    <span class="nav-badge">{{ $availableJobs }}</span>
                @endif
            </a>

            <a href="{{ route('driver.jobs') }}" class="nav-link {{ request()->routeIs('driver.jobs') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i>
                <span>My Jobs</span>
                @php
                    $myJobs = \App\Models\DispatchOrder::where('driver_id', $user->id)
                        ->whereIn('status', ['assigned', 'picked_up', 'on_the_way'])
                        ->count();
                @endphp
                @if($myJobs > 0)
                    <span class="nav-badge">{{ $myJobs }}</span>
                @endif
            </a>

            <a href="{{ route('driver.pickups') }}" class="nav-link {{ request()->routeIs('driver.pickups') ? 'active' : '' }}">
                <i class="fas fa-warehouse"></i>
                <span>Pickup Jobs</span>
            </a>

            <a href="{{ route('driver.vehicles.index') }}" class="nav-link {{ request()->routeIs('driver.vehicles.*') ? 'active' : '' }}">
                <i class="fas fa-car"></i>
                <span>My Vehicles</span>
                @php
                    $vehicleCount = \App\Models\Vehicle::where('driver_id', $user->id)->count();
                @endphp
                @if($vehicleCount > 0)
                    <span class="nav-badge">{{ $vehicleCount }}</span>
                @endif
            </a>

            <a href="{{ route('driver.rates') }}" class="nav-link {{ request()->routeIs('driver.rates.*') ? 'active' : '' }}">
                <i class="fas fa-dollar-sign"></i>
                <span>My Rates</span>
            </a>

            <a href="{{ route('driver.earnings') }}" class="nav-link {{ request()->routeIs('driver.earnings') ? 'active' : '' }}">
                <i class="fas fa-wallet"></i>
                <span>Earnings</span>
            </a>
        </div>
        @endif

        <!-- ============================================================ -->
        <!-- EQUIPMENT OWNER SECTION -->
        <!-- ============================================================ -->
        @if(in_array($role, ['equipment_owner', 'admin']))
        <div class="nav-section">
            <div class="nav-header">EQUIPMENT</div>
            
            <a href="{{ route('equipment.dashboard') }}" class="nav-link {{ request()->routeIs('equipment.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Equipment Dashboard</span>
            </a>

            <a href="{{ route('equipment.list') }}" class="nav-link {{ request()->routeIs('equipment.list') ? 'active' : '' }}">
                <i class="fas fa-tools"></i>
                <span>My Equipment</span>
                @php
                    $equipmentCount = \App\Models\Equipment::where('user_id', $user->id)->count();
                @endphp
                @if($equipmentCount > 0)
                    <span class="nav-badge">{{ $equipmentCount }}</span>
                @endif
            </a>

            <a href="{{ route('equipment.register') }}" class="nav-link {{ request()->routeIs('equipment.register') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                <span>Register Equipment</span>
            </a>

            <a href="{{ route('equipment.jobs.index') }}" class="nav-link {{ request()->routeIs('equipment.jobs.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i>
                <span>Equipment Jobs</span>
                @php
                    $jobRequests = \App\Models\EquipmentJob::where('owner_id', $user->id)
                        ->where('status', 'pending')
                        ->count();
                @endphp
                @if($jobRequests > 0)
                    <span class="nav-badge">{{ $jobRequests }}</span>
                @endif
            </a>

            <a href="{{ route('equipment.earnings') }}" class="nav-link {{ request()->routeIs('equipment.earnings') ? 'active' : '' }}">
                <i class="fas fa-wallet"></i>
                <span>Earnings</span>
            </a>
        </div>
        @endif

        <!-- ============================================================ -->
        <!-- PROPERTY OWNER SECTION -->
        <!-- ============================================================ -->
        @if(in_array($role, ['property_owner', 'admin']))
        <div class="nav-section">
            <div class="nav-header">PROPERTY</div>
            
            <a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.index') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>My Properties</span>
                @php
                    $propertyCount = \App\Models\Warehouse::where('user_id', $user->id)->count();
                @endphp
                @if($propertyCount > 0)
                    <span class="nav-badge">{{ $propertyCount }}</span>
                @endif
            </a>

            <a href="{{ route('warehouses.create') }}" class="nav-link {{ request()->routeIs('warehouses.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                <span>Add Property</span>
            </a>

            <a href="{{ route('property.pending') }}" class="nav-link {{ request()->routeIs('property.pending') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                <span>Pending Approval</span>
                @php
                    $pendingProperties = \App\Models\Warehouse::where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->count();
                @endphp
                @if($pendingProperties > 0)
                    <span class="nav-badge">{{ $pendingProperties }}</span>
                @endif
            </a>
        </div>
        @endif

        <!-- ============================================================ -->
        <!-- SECURITY AGENCY SECTION -->
        <!-- ============================================================ -->
        @if(in_array($role, ['security_agency', 'admin']))
        <div class="nav-section">
            <div class="nav-header">SECURITY</div>

            <a href="{{ route('security.dashboard') }}" class="nav-link {{ request()->routeIs('security.dashboard') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i>
                <span>Security Dashboard</span>
            </a>

            <a href="{{ route('security.personnel.index') }}" class="nav-link {{ request()->routeIs('security.personnel.*') ? 'active' : '' }}">
                <i class="fas fa-user-plus"></i>
                <span>Add Personnel</span>
            </a>

            <a href="{{ route('security.goods.index') }}" class="nav-link {{ request()->routeIs('security.goods.*') ? 'active' : '' }}">
                <i class="fas fa-boxes"></i>
                <span>My Goods</span>
            </a>

            <a href="{{ route('security.assignments.index') }}" class="nav-link {{ request()->routeIs('security.assignments.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i>
                <span>Assignments</span>
            </a>
        </div>
        @endif

        <!-- ============================================================ -->
        <!-- ADMIN SECTION -->
        <!-- ============================================================ -->
        @if($role === 'admin')
        <div class="nav-section">
            <div class="nav-header">ADMIN</div>
            
            <a href="{{ route('admin.pending') }}" class="nav-link {{ request()->routeIs('admin.pending') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Admin Dashboard</span>
            </a>

            <a href="{{ route('admin.all-warehouses') }}" class="nav-link {{ request()->routeIs('admin.all-warehouses') ? 'active' : '' }}">
                <i class="fas fa-warehouse"></i>
                <span>All Warehouses</span>
                @if($pendingCount > 0)
                    <span class="nav-badge">{{ $pendingCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.requests') }}" class="nav-link {{ request()->routeIs('admin.requests') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Client Requests</span>
            </a>

            <a href="{{ route('admin.security.agencies') }}" class="nav-link {{ request()->routeIs('admin.security.*') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i>
                <span>Security Agencies</span>
            </a>

            <a href="{{ route('admin.clients') }}" class="nav-link {{ request()->routeIs('admin.clients') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Clients</span>
                @php
                    $clientCount = \App\Models\User::where('role', 'client')->count();
                @endphp
                @if($clientCount > 0)
                    <span class="nav-badge">{{ $clientCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.drivers') }}" class="nav-link {{ request()->routeIs('admin.drivers') ? 'active' : '' }}">
                <i class="fas fa-user-tie"></i>
                <span>Drivers</span>
                @php
                    $driverCount = \App\Models\User::where('role', 'driver')->count();
                @endphp
                @if($driverCount > 0)
                    <span class="nav-badge">{{ $driverCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.equipment-owners') }}" class="nav-link {{ request()->routeIs('admin.equipment-owners') ? 'active' : '' }}">
                <i class="fas fa-tools"></i>
                <span>Equipment Owners</span>
            </a>

            <a href="{{ route('admin.property-owners') }}" class="nav-link {{ request()->routeIs('admin.property-owners') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>Property Owners</span>
            </a>

            <a href="{{ route('admin.vehicles') }}" class="nav-link {{ request()->routeIs('admin.vehicles') ? 'active' : '' }}">
                <i class="fas fa-truck"></i>
                <span>Vehicles</span>
                @php
                    $vehicleCount = \App\Models\Vehicle::count();
                @endphp
                @if($vehicleCount > 0)
                    <span class="nav-badge">{{ $vehicleCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.equipment-list') }}" class="nav-link {{ request()->routeIs('admin.equipment-list') ? 'active' : '' }}">
                <i class="fas fa-tools"></i>
                <span>Equipment</span>
            </a>

            <a href="{{ route('admin.dispatch') }}" class="nav-link {{ request()->routeIs('admin.dispatch') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i>
                <span>Dispatch Orders</span>
                @php
                    $pendingDispatch = \App\Models\DispatchOrder::where('status', 'pending')->count();
                @endphp
                @if($pendingDispatch > 0)
                    <span class="nav-badge">{{ $pendingDispatch }}</span>
                @endif
            </a>

            <a href="{{ route('admin.pickup') }}" class="nav-link {{ request()->routeIs('admin.pickup') ? 'active' : '' }}">
                <i class="fas fa-boxes"></i>
                <span>Pickup Requests</span>
            </a>

            <a href="{{ route('admin.stocks') }}" class="nav-link {{ request()->routeIs('admin.stocks') ? 'active' : '' }}">
                <i class="fas fa-cubes"></i>
                <span>Stock Management</span>
            </a>

            <a href="{{ route('admin.invoices') }}" class="nav-link {{ request()->routeIs('admin.invoices') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i>
                <span>Invoices</span>
                @php
                    $pendingInvoices = \App\Models\Invoice::where('status', 'pending')->count();
                @endphp
                @if($pendingInvoices > 0)
                    <span class="nav-badge">{{ $pendingInvoices }}</span>
                @endif
            </a>

            <a href="{{ route('admin.margins.index') }}" class="nav-link {{ request()->routeIs('admin.margins.*') ? 'active' : '' }}">
                <i class="fas fa-percent"></i>
                <span>Margins & Tiers</span>
            </a>

            <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>

            <a href="{{ route('admin.partner-earnings') }}" class="nav-link {{ request()->routeIs('admin.partner-earnings') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd"></i>
                <span>Partner Earnings</span>
            </a>

            <a href="{{ route('admin.insurance.list') }}" class="nav-link {{ request()->routeIs('admin.insurance.list') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i>
                <span>Insurance</span>
            </a>

            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.index') ? 'active' : '' }}">
                <i class="fas fa-user-tag"></i>
                <span>Roles</span>
            </a>
        </div>
        @endif

        <!-- ============================================================ -->
        <!-- ACCOUNT SECTION -->
        <!-- ============================================================ -->
        <div class="nav-section">
            <div class="nav-header">ACCOUNT</div>

            @if(auth()->user()->role === 'security_agency')
                <a href="{{ route('security.profile') }}" class="nav-link {{ request()->routeIs('security.profile*') ? 'active' : '' }}">
                    <i class="fas fa-id-card"></i>
                    <span>Agency Profile</span>
                </a>
            @else
                <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            @endif

            <a href="{{ route('profile.contacts') }}" class="nav-link {{ request()->routeIs('profile.contacts') ? 'active' : '' }}">
                <i class="fas fa-address-book"></i>
                <span>Manage Contacts</span>
            </a>

            <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                @if($unreadCount > 0)
                    <span class="nav-badge">{{ $unreadCount }}</span>
                @endif
            </a>

            <a href="{{ route('reminders.index') }}" class="nav-link {{ request()->routeIs('reminders.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span>Reminder Calendar</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <p class="text-center text-xs text-gray-500">
            KTM-WDC v1.0<br>
            &copy; {{ date('Y') }} All rights reserved
        </p>
    </div>
</aside>

<!-- ============================================================ -->
<!-- STYLES -->
<!-- ============================================================ -->
<style>
    /* ===== SIDEBAR CONTAINER ===== */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 280px;
        height: 100vh;
        background: #111827;
        color: #d1d5db;
        z-index: 50;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
    }

    .sidebar::-webkit-scrollbar {
        width: 5px;
    }
    .sidebar::-webkit-scrollbar-track {
        background: #1f2937;
    }
    .sidebar::-webkit-scrollbar-thumb {
        background: #f59e0b;
        border-radius: 10px;
    }

    /* ===== BRAND ===== */
    .sidebar-brand {
        padding: 20px 24px;
        border-bottom: 1px solid #1f2937;
        flex-shrink: 0;
    }

    .sidebar-brand a {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: white;
    }

    .brand-icon {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }

    .brand-name {
        font-size: 20px;
        font-weight: 700;
        color: white;
        display: block;
        line-height: 1.2;
    }

    .brand-tagline {
        font-size: 11px;
        color: #9ca3af;
        display: block;
        font-weight: 400;
    }

    /* ===== USER PROFILE ===== */
    .sidebar-user {
        padding: 16px 20px;
        border-bottom: 1px solid #1f2937;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .user-avatar {
        position: relative;
        flex-shrink: 0;
    }

    .user-avatar img {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 2px solid #f59e0b;
    }

    .user-status {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #111827;
    }

    .user-status.online {
        background: #22c55e;
    }

    .user-info {
        min-width: 0;
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        color: white;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-email {
        font-size: 12px;
        color: #9ca3af;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        margin-top: 2px;
    }

    .role-badge {
        display: inline-block;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 10px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== NAVIGATION ===== */
    .sidebar-nav {
        flex: 1;
        padding: 8px 0 20px 0;
        overflow-y: auto;
    }

    .nav-section {
        margin-bottom: 4px;
    }

    .nav-header {
        font-size: 10px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 12px 24px 4px 24px;
        position: relative;
    }

    .nav-header::after {
        content: '';
        position: absolute;
        left: 24px;
        right: 24px;
        bottom: -2px;
        height: 1px;
        background: linear-gradient(to right, #374151, transparent);
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 9px 20px 9px 24px;
        color: #9ca3af;
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 14px;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        position: relative;
    }

    .nav-link:hover {
        color: white;
        background: rgba(255, 255, 255, 0.05);
    }

    .nav-link.active {
        color: white;
        background: rgba(245, 158, 11, 0.15);
    }

    .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #f59e0b;
        border-radius: 0 3px 3px 0;
    }

    .nav-link i {
        width: 20px;
        font-size: 16px;
        text-align: center;
        color: #6b7280;
        transition: color 0.2s ease;
        flex-shrink: 0;
    }

    .nav-link:hover i,
    .nav-link.active i {
        color: #f59e0b;
    }

    .nav-link span {
        flex: 1;
    }

    .nav-badge {
        background: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 1px 8px;
        border-radius: 10px;
        min-width: 20px;
        text-align: center;
        flex-shrink: 0;
    }

    /* Logout button */
    .logout-form {
        margin: 0;
        padding: 0;
    }

    .logout-form .nav-link {
        padding: 9px 20px 9px 24px;
        color: #9ca3af;
    }

    .logout-form .nav-link:hover {
        color: #ef4444;
        background: rgba(239, 68, 68, 0.1);
    }

    .logout-form .nav-link:hover i {
        color: #ef4444;
    }

    /* ===== FOOTER ===== */
    .sidebar-footer {
        padding: 12px 20px;
        border-top: 1px solid #1f2937;
        flex-shrink: 0;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            width: 280px;
        }
        .sidebar.mobile-open {
            transform: translateX(0);
        }
    }
</style>

<!-- ============================================================ -->
<!-- SCRIPTS -->
<!-- ============================================================ -->
<script>
    // Close sidebar on mobile when clicking a link
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('.nav-link');
        const sidebar = document.getElementById('sidebar');
        
        links.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-open');
                }
            });
        });
    });
</script>
