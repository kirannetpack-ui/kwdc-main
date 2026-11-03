@extends('layouts.app')

@section('title', 'Margin Tiers')
@section('header', 'Margin Tiers')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800">Service Margin Configuration</h3>
        <button onclick="openCreateModal()" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition">
            <i class="fas fa-plus mr-2"></i> Add New Tier
        </button>
    </div>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Margin Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Distance Range</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($marginTiers ?? [] as $tier)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $tier->name }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            @if($tier->service_type == 'dispatch') bg-blue-100 text-blue-800
                            @elseif($tier->service_type == 'pickup') bg-green-100 text-green-800
                            @elseif($tier->service_type == 'warehouse') bg-purple-100 text-purple-800
                            @elseif($tier->service_type == 'equipment') bg-orange-100 text-orange-800
                            @endif">
                            {{ ucfirst($tier->service_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if($tier->margin_type == 'percentage') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($tier->margin_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-bold text-orange-600">
                        @if($tier->margin_type == 'flat')
                            रू {{ number_format($tier->margin_value, 2) }}
                        @else
                            {{ $tier->margin_value }}%
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600 text-sm">
                        @if($tier->service_type == 'dispatch' || $tier->service_type == 'pickup')
                            {{ $tier->min_distance ?? 0 }} km
                            @if($tier->max_distance)
                                - {{ $tier->max_distance }} km
                            @else
                                +
                            @endif
                        @else
                            <span class="text-gray-400">Not applicable</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $tier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $tier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="openEditModal({{ $tier->id }}, '{{ $tier->name }}', '{{ $tier->service_type }}', '{{ $tier->margin_type }}', {{ $tier->margin_value }}, {{ $tier->min_distance ?? 0 }}, {{ $tier->max_distance ?? 'null' }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.margin-tiers.destroy', $tier->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Delete this margin tier?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-percent text-4xl mb-3 text-gray-300"></i>
                        <p>No margin tiers configured yet.</p>
                        <p class="text-sm">Click "Add New Tier" to define margins for Dispatches, Pickups, Warehouses, or Equipment.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ==================== CREATE / EDIT MODAL ==================== -->
<div id="marginModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-lg font-bold text-gray-800" id="modalTitle">Add New Margin Tier</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>

        <form id="marginForm" method="POST" action="">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tier Name</label>
                    <input type="text" name="name" id="tier_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="e.g., Short Dispatch Margin">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service Type</label>
                        <select name="service_type" id="service_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                            <option value="dispatch">Dispatch</option>
                            <option value="pickup">Pickup Request</option>
                            <option value="warehouse">Warehouse Rental</option>
                            <option value="equipment">Equipment Rental</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Margin Type</label>
                        <select name="margin_type" id="margin_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                            <option value="percentage">Percentage (%)</option>
                            <option value="flat">Flat Amount (रू)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Margin Value</label>
                    <input type="number" step="0.01" name="margin_value" id="margin_value" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="e.g., 12.5">
                    <p class="text-xs text-gray-500 mt-1">If Percentage, use numbers like 12.5. If Flat Amount, use numbers like 200.</p>
                </div>

                <div id="distance-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4 mt-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Distance (km)</label>
                        <input type="number" step="0.1" name="min_distance" id="min_distance" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Distance (km)</label>
                        <input type="number" step="0.1" name="max_distance" id="max_distance" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Leave empty for unlimited">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                    <i class="fas fa-save mr-2"></i> Save Tier
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Add New Margin Tier';
        document.getElementById('marginForm').action = "{{ route('admin.margin-tiers.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('tier_name').value = '';
        document.getElementById('margin_type').value = 'percentage';
        document.getElementById('service_type').value = 'dispatch';
        document.getElementById('margin_value').value = '';
        document.getElementById('min_distance').value = '';
        document.getElementById('max_distance').value = '';
        document.getElementById('marginModal').classList.remove('hidden');
    }

    function openEditModal(id, name, serviceType, marginType, marginValue, minDistance, maxDistance) {
        document.getElementById('modalTitle').innerText = 'Edit Margin Tier';
        document.getElementById('marginForm').action = "{{ route('admin.margin-tiers.update', '') }}" + '/' + id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('tier_name').value = name;
        document.getElementById('service_type').value = serviceType;
        document.getElementById('margin_type').value = marginType;
        document.getElementById('margin_value').value = marginValue;
        document.getElementById('min_distance').value = minDistance;
        document.getElementById('max_distance').value = maxDistance === null ? '' : maxDistance;
        document.getElementById('marginModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('marginModal').classList.add('hidden');
    }

    // Close modal if clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('marginModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
@endsection