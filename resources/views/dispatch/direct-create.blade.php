@extends('layouts.app')

@section('title', 'Create New Dispatch')
@section('header', 'Create New Dispatch')

@section('content')
<style>
    .stop-card {
        transition: all 0.3s ease;
    }
    .stop-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .map-container {
        height: 400px;
        border-radius: 12px;
        overflow: hidden;
        z-index: 1;
    }
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
    }
    .loading-content {
        background: white;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
    }
    .error-text {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
</style>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-[#E8E2D8]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-[#D96B43]/10 text-[#D96B43] border border-[#D96B43]/20">
                    Freight Dispatch
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D] tracking-tight" style="font-family: 'Newsreader', Georgia, serif;">Create New Dispatch</h1>
            <p class="text-sm text-[#645D56] mt-0.5">Map multi-stop highway routes, estimate fair pricing with AI, and assign verified drivers.</p>
        </div>
        <div>
            <a href="{{ route('dispatch.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-[#24201D] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition shadow-2xs">
                <i class="fas fa-arrow-left text-[#D96B43]"></i>
                <span>Back to Dispatches</span>
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="dispatchForm" method="POST" action="{{ route('dispatch.store') }}">
        @csrf
        
        {{-- Hidden fields for price and distance --}}
        <input type="hidden" name="total_price" id="total_price" value="0">
        <input type="hidden" name="total_distance" id="total_distance" value="0">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form - 2 columns -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Pickup Information -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
                        Pickup Information
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pickup Address <span class="text-red-500">*</span></label>
                            <input type="text" name="pickup_address" id="pickup_address" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                   placeholder="Pickup location" value="{{ old('pickup_address') }}">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
                                <input type="text" name="pickup_contact_person" id="pickup_contact_person"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       placeholder="Contact person name" value="{{ old('pickup_contact_person') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                <input type="tel" name="pickup_contact_phone" id="pickup_contact_phone"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       placeholder="Contact phone number" value="{{ old('pickup_contact_phone') }}">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <input type="hidden" name="pickup_latitude" id="pickup_latitude">
                            <input type="hidden" name="pickup_longitude" id="pickup_longitude">
                        </div>
                    </div>
                </div>

                <!-- Delivery Stops -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold flex items-center">
                            <i class="fas fa-map-pin text-red-500 mr-2"></i>
                            Delivery Stops
                        </h3>
                        <button type="button" onclick="addStop()" class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 rounded-lg text-sm transition">
                            <i class="fas fa-plus mr-1"></i>Add Stop
                        </button>
                    </div>
                    
                    <div id="stops-container" class="space-y-4">
                        <!-- Stop 1 will be added dynamically -->
                    </div>
                    
                    <template id="stop-template">
                        <div class="stop-card bg-gray-50 rounded-lg p-4 border border-gray-200 relative">
                            <button type="button" onclick="removeStop(this)" class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                                    <input type="text" name="delivery_stops[__INDEX__][address]" class="stop-address w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Delivery location" required>
                                    <input type="hidden" name="delivery_stops[__INDEX__][latitude]" class="stop-latitude">
                                    <input type="hidden" name="delivery_stops[__INDEX__][longitude]" class="stop-longitude">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Name</label>
                                    <input type="text" name="delivery_stops[__INDEX__][recipient_name]" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Full name" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Phone</label>
                                    <input type="tel" name="delivery_stops[__INDEX__][recipient_phone]" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Phone number" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                    <textarea name="delivery_stops[__INDEX__][notes]" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Notes"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Route Map Display -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-map text-purple-500 mr-2"></i>
                        Route Visualization
                    </h3>
                    <div id="map" class="map-container"></div>
                    <p class="text-xs text-gray-500 mt-2">Addresses pin automatically.</p>
                </div>

                <!-- Assigned Warehouses -->
                @if(isset($assignedWarehouses) && $assignedWarehouses->count() > 0)
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-warehouse text-blue-500 mr-2"></i>
                        Your Assigned Warehouses
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($assignedWarehouses as $warehouse)
                        <div class="border rounded-lg p-3 hover:bg-orange-50 cursor-pointer transition warehouse-card"
                             data-address="{{ $warehouse->address ?? $warehouse->location }}"
                             data-name="{{ $warehouse->name }}"
                             onclick="selectWarehouse(this)">
                            <p class="font-semibold">{{ $warehouse->name }}</p>
                            <p class="text-sm text-gray-500">{{ $warehouse->address ?? $warehouse->location }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Stock Items -->
                @if(isset($stocks) && $stocks->count() > 0)
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-boxes text-purple-500 mr-2"></i>
                        Available Stock Items
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($stocks as $stock)
                        <div class="border rounded-lg p-3 hover:bg-purple-50 cursor-pointer transition"
                             onclick="addStockToDispatch('{{ $stock->product_name }}', {{ $stock->id }})">
                            <p class="font-semibold">{{ $stock->product_name }}</p>
                            <p class="text-sm text-gray-500">SKU: {{ $stock->sku }} | Qty: {{ $stock->remaining_quantity ?? $stock->total_quantity }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Bill Details -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-file-invoice text-gray-500 mr-2"></i>
                        Billing Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bill Type</label>
                            <select name="bill_type" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="regular">Regular</option>
                                <option value="vat">VAT Registered</option>
                                <option value="pan">PAN Number</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">PAN Number (if applicable)</label>
                            <input type="text" name="pan_number" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Enter PAN number" value="{{ old('pan_number') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - 1 column -->
            <div class="space-y-6">
                <!-- AI Route & Cargo Advisor -->
                <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-xl shadow-md p-5 text-white border border-indigo-500/25">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 border border-orange-500/30 flex items-center justify-center text-sm">
                                <i class="fas fa-wand-magic-sparkles"></i>
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-white leading-tight">AI Route Advisor</h4>
                                <p class="text-[11px] text-indigo-200/70">Nepal Highway & Rates Engine</p>
                            </div>
                        </div>
                        <button type="button" id="btnAiDispatchAdvisor" onclick="runAiDispatchAdvisor()" class="text-xs px-2.5 py-1 rounded-md bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-semibold transition flex items-center gap-1 shadow-sm">
                            <i class="fas fa-bolt text-[10px]"></i> Advise
                        </button>
                    </div>

                    <div id="aiDispatchLoading" class="hidden text-center py-4 text-xs text-indigo-200">
                        <div class="spinner-border spinner-border-sm text-orange-400 mb-1" role="status"></div>
                        <p>Analyzing Nepal terrain, route & fair rates...</p>
                    </div>

                    <div id="aiDispatchOutput" class="hidden space-y-2.5 text-xs">
                        <div class="bg-white/10 rounded-lg p-3 border border-white/10">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-slate-300 font-medium">Suggested Vehicle:</span>
                                <span id="aiSuggestedVehicle" class="font-bold text-amber-300"></span>
                            </div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-slate-300 font-medium">Estimated Transit:</span>
                                <span id="aiEstimatedTransit" class="font-semibold text-white"></span>
                            </div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-slate-300 font-medium">Fair Rate Est:</span>
                                <span id="aiSuggestedPrice" class="font-extrabold text-emerald-400"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-300 font-medium">Est. Distance:</span>
                                <span id="aiSuggestedDistance" class="font-semibold text-white"></span>
                            </div>
                        </div>

                        <div class="bg-white/5 rounded-lg p-2.5 border border-white/5 text-[11px] text-slate-200">
                            <p class="font-semibold text-orange-300 mb-0.5"><i class="fas fa-route mr-1"></i>Route Advisory:</p>
                            <p id="aiRouteAdvisory" class="text-slate-300 leading-relaxed mb-2"></p>
                            <p class="font-semibold text-orange-300 mb-0.5"><i class="fas fa-shield-alt mr-1"></i>Precautions:</p>
                            <p id="aiHandlingPrecautions" class="text-slate-300 leading-relaxed"></p>
                        </div>

                        <button type="button" onclick="applyAiDispatchAdvice()" class="w-full py-1.5 px-2 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                            <i class="fas fa-check-circle"></i> Apply AI Recommendations
                        </button>
                    </div>
                </div>

                <!-- Vehicle Type (NEW) -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-truck text-blue-500 mr-2"></i>
                        Vehicle Type
                    </h3>
                    <select name="vehicle_type" id="vehicle_type" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="Standard">Standard</option>
                        <option value="Heavy">Heavy</option>
                        <option value="Refrigerated">Refrigerated</option>
                        <option value="Two-Wheeler">Two-Wheeler</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Used for matching and pricing.</p>
                </div>

                <!-- Drivers Section -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold flex items-center">
                            <i class="fas fa-truck text-orange-500 mr-2"></i>
                            Select Driver
                        </h3>
                        <button type="button" id="findDriversBtn" class="text-sm bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg transition">
                            <i class="fas fa-robot mr-1"></i> AI Recommend
                        </button>
                    </div>
                    
                    <div id="driver-loading" class="hidden text-center py-3">
                        <div class="spinner-border text-blue-500" role="status"></div>
                        <p class="text-sm text-gray-500 mt-1">Finding best drivers...</p>
                    </div>
                    
                    <div id="drivers-list" class="space-y-3 max-h-96 overflow-y-auto">
                        @if(isset($availableDrivers) && $availableDrivers->count() > 0)
                            @foreach($availableDrivers as $driver)
                            <div class="driver-card border rounded-lg p-3 hover:shadow-md transition cursor-pointer"
                                 data-driver-id="{{ $driver['id'] }}"
                                 data-driver-price="{{ $driver['price'] ?? 0 }}"
                                 onclick="selectDriver(this, {{ $driver['id'] }}, '{{ $driver['name'] }}', {{ $driver['price'] ?? 0 }})">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold">{{ $driver['name'] }}</p>
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-phone-alt mr-1"></i>{{ $driver['phone'] }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-truck mr-1"></i>{{ $driver['vehicle_type'] }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-orange-600 font-bold">
                                            रू {{ number_format($driver['price'] ?? 0, 2) }}
                                        </p>
                                        <div class="flex items-center mt-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-xs {{ $i <= ($driver['rating'] ?? 4) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 pt-2 border-t">
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-600 rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i>Available Today
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-truck text-4xl mb-3"></i>
                                <p>No drivers available at the moment.</p>
                                <p class="text-sm">Try again shortly.</p>
                            </div>
                        @endif
                    </div>
                    
                    <input type="hidden" id="selected_driver_id" name="driver_id" value="">
                </div>

                <!-- AI Route & Fleet Advisor Card -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-slate-200/80">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-sm font-bold">
                                <i class="fas fa-wand-magic-sparkles"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 mb-0">AI Route Advisor</h3>
                                <span class="text-[10px] text-slate-400 font-semibold">Gemini 3.5 Logistics</span>
                            </div>
                        </div>
                        <button type="button" id="btnAiDispatchAdvisor" onclick="runAiDispatchAdvisor()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-bold transition border border-orange-200">
                            <i class="fas fa-bolt"></i> Consult AI
                        </button>
                    </div>

                    <div id="aiDispatchLoading" class="hidden py-4 text-center text-xs text-slate-500">
                        <div class="spinner-border spinner-border-sm text-orange-500 mb-2" role="status"></div>
                        <p class="font-medium">Analyzing terrain, traffic corridors & pricing...</p>
                    </div>

                    <div id="aiDispatchOutput" class="hidden space-y-3 pt-2 text-xs">
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">Vehicle</span>
                                <span id="aiSuggestedVehicle" class="font-bold text-slate-800">Standard</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">Est. Transit</span>
                                <span id="aiEstimatedTransit" class="font-bold text-slate-800">2-3 hrs</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">Est. Distance</span>
                                <span id="aiSuggestedDistance" class="font-bold text-slate-800">--</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">Suggested Rate</span>
                                <span id="aiSuggestedPrice" class="font-bold text-emerald-600">रू --</span>
                            </div>
                        </div>

                        <div class="bg-amber-50/70 border border-amber-200/70 rounded-xl p-2.5 text-slate-700 space-y-1.5">
                            <div>
                                <span class="font-bold text-amber-900 block mb-0.5"><i class="fas fa-route text-amber-500 mr-1"></i> Advisory:</span>
                                <span id="aiRouteAdvisory" class="leading-relaxed">Standard transit routes.</span>
                            </div>
                            <div class="pt-1 border-t border-amber-200/50">
                                <span class="font-bold text-amber-900 block mb-0.5"><i class="fas fa-shield-alt text-amber-500 mr-1"></i> Precautions:</span>
                                <span id="aiHandlingPrecautions" class="leading-relaxed">Standard cargo handling.</span>
                            </div>
                        </div>

                        <button type="button" onclick="applyAiDispatchAdvice()" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                            <i class="fas fa-check"></i> Apply Recommendations
                        </button>
                    </div>
                </div>

                <!-- Price Summary -->
                <div class="rounded-2xl p-6 text-white shadow-sm" style="background: linear-gradient(135deg, #D96B43 0%, #B85832 100%); border: 1px solid rgba(255,255,255,0.15);">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-serif font-bold text-white" style="font-family: 'Newsreader', Georgia, serif;">Price & Rate Summary</h3>
                        <button type="button" id="calculateBtn" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-xl font-semibold transition flex items-center gap-1.5 shadow-2xs">
                            <i class="fas fa-calculator text-[11px]"></i>
                            <span>AI Calculate</span>
                        </button>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-white/90">
                            <span>Total Distance:</span>
                            <span id="total_distance_display" class="font-mono font-semibold">0 km</span>
                        </div>
                        <div class="flex justify-between text-white/90">
                            <span>Base Price:</span>
                            <span id="base_price_display" class="font-mono font-semibold">रू 0</span>
                        </div>
                        <div class="flex justify-between text-white/90">
                            <span>Admin Margin:</span>
                            <span id="margin_display" class="font-mono font-semibold">रू 0</span>
                        </div>
                        <div class="border-t border-white/20 my-2"></div>
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total Amount:</span>
                            <span id="total_price_display" class="font-serif">रू 0</span>
                        </div>
                    </div>
                    <div id="ai-insight-tooltip" class="text-xs text-white/80 mt-2 hidden"></div>
                </div>

                <!-- Submit Button -->
                <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl shadow-2xs p-6 space-y-3">
                    <div class="p-3 bg-amber-50/80 border border-amber-200/80 rounded-xl text-xs text-amber-950 space-y-1.5">
                        <div class="flex items-start gap-2">
                            <input type="checkbox" id="dispatch_liability_consent" name="terms_consent" required class="mt-0.5 accent-orange-600 rounded">
                            <label for="dispatch_liability_consent" class="cursor-pointer text-[11px] leading-relaxed">
                                <strong>Statutory Liability Limit:</strong> I acknowledge carriage liability is capped at NPR 250/kg under Muluki Civil Code 2074 & Transport Act 2049. No hazardous materials, unmanifested currency, or contraband permitted.
                            </label>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" class="w-full bg-[#D96B43] hover:bg-[#C35832] text-white font-bold py-3.5 px-4 rounded-xl shadow-xs transition flex items-center justify-center gap-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-paper-plane text-xs"></i>
                        <span>Confirm & Dispatch Order</span>
                    </button>
                    <p class="text-xs text-[#645D56] text-center">
                        <i class="fas fa-shield-halved text-[#D96B43] mr-1"></i>
                        Dispatch stays editable and tracked via live GPS.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-content">
        <i class="fas fa-spinner fa-spin text-orange-500 text-4xl mb-3"></i>
        <p class="text-gray-700">Creating dispatch...</p>
    </div>
</div>

<script>
let stopCount = 1;
let totalDistance = 0;
let selectedDriversPrice = 0;
let mapInstance = null;
let mapMarkers = [];

// Initialize first stop and map on page load
function setupDispatchCreatePage() {
    addStop();
    setupAutoComplete();
    initMap();
    prefillDispatchFromAssistant();
    document.getElementById('calculateBtn')?.addEventListener('click', calculateDistance);
}

if (document.readyState !== 'loading') {
    setupDispatchCreatePage();
} else {
    document.addEventListener('DOMContentLoaded', setupDispatchCreatePage);
}
document.addEventListener('kwdc:page-loaded', function() {
    initMap();
    prefillDispatchFromAssistant();
});

function initMap() {
    const mapEl = document.getElementById('map');
    if (!mapEl) return;
    if (typeof L === 'undefined') {
        setTimeout(initMap, 100);
        return;
    }

    if (mapEl._leaflet_id) {
        mapEl._leaflet_id = null;
        mapEl.innerHTML = '';
    }

    mapInstance = L.map(mapEl, {
        zoomControl: true,
        scrollWheelZoom: false
    }).setView([27.7172, 85.3240], 13);
    mapEl._leaflet_map = mapInstance;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapInstance);

    const resize = () => { if (mapInstance) mapInstance.invalidateSize(); };
    setTimeout(resize, 80);
    setTimeout(resize, 250);
    setTimeout(resize, 600);
    window.addEventListener('resize', resize);
}

// ------------------ LOCATION LOOKUP ------------------
async function geocodeAddress(address) {
    if (!address || address.trim().length < 3) return null;
    try {
        return await KwdcMaps.search(address);
    } catch (e) {
        console.warn('Geocoding notice:', e);
    }
    return null;
}

let mapRevision = 0;
async function renderMapMarkers() {
    const revision = ++mapRevision;
    if (!mapInstance) return;
    document.querySelectorAll('#pickup_latitude, #pickup_longitude, #stops-container .stop-latitude, #stops-container .stop-longitude').forEach(input => { input.value = ''; });

    // Clear existing markers
    mapMarkers.forEach(m => mapInstance.removeLayer(m.marker));
    mapMarkers = [];

    const addresses = [];
    
    // 1. Collect Pickup Address
    const pickup = document.getElementById('pickup_address').value;
    if (pickup.trim() !== '') {
        addresses.push({
            address: pickup,
            title: 'Pickup',
            color: 'green',
            latInput: document.getElementById('pickup_latitude'),
            lngInput: document.getElementById('pickup_longitude')
        });
    }

    // 2. Collect Delivery Stops
    document.querySelectorAll('#stops-container .stop-address').forEach((input, index) => {
        if (input.value.trim() !== '') {
            addresses.push({ 
                address: input.value, 
                title: `Delivery #${index + 1}`, 
                color: 'red',
                latInput: input.closest('.stop-card')?.querySelector('.stop-latitude'),
                lngInput: input.closest('.stop-card')?.querySelector('.stop-longitude')
            });
        }
    });

    if (addresses.length === 0) return;

    for (const item of addresses) {
        if (item.address.trim().length < 4) continue;
        if (item.latInput) item.latInput.value = '';
        if (item.lngInput) item.lngInput.value = '';
        let coords = await geocodeAddress(item.address);
        if (revision !== mapRevision) return;
        
        if (!coords) {
            continue;
        }

        if (item.latInput) item.latInput.value = coords.lat.toFixed(8);
        if (item.lngInput) item.lngInput.value = coords.lng.toFixed(8);

        const marker = L.marker([coords.lat, coords.lng], { 
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background-color:${item.color}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.5);"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            })
        }).addTo(mapInstance);
        const popup = document.createElement('div');
        popup.textContent = item.title + ': ' + (coords.label || item.address);
        marker.bindPopup(popup);
        
        mapMarkers.push({ title: item.title, marker });

    }

    // Fit bounds to show all markers
    if (mapMarkers.length > 0) {
        const group = L.featureGroup(mapMarkers.map(m => m.marker));
        mapInstance.fitBounds(group.getBounds().pad(0.2), {maxZoom: 16});
    }
}

function setupAutoComplete() {
    // Setup Google Places Autocomplete if available
    const pickupInput = document.getElementById('pickup_address');
    if (pickupInput) {
        pickupInput.addEventListener('input', debounceLocationUpdate(renderMapMarkers));
        pickupInput.addEventListener('blur', renderMapMarkers);
    }
    if (pickupInput && typeof google !== 'undefined' && google.maps) {
        const autocomplete = new google.maps.places.Autocomplete(pickupInput);
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (place.geometry) {
                document.getElementById('pickup_latitude').value = place.geometry.location.lat();
                document.getElementById('pickup_longitude').value = place.geometry.location.lng();
                calculateDistance();
            }
        });
    }
}

function debounceLocationUpdate(callback, wait = 650) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(callback, wait);
    };
}

function addStop() {
    const container = document.getElementById('stops-container');
    const template = document.getElementById('stop-template');
    const newStop = document.createElement('div');
    
    // Replace __INDEX__ with current stop count
    const html = template.innerHTML.replace(/__INDEX__/g, stopCount);
    newStop.innerHTML = html;
    
    container.appendChild(newStop);
    
    // Add address input listener for distance calculation and map update
    const addressInput = newStop.querySelector('.stop-address');
    if (addressInput) {
        addressInput.addEventListener('change', function() {
            calculateDistance(); // Updates price
            renderMapMarkers();  // Updates map
        });
        addressInput.addEventListener('input', debounceLocationUpdate(renderMapMarkers));
        addressInput.addEventListener('blur', renderMapMarkers);
        if (typeof google !== 'undefined' && google.maps) {
            const autocomplete = new google.maps.places.Autocomplete(addressInput);
            autocomplete.addListener('place_changed', function() {
                calculateDistance();
                renderMapMarkers();
            });
        }
    }
    
    stopCount++;
}

function removeStop(button) {
    const stopCard = button.closest('.stop-card');
    if (document.querySelectorAll('.stop-card').length > 1) {
        stopCard.remove();
        calculateDistance();
        renderMapMarkers();
    } else {
        alert('You need at least one delivery stop');
    }
}

function selectDriver(element, driverId, driverName, price) {
    document.querySelectorAll('.driver-card').forEach(card => {
        card.classList.remove('border-orange-500', 'bg-orange-50');
        card.classList.add('border-gray-200');
    });
    
    element.classList.remove('border-gray-200');
    element.classList.add('border-orange-500', 'bg-orange-50');
    
    document.getElementById('selected_driver_id').value = driverId;
    selectedDriversPrice = price;
    
    updatePriceSummary();
    showToast('Driver selected: ' + driverName, 'success');
}

function selectWarehouse(element) {
    const address = element.dataset.address;
    const name = element.dataset.name;
    
    document.getElementById('pickup_address').value = address;
    document.getElementById('pickup_contact_person').value = name;
    
    document.querySelectorAll('.warehouse-card').forEach(card => {
        card.classList.remove('border-orange-500', 'bg-orange-50');
        card.classList.add('border-gray-200');
    });
    element.classList.remove('border-gray-200');
    element.classList.add('border-orange-500', 'bg-orange-50');
    
    calculateDistance();
    renderMapMarkers(); // Updates map with the newly selected pickup address
    showToast('Warehouse selected: ' + name, 'success');
}

function addStockToDispatch(productName, stockId) {
    showToast('Added: ' + productName + ' to dispatch', 'info');
}

function prefillDispatchFromAssistant() {
    const params = new URLSearchParams(window.location.search);
    if (!params.toString()) return;

    const setValue = (id, value) => {
        const input = document.getElementById(id);
        if (input && value) input.value = value;
    };

    setValue('pickup_address', params.get('pickup_address'));
    setValue('pickup_contact_person', params.get('pickup_contact_person'));
    setValue('pickup_contact_phone', params.get('pickup_contact_phone'));
    setValue('total_distance', params.get('total_distance'));
    setValue('total_price', params.get('total_price'));

    const vehicleType = params.get('vehicle_type');
    const vehicleSelect = document.getElementById('vehicle_type');
    if (vehicleType && vehicleSelect) {
        [...vehicleSelect.options].some(option => {
            if (option.value.toLowerCase() === vehicleType.toLowerCase()) {
                vehicleSelect.value = option.value;
                return true;
            }
            return false;
        });
    }

    const firstStop = document.querySelector('.stop-card');
    if (firstStop) {
        const deliveryAddress = params.get('delivery_address');
        const recipientName = params.get('recipient_name') || params.get('pickup_contact_person') || 'Customer';
        const recipientPhone = params.get('recipient_phone') || params.get('pickup_contact_phone') || 'N/A';
        const notes = params.get('items_description');

        if (deliveryAddress) firstStop.querySelector('.stop-address').value = deliveryAddress;
        const nameInput = firstStop.querySelector('input[name*="recipient_name"]');
        const phoneInput = firstStop.querySelector('input[name*="recipient_phone"]');
        const notesInput = firstStop.querySelector('textarea[name*="notes"]');
        if (nameInput && recipientName) nameInput.value = recipientName;
        if (phoneInput && recipientPhone) phoneInput.value = recipientPhone;
        if (notesInput && notes) notesInput.value = notes;
    }

    const totalDistance = params.get('total_distance');
    const totalPrice = params.get('total_price');
    if (totalDistance) {
        document.getElementById('total_distance_display').innerText = totalDistance + ' km';
    }
    if (totalPrice) {
        document.getElementById('base_price_display').innerText = 'रू ' + totalPrice;
        document.getElementById('total_price_display').innerText = 'रू ' + totalPrice;
    }

    renderMapMarkers();
    showToast('Assistant filled the dispatch form. Please review before saving.', 'info');
}

function clearPriceSummary(message = 'Unavailable') {
    totalDistance = 0;
    document.getElementById('total_distance_display').innerText = '0 km';
    document.getElementById('base_price_display').innerText = message;
    document.getElementById('margin_display').innerText = '-';
    document.getElementById('total_price_display').innerText = message;
    document.getElementById('total_price').value = '';
    document.getElementById('total_distance').value = '';
}

// ------------------ PRICE CALCULATION ------------------
async function calculateDistance() {
    const pickup = document.getElementById('pickup_address').value;
    const stops = [];
    document.querySelectorAll('.stop-address').forEach(input => {
        if (input.value) stops.push(input.value);
    });
    if (!pickup || stops.length === 0) return;

    const priceDisplay = document.getElementById('total_price_display');
    priceDisplay.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculating...';

    try {
        const vehicleType = document.getElementById('vehicle_type') ? document.getElementById('vehicle_type').value : 'Standard';
        const roadDistance = await KwdcMaps.roadDistance([pickup, ...stops]);
        
        const response = await fetch('{{ route("dispatch.calculate-price") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                pickup_stops: [{ address: pickup }],
                delivery_stops: stops.map(addr => ({ address: addr })),
                total_distance: roadDistance,
                vehicle_type: vehicleType
            })
        });
        const data = await response.json();

        if (data.success) {
            totalDistance = data.total_distance;
            document.getElementById('total_distance_display').innerText = data.total_distance + ' km';
            document.getElementById('base_price_display').innerText = 'रू ' + data.base_price;
            document.getElementById('margin_display').innerText = 'Admin Margin (' + data.margin_applied + ' - AI Analyzed)';
            document.getElementById('total_price_display').innerText = 'रू ' + data.final_price;

            // Update hidden fields
            document.getElementById('total_price').value = parseFloat(String(data.final_price).replace(/,/g, ''));
            document.getElementById('total_distance').value = data.total_distance;

            const tooltip = document.getElementById('ai-insight-tooltip');
            tooltip.classList.remove('hidden');
            tooltip.innerText = '🤖 ' + data.explanation;
        } else {
            clearPriceSummary();
            showToast(data.errors || 'Price calculation failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        clearPriceSummary();
        showToast('Route distance is unavailable. Check the addresses and try again.', 'error');
    } finally {
        renderMapMarkers(); 
    }
}

function updatePriceSummary(data = null) {
    if (data) {
        document.getElementById('total_distance_display').innerText = data.total_distance + ' km';
        document.getElementById('base_price_display').innerText = 'रू ' + data.base_price;
        document.getElementById('margin_display').innerText = 'रू ' + data.margin_amount;
        document.getElementById('total_price_display').innerText = 'रू ' + data.final_price;
        document.getElementById('total_price').value = parseFloat(String(data.final_price).replace(/,/g, ''));
        document.getElementById('total_distance').value = data.total_distance;
    } else if (selectedDriversPrice) {
        const totalPrice = selectedDriversPrice;
        document.getElementById('total_price_display').innerText = 'रू ' + totalPrice.toFixed(2);
        document.getElementById('total_price').value = totalPrice;
    }
}

// ------------------ DRIVER & VEHICLE RECOMMENDATION ------------------
document.getElementById('findDriversBtn').addEventListener('click', async function() {
    const pickup = document.getElementById('pickup_address').value;
    const stops = [];
    document.querySelectorAll('.stop-address').forEach(input => {
        if (input.value) stops.push(input.value);
    });
    if (!pickup || stops.length === 0) {
        alert('Please enter pickup and delivery addresses first.');
        return;
    }

    const loadingDiv = document.getElementById('driver-loading');
    loadingDiv.classList.remove('hidden');

    let roadDistance;
    try {
        roadDistance = await KwdcMaps.roadDistance([pickup, ...stops]);
    } catch (error) {
        loadingDiv.classList.add('hidden');
        showToast(error.message, 'error');
        return;
    }
    const vehicleType = document.getElementById('vehicle_type') ? document.getElementById('vehicle_type').value : 'Standard';

    fetch('/dispatch/recommend-drivers', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            pickup_stops: [{ address: pickup }],
            delivery_stops: stops.map(addr => ({ address: addr })),
            total_distance: roadDistance,
            vehicle_type: vehicleType
        })
    })
    .then(response => response.json())
    .then(data => {
        loadingDiv.classList.add('hidden');
        if (data.success) {
            const vehicleSelect = document.getElementById('vehicle_type');
            if (vehicleSelect) {
                for (let i = 0; i < vehicleSelect.options.length; i++) {
                    if (vehicleSelect.options[i].value === data.recommended_vehicle) {
                        vehicleSelect.value = data.recommended_vehicle;
                        vehicleSelect.style.borderColor = '#f59e0b';
                        break;
                    }
                }
            }

            const driverCards = document.querySelectorAll('.driver-card');
            driverCards.forEach(card => {
                const oldBadge = card.querySelector('.ai-badge');
                if (oldBadge) oldBadge.remove();

                const driverId = card.dataset.driverId;
                const aiDriver = data.drivers.find(d => d.id == driverId);
                if (aiDriver) {
                    const rank = data.drivers.indexOf(aiDriver);
                    let badgeHtml = '';
                    if (rank === 0) {
                        badgeHtml = `<span class="ai-badge inline-block ml-2 px-2 py-0.5 bg-yellow-400 text-xs font-bold rounded-full">🤖 Top Pick</span>`;
                    } else {
                        badgeHtml = `<span class="ai-badge inline-block ml-2 px-2 py-0.5 bg-blue-400 text-white text-xs font-bold rounded-full">🤖 AI Recommended</span>`;
                    }
                    const nameEl = card.querySelector('.font-semibold');
                    if (nameEl) nameEl.insertAdjacentHTML('beforeend', badgeHtml);
                }
            });

            showToast('Found ' + data.drivers.length + ' recommended drivers.', 'info');
        }
    })
    .catch(error => {
        loadingDiv.classList.add('hidden');
        console.error('Driver AI error:', error);
        alert('Failed to get driver recommendations.');
    });
});

// Form submission
document.getElementById('dispatchForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const pickupAddress = document.getElementById('pickup_address').value;
    const driverId = document.getElementById('selected_driver_id').value;
    const stops = document.querySelectorAll('.stop-card');
    
    if (!pickupAddress) {
        showToast('Please enter pickup address', 'error');
        return;
    }
    
    if (stops.length === 0) {
        showToast('Please add at least one delivery stop', 'error');
        return;
    }
    
    let valid = true;
    stops.forEach((stop, index) => {
        const address = stop.querySelector('.stop-address').value;
        const name = stop.querySelector('input[name*="recipient_name"]').value;
        const phone = stop.querySelector('input[name*="recipient_phone"]').value;
        
        if (!address) {
            showToast('Please enter address for stop ' + (index + 1), 'error');
            valid = false;
        }
        if (!name) {
            showToast('Please enter recipient name for stop ' + (index + 1), 'error');
            valid = false;
        }
        if (!phone) {
            showToast('Please enter recipient phone for stop ' + (index + 1), 'error');
            valid = false;
        }
    });
    
    if (!valid) return;
    
    document.getElementById('loadingOverlay').style.display = 'flex';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => {
                window.location.href = result.redirect_url || '{{ route("dispatch.index") }}';
            }, 1500);
        } else {
            document.getElementById('loadingOverlay').style.display = 'none';
            showToast(result.message || 'Failed to create dispatch', 'error');
        }
    } catch (error) {
        document.getElementById('loadingOverlay').style.display = 'none';
        showToast('Network error. Please try again.', 'error');
        console.error('Error:', error);
    }
});

let currentAiDispatchAdvice = null;

async function runAiDispatchAdvisor() {
    const pickup = document.getElementById('pickup_address')?.value?.trim() || '';
    const firstStop = document.querySelector('.stop-address')?.value?.trim() || '';
    const cargoType = document.getElementById('cargo_type')?.value || '';
    const weight = document.getElementById('weight_kg')?.value || '';
    const notes = document.getElementById('special_instructions')?.value || '';

    const loading = document.getElementById('aiDispatchLoading');
    const output = document.getElementById('aiDispatchOutput');
    const btn = document.getElementById('btnAiDispatchAdvisor');

    if (!pickup && !firstStop) {
        showToast('Please enter pickup or delivery address first.', 'info');
        document.getElementById('pickup_address')?.focus();
        return;
    }

    loading.classList.remove('hidden');
    output.classList.add('hidden');
    btn.disabled = true;

    try {
        const response = await fetch('/ai/dispatch-advisor', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                pickup_address: pickup,
                delivery_address: firstStop,
                cargo_type: cargoType,
                weight_kg: weight ? parseFloat(weight) : null,
                urgency: 'medium',
                special_instructions: notes
            })
        });

        const res = await response.json();
        if (res.success && res.advice) {
            currentAiDispatchAdvice = res.advice;
            document.getElementById('aiSuggestedVehicle').textContent = res.advice.suggested_vehicle || 'Standard';
            document.getElementById('aiEstimatedTransit').textContent = res.advice.estimated_hours || 'N/A';
            document.getElementById('aiSuggestedPrice').textContent = 'रू ' + Number(res.advice.suggested_price_npr || 0).toLocaleString();
            document.getElementById('aiSuggestedDistance').textContent = (res.advice.estimated_distance_km || 0) + ' km';
            const precEl = document.getElementById('aiHandlingPrecautions');
            if (precEl) precEl.textContent = res.advice.handling_precautions || 'Standard cargo handling protocols apply.';

            output.classList.remove('hidden');
            showToast('AI route analysis complete', 'success');
        } else {
            showToast('AI advisor unavailable right now.', 'error');
        }
    } catch (e) {
        console.error(e);
        showToast('Failed to consult AI advisor.', 'error');
    } finally {
        loading.classList.add('hidden');
        btn.disabled = false;
    }
}

function applyAiDispatchAdvice() {
    if (!currentAiDispatchAdvice) return;

    // Apply vehicle type
    const vType = document.getElementById('vehicle_type');
    if (vType) {
        const sv = (currentAiDispatchAdvice.suggested_vehicle || '').toLowerCase();
        if (sv.includes('heavy') || sv.includes('container') || sv.includes('tata 407')) {
            vType.value = 'Heavy';
        } else if (sv.includes('refrigerated') || sv.includes('cold')) {
            vType.value = 'Refrigerated';
        } else if (sv.includes('bike') || sv.includes('two') || sv.includes('courier')) {
            vType.value = 'Two-Wheeler';
        } else {
            vType.value = 'Standard';
        }
    }

    // Apply distance if not yet calculated
    if (currentAiDispatchAdvice.estimated_distance_km) {
        const distEl = document.getElementById('total_distance');
        const distDisplay = document.getElementById('total_distance_display');
        if (distEl && (!distEl.value || distEl.value == '0')) {
            distEl.value = currentAiDispatchAdvice.estimated_distance_km;
            if (distDisplay) distDisplay.textContent = currentAiDispatchAdvice.estimated_distance_km + ' km';
        }
    }

    // Apply price if not yet set
    if (currentAiDispatchAdvice.suggested_price_npr) {
        const priceEl = document.getElementById('total_price');
        const priceDisplay = document.getElementById('total_price_display');
        if (priceEl && (!priceEl.value || priceEl.value == '0')) {
            priceEl.value = currentAiDispatchAdvice.suggested_price_npr;
            if (priceDisplay) priceDisplay.textContent = 'रू ' + Number(currentAiDispatchAdvice.suggested_price_npr).toLocaleString();
        }
    }

    showToast('AI recommendations applied to form!', 'success');
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-up ' + 
        (type === 'success' ? 'bg-green-500 text-white' : 
         type === 'error' ? 'bg-red-500 text-white' : 
         'bg-blue-500 text-white');
    const icon = document.createElement('i');
    icon.className = 'fas fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle') + ' mr-2';
    toast.append(icon, document.createTextNode(message));
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-slide-up {
        animation: slideUp 0.3s ease-out;
    }
`;
document.head.appendChild(style);
</script>

@endsection
