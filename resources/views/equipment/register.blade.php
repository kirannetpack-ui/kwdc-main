@extends('layouts.app')

@section('title', 'Register Machinery - KTM-WDC Equipment')
@section('header', 'Register Machinery')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Editorial Claude Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E8E2D8]">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FAF4ED] text-[#D96B43] border border-[#F3DFC8] mb-2">
                <i class="fas fa-truck-ramp-box text-[11px]"></i>
                Heavy Equipment & Fleet Asset
            </div>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D] tracking-tight">Register New Machinery</h1>
            <p class="text-sm text-[#5C554E] mt-1 max-w-xl">
                Onboard your heavy logistics machinery to KTM-WDC's regional rental network. Set daily rates, specifications, and deployment availability.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('equipment.list') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-[#5C554E] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition shadow-2xs">
                <i class="fas fa-arrow-left text-[11px]"></i>
                Back to Fleet
            </a>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="p-4 rounded-xl bg-[#FCF2F0] border border-[#F5C2B8] text-[#A6321D] text-xs">
        <div class="flex items-center gap-2 font-semibold mb-1">
            <i class="fas fa-triangle-exclamation"></i>
            <span>Please correct the errors below before submitting:</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 ml-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('equipment.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <!-- 1. Basic Information -->
        <div class="bg-[#FFFDF9] rounded-2xl border border-[#E8E2D8] p-6 shadow-2xs space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#E8E2D8]">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-[#FAF4ED] text-[#D96B43] border border-[#F3DFC8] flex items-center justify-center text-xs font-bold">1</span>
                    <h3 class="text-base font-serif font-bold text-[#24201D]">Machinery Identification</h3>
                </div>
                <span class="text-xs text-[#8C827A] font-medium">* Required fields</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Equipment / Machine Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                           placeholder="e.g., CAT 320D Hydraulic Excavator"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Machinery Category *</label>
                    <select name="type" required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                        <option value="">Select Equipment Category</option>
                        <option value="jcb" {{ old('type') == 'jcb' ? 'selected' : '' }}>JCB / Heavy Excavator</option>
                        <option value="crane" {{ old('type') == 'crane' ? 'selected' : '' }}>Hydraulic Crane / Mobile Boom</option>
                        <option value="forklift" {{ old('type') == 'forklift' ? 'selected' : '' }}>Forklift / Pallet Stacker</option>
                        <option value="dozer" {{ old('type') == 'dozer' ? 'selected' : '' }}>Crawler Bulldozer</option>
                        <option value="loader" {{ old('type') == 'loader' ? 'selected' : '' }}>Wheel Loader</option>
                        <option value="backhoe" {{ old('type') == 'backhoe' ? 'selected' : '' }}>Backhoe Loader</option>
                        <option value="compactor" {{ old('type') == 'compactor' ? 'selected' : '' }}>Compactor / Road Roller</option>
                        <option value="concrete_mixer" {{ old('type') == 'concrete_mixer' ? 'selected' : '' }}>Transit Concrete Mixer</option>
                        <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Specialized Cargo Logistics Machinery</option>
                    </select>
                    @error('type') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Model / Chassis Number</label>
                    <input type="text" name="model" value="{{ old('model') }}" 
                           placeholder="e.g., Komatsu PC200-8"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                    @error('model') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Manufacturing Year</label>
                    <input type="number" name="year" value="{{ old('year') }}" min="1990" max="{{ date('Y') + 1 }}" 
                           placeholder="e.g., 2022"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                    @error('year') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Machinery Description & Capabilities</label>
                    <textarea name="description" rows="3" 
                              placeholder="Detail attachments included, boom length, operator certification, fuel policy, and recommended applications..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">{{ old('description') }}</textarea>
                    @error('description') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
        
        <!-- 2. Technical Specifications -->
        <div class="bg-[#FFFDF9] rounded-2xl border border-[#E8E2D8] p-6 shadow-2xs space-y-5">
            <div class="flex items-center gap-2.5 pb-3 border-b border-[#E8E2D8]">
                <span class="w-7 h-7 rounded-lg bg-[#FAF4ED] text-[#D96B43] border border-[#F3DFC8] flex items-center justify-center text-xs font-bold">2</span>
                <h3 class="text-base font-serif font-bold text-[#24201D]">Technical Specifications</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Operating Weight (kg)</label>
                    <input type="number" step="0.01" name="weight" value="{{ old('weight') }}" placeholder="e.g., 20500"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Engine Power (HP)</label>
                    <input type="number" step="0.01" name="engine_power" value="{{ old('engine_power') }}" placeholder="e.g., 148"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Bucket / Lift (m³ / t)</label>
                    <input type="number" step="0.01" name="bucket_capacity" value="{{ old('bucket_capacity') }}" placeholder="e.g., 1.2"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Max Reach / Boom (m)</label>
                    <input type="number" step="0.01" name="max_reach" value="{{ old('max_reach') }}" placeholder="e.g., 9.8"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                </div>
            </div>
        </div>
        
        <!-- 3. Rental Pricing Structure -->
        <div class="bg-[#FFFDF9] rounded-2xl border border-[#E8E2D8] p-6 shadow-2xs space-y-5">
            <div class="flex items-center gap-2.5 pb-3 border-b border-[#E8E2D8]">
                <span class="w-7 h-7 rounded-lg bg-[#FAF4ED] text-[#D96B43] border border-[#F3DFC8] flex items-center justify-center text-xs font-bold">3</span>
                <h3 class="text-base font-serif font-bold text-[#24201D]">Rental Tariff & Billing (NPR)</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Daily Rate (NPR)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs font-semibold text-[#8C827A]">Rs.</span>
                        <input type="number" step="0.01" name="daily_rate" value="{{ old('daily_rate') }}" placeholder="15000"
                               class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Weekly Rate (NPR)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs font-semibold text-[#8C827A]">Rs.</span>
                        <input type="number" step="0.01" name="weekly_rate" value="{{ old('weekly_rate') }}" placeholder="90000"
                               class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Monthly Rate (NPR)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs font-semibold text-[#8C827A]">Rs.</span>
                        <input type="number" step="0.01" name="monthly_rate" value="{{ old('monthly_rate') }}" placeholder="320000"
                               class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Security Deposit (NPR)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs font-semibold text-[#8C827A]">Rs.</span>
                        <input type="number" step="0.01" name="security_deposit" value="{{ old('security_deposit') }}" placeholder="50000"
                               class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 4. Location & Fleet Status -->
        <div class="bg-[#FFFDF9] rounded-2xl border border-[#E8E2D8] p-6 shadow-2xs space-y-5">
            <div class="flex items-center gap-2.5 pb-3 border-b border-[#E8E2D8]">
                <span class="w-7 h-7 rounded-lg bg-[#FAF4ED] text-[#D96B43] border border-[#F3DFC8] flex items-center justify-center text-xs font-bold">4</span>
                <h3 class="text-base font-serif font-bold text-[#24201D]">Base Location & Status</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Current Base Location *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-[#D96B43]">
                            <i class="fas fa-location-dot"></i>
                        </span>
                        <input type="text" name="location" value="{{ old('location') }}" required 
                               placeholder="e.g., Kathmandu Ring Road Yard / Balkhu"
                               class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                    </div>
                    @error('location') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-[#24201D] uppercase tracking-wider mb-1.5">Initial Availability</label>
                    <select name="status" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm text-[#24201D] focus:bg-[#FFFDF9] focus:outline-none focus:ring-2 focus:ring-[#D96B43]/30 focus:border-[#D96B43] transition">
                        <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>🟢 Available for Deployment</option>
                        <option value="in_use" {{ old('status') == 'in_use' ? 'selected' : '' }}>🔵 Currently Deployed on Site</option>
                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>🟡 Under Periodic Maintenance</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- 5. Verification Photos & Documents -->
        <div class="bg-[#FFFDF9] rounded-2xl border border-[#E8E2D8] p-6 shadow-2xs space-y-5">
            <div class="flex items-center gap-2.5 pb-3 border-b border-[#E8E2D8]">
                <span class="w-7 h-7 rounded-lg bg-[#FAF4ED] text-[#D96B43] border border-[#F3DFC8] flex items-center justify-center text-xs font-bold">5</span>
                <h3 class="text-base font-serif font-bold text-[#24201D]">Inspection Photos & Documentation</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="p-4 rounded-xl border border-dashed border-[#E8E2D8] bg-[#FAF8F5] hover:bg-[#FAF4ED]/50 transition">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold text-[#24201D]">
                        <i class="fas fa-camera text-[#D96B43]"></i>
                        <span>Front Profile Photo</span>
                    </div>
                    <input type="file" name="front_photo" accept="image/*" class="w-full text-xs text-[#5C554E] file:mr-2.5 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#FAF4ED] file:text-[#D96B43] hover:file:bg-[#F3DFC8] cursor-pointer">
                    <p class="text-[10px] text-[#8C827A] mt-1.5">JPG, PNG, WebP up to 5MB</p>
                </div>

                <div class="p-4 rounded-xl border border-dashed border-[#E8E2D8] bg-[#FAF8F5] hover:bg-[#FAF4ED]/50 transition">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold text-[#24201D]">
                        <i class="fas fa-camera text-[#D96B43]"></i>
                        <span>Side / Boom Photo</span>
                    </div>
                    <input type="file" name="side_photo" accept="image/*" class="w-full text-xs text-[#5C554E] file:mr-2.5 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#FAF4ED] file:text-[#D96B43] hover:file:bg-[#F3DFC8] cursor-pointer">
                    <p class="text-[10px] text-[#8C827A] mt-1.5">Shows chassis & tread state</p>
                </div>

                <div class="p-4 rounded-xl border border-dashed border-[#E8E2D8] bg-[#FAF8F5] hover:bg-[#FAF4ED]/50 transition">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold text-[#24201D]">
                        <i class="fas fa-camera text-[#D96B43]"></i>
                        <span>Working / In-Action Photo</span>
                    </div>
                    <input type="file" name="working_photo" accept="image/*" class="w-full text-xs text-[#5C554E] file:mr-2.5 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#FAF4ED] file:text-[#D96B43] hover:file:bg-[#F3DFC8] cursor-pointer">
                    <p class="text-[10px] text-[#8C827A] mt-1.5">Demonstrating active operation</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-3 border-t border-[#E8E2D8]/60">
                <div class="p-4 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5]">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold text-[#24201D]">
                        <i class="fas fa-file-contract text-emerald-600"></i>
                        <span>Vehicle / Machinery Registration Bluebook</span>
                    </div>
                    <input type="file" name="registration_doc" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-[#5C554E] file:mr-2.5 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#E8F5E9] file:text-emerald-700 hover:file:bg-[#C8E6C9] cursor-pointer">
                    <p class="text-[10px] text-[#8C827A] mt-1.5">PDF or image of DOTM Bluebook / ownership deed</p>
                </div>

                <div class="p-4 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5]">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold text-[#24201D]">
                        <i class="fas fa-shield-halved text-blue-600"></i>
                        <span>Comprehensive Equipment Insurance</span>
                    </div>
                    <input type="file" name="insurance_doc" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-[#5C554E] file:mr-2.5 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#EBF5FB] file:text-blue-700 hover:file:bg-[#D4E6F1] cursor-pointer">
                    <p class="text-[10px] text-[#8C827A] mt-1.5">Active third-party / machinery breakdown policy</p>
                </div>
            </div>
        </div>
        
        <!-- Action Row -->
        <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3 pt-2">
            <a href="{{ route('equipment.list') }}" 
               class="px-5 py-2.5 text-center rounded-xl text-xs font-semibold text-[#5C554E] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition">
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#D96B43] hover:bg-[#C25A34] transition shadow-xs">
                <i class="fas fa-check text-[11px]"></i>
                <span>Complete Registration & Publish Machinery</span>
            </button>
        </div>
    </form>
</div>
@endsection