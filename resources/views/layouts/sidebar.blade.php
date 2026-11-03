@php
    $user = auth()->user();
    $role = $user->role ?? 'client';
@endphp

<!-- Sidebar -->
<div class="fixed left-0 top-0 h-full w-64 bg-gray-900 text-white shadow-lg z-10">
    <div class="p-4 border-b border-gray-700">
        <h2 class="text-xl font-bold">KTM-WDC</h2>
        <p class="text-sm text-gray-400">Warehouse & Distribution</p>
    </div>
    
    <div class="p-4 border-b border-gray-700">
        <p class="text-xs text-gray-400 uppercase tracking-wider">Welcome</p>
        <p class="font-semibold">{{ $user->name }}</p>
        <p class="text-xs text-gray-400 capitalize">{{ $role }}</p>
    </div>
    
    <nav class="flex-1 overflow-y-auto py-4">
        <!-- Common Menu for All Roles -->
        <div class="px-4 mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Main</p>
        </div>
        
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Dashboard
        </a>
        
        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            My Profile
        </a>
        
        <!-- ADMIN MENU -->
        @if($role == 'admin')
            <div class="px-4 mt-4 mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Administration</p>
            </div>
            
            <a href="{{ route('admin.warehouses.pending') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Approve Warehouses
                <span class="ml-auto bg-red-500 text-xs px-2 py-1 rounded-full">
                    {{ \App\Models\Warehouse::where('status', 'pending')->count() }}
                </span>
            </a>
            
            <a href="{{ route('admin.warehouses.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                All Warehouses
            </a>
            
            <a href="{{ route('admin.clients') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Clients
            </a>
            
            <a href="{{ route('admin.drivers') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Drivers
            </a>
            
            <a href="{{ route('admin.vehicles') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Vehicles
            </a>
            
            <a href="{{ route('admin.dispatches.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Dispatches
            </a>
            
            <a href="{{ route('admin.invoices') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Invoices
            </a>
            
            <a href="{{ route('admin.reports') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Reports
            </a>
        @endif
        
        <!-- CLIENT MENU -->
        @if($role == 'client')
            <div class="px-4 mt-4 mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Warehouse</p>
            </div>
            
            <a href="{{ route('warehouses.search') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Find Warehouses
            </a>
            
            <a href="{{ route('my-requests.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                My Requests
            </a>
            
            <div class="px-4 mt-4 mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Inventory</p>
            </div>
            
            <a href="{{ route('stock.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                Stock Management
            </a>
            
            <div class="px-4 mt-4 mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Transport</p>
            </div>
            
            <a href="{{ route('dispatch.create') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 18H6a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v1M8 18h8m-8 0v2m0-2h8a2 2 0 002-2v-2m0 0h-4m4 0v-2m0 0h-4m4 0V8m0 0h-4m4 0V5a2 2 0 00-2-2h-2"></path>
                </svg>
                New Dispatch
            </a>
            
            <a href="{{ route('dispatch.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                My Dispatches
            </a>
            
            <a href="{{ route('pickup.create') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
                New Pickup
            </a>
            
            <a href="{{ route('invoices.client-index') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Invoices
            </a>
        @endif
        
        <!-- DRIVER MENU -->
        @if($role == 'driver')
            <div class="px-4 mt-4 mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Jobs</p>
            </div>
            
            <a href="{{ route('driver.available-jobs') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Available Jobs
                <span class="ml-auto bg-green-500 text-xs px-2 py-1 rounded-full">
                    {{ \App\Models\DispatchOrder::where('status', 'pending')->count() }}
                </span>
            </a>
            
            <a href="{{ route('driver.jobs') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                My Active Jobs
            </a>
            
            <div class="px-4 mt-4 mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Vehicle</p>
            </div>
            
            <a href="{{ route('driver.vehicles.create') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Register Vehicle
            </a>
            
            <a href="{{ route('driver.rates') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Set Daily Rates
            </a>
            
            <a href="{{ route('driver.earnings') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Earnings
            </a>
        @endif
        
        <!-- EQUIPMENT OWNER MENU -->
        @if($role == 'equipment_owner')
            <div class="px-4 mt-4 mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Equipment</p>
            </div>
            
            <a href="{{ route('equipment.register') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Register Equipment
            </a>
            
            <a href="{{ route('equipment.list') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                My Equipment
            </a>
            
            <a href="{{ route('equipment.jobs.requests') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"></path>
                </svg>
                Job Requests
            </a>
            
            <a href="{{ route('equipment.jobs.active') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Active Jobs
            </a>
            
            <a href="{{ route('equipment.jobs.earnings') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Earnings
            </a>
        @endif
        
        <!-- PROPERTY OWNER MENU -->
        @if($role == 'property_owner')
            <div class="px-4 mt-4 mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Properties</p>
            </div>
            
            <a href="{{ route('warehouses.create') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Register Warehouse
            </a>
            
            <a href="{{ route('warehouses.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                My Warehouses
            </a>
            
            <a href="{{ route('property.requests.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Warehouse Requests
            </a>
        @endif
        
        <!-- Common Footer Menu -->
        <div class="px-4 mt-4 mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Account</p>
        </div>
        
        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Settings
        </a>
        
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-2 hover:bg-gray-800 transition text-left">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </button>
        </form>
    </nav>
</div>