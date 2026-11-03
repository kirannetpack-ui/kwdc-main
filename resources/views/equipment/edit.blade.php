@extends('layouts.app')

@section('title', 'Edit Equipment - ' . $equipment->name)
@section('header', 'Edit Registered Equipment')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Edit Equipment</h2>
            <p class="text-xs text-slate-500">Update machinery specifications, location, and commercial rental rates.</p>
        </div>
        <a href="{{ route('equipment.list') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition">
            <i class="fas fa-arrow-left mr-1.5"></i> Back to Fleet
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('equipment.update', $equipment->id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Primary Information -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-3">General Specifications</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Equipment Name *</label>
                    <input type="text" name="name" value="{{ old('name', $equipment->name) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Category Type *</label>
                    <select name="type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
                        @foreach([
                            'forklift' => 'Forklift / Pallet Mover',
                            'crane' => 'Hydraulic Crane',
                            'jcb' => 'JCB / Excavator',
                            'loader' => 'Wheel Loader',
                            'dozer' => 'Bulldozer',
                            'backhoe' => 'Backhoe Loader',
                            'compactor' => 'Compactor / Road Roller',
                            'other' => 'Other Machinery'
                        ] as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $equipment->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Base Location *</label>
                    <input type="text" name="location" value="{{ old('location', $equipment->location) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm" placeholder="e.g., Tinkune Logistics Yard, Kathmandu">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Operational Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                        <option value="available" {{ old('status', $equipment->status) == 'available' ? 'selected' : '' }}>Available for Hire</option>
                        <option value="in_use" {{ old('status', $equipment->status) == 'in_use' ? 'selected' : '' }}>Currently On Site</option>
                        <option value="maintenance" {{ old('status', $equipment->status) == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                        <option value="unavailable" {{ old('status', $equipment->status) == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Model / Serial Number</label>
                    <input type="text" name="model" value="{{ old('model', $equipment->model) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Manufacturing Year</label>
                    <input type="number" name="year" value="{{ old('year', $equipment->year) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Daily Rental Tariff (NPR)</label>
                    <input type="number" step="0.01" name="daily_rate" value="{{ old('daily_rate', $equipment->daily_rate) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Security Deposit (NPR)</label>
                    <input type="number" step="0.01" name="security_deposit" value="{{ old('security_deposit', $equipment->security_deposit) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Equipment Description &amp; Technical Capabilities</label>
                    <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">{{ old('description', $equipment->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('equipment.list') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-sm transition">
                <i class="fas fa-check mr-1.5"></i> Update Equipment
            </button>
        </div>
    </form>
</div>
@endsection
