@extends('layouts.app')

@section('title', 'Request Equipment')
@section('header', 'Request Equipment')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <form method="POST" action="{{ route('client.equipment.request.store') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="block text-gray-700 font-semibold mb-2">Select Equipment Type *</label>
                <select name="equipment_type" id="equipment_type" required class="w-full px-4 py-2 border rounded-lg">
                    <option value="">Select Equipment Type</option>
                    <option value="jcb">JCB / Excavator</option>
                    <option value="crane">Crane</option>
                    <option value="dozer">Dozer / Bulldozer</option>
                    <option value="loader">Loader</option>
                    <option value="backhoe">Backhoe Loader</option>
                    <option value="forklift">Forklift</option>
                    <option value="compactor">Compactor / Roller</option>
                    <option value="concrete_mixer">Concrete Mixer</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="col-span-2">
                <label class="block text-gray-700 font-semibold mb-2">Specific Equipment (Optional)</label>
                <input type="text" name="equipment_name" class="w-full px-4 py-2 border rounded-lg" placeholder="e.g., JCB 3DX">
            </div>
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Required From Date *</label>
                <input type="date" name="start_date" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Required To Date *</label>
                <input type="date" name="end_date" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Duration (Days) *</label>
                <input type="number" name="duration_days" id="duration_days" required class="w-full px-4 py-2 border rounded-lg" placeholder="Number of days">
            </div>
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Your Proposed Budget (NPR)</label>
                <input type="number" step="0.01" name="proposed_budget" id="proposed_budget" class="w-full px-4 py-2 border rounded-lg" placeholder="Your proposed price">
            </div>
            
            <div class="col-span-2">
                <label class="block text-gray-700 font-semibold mb-2">Work Location *</label>
                <input type="text" name="location" required class="w-full px-4 py-2 border rounded-lg" placeholder="Where will the equipment be used?">
            </div>
            
            <div class="col-span-2">
                <label class="block text-gray-700 font-semibold mb-2">Description / Requirements *</label>
                <textarea name="description" rows="4" required class="w-full px-4 py-2 border rounded-lg" placeholder="Describe your project requirements..."></textarea>
            </div>
        </div>
        
        <div class="flex justify-end space-x-4 mt-6">
            <a href="{{ route('client.equipment.requests') }}" class="px-6 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                Submit Request
            </button>
        </div>
    </form>
</div>

<script>
    // Calculate duration days
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const durationDays = document.getElementById('duration_days');
    
    function calculateDuration() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            durationDays.value = diffDays;
        }
    }
    
    startDate.addEventListener('change', calculateDuration);
    endDate.addEventListener('change', calculateDuration);
</script>
@endsection