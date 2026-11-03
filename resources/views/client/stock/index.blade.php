@extends('layouts.app')

@section('title', 'My Stock')
@section('header', 'Stock Management')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800">My Stock Items</h3>
        <button onclick="openAddModal()" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
            <i class="fas fa-plus mr-2"></i> Add Stock
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($stocks as $stock)
                <tr>
                    <td class="px-6 py-4">{{ $stock->sku ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $stock->product_name }}</td>
                    <td class="px-6 py-4">{{ $stock->quantity }}</td>
                    <td class="px-6 py-4">{{ $stock->unit ?? 'pcs' }}</td>
                    <td class="px-6 py-4">{{ $stock->warehouse->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="status-badge status-{{ $stock->status }}">
                            {{ ucfirst($stock->status ?? 'Active') }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="openEditModal({{ $stock->id }})" class="text-blue-500 hover:text-blue-700 mr-2">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('stock.destroy', $stock->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Delete this stock?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No stock items found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $stocks->links() }}
    </div>
</div>

<!-- Add Stock Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Add Stock Item</h3>
        <form method="POST" action="{{ route('stock.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">SKU (Stock Keeping Unit)</label>
                <input type="text" name="sku" class="w-full px-4 py-2 border rounded-lg" placeholder="e.g., PROD-001">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Product Name *</label>
                <input type="text" name="product_name" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Quantity *</label>
                <input type="number" name="quantity" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Unit</label>
                <select name="unit" class="w-full px-4 py-2 border rounded-lg">
                    <option value="pcs">Pieces (pcs)</option>
                    <option value="kg">Kilogram (kg)</option>
                    <option value="g">Gram (g)</option>
                    <option value="ltr">Liter (ltr)</option>
                    <option value="box">Box</option>
                    <option value="carton">Carton</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Warehouse</label>
                <select name="warehouse_id" class="w-full px-4 py-2 border rounded-lg">
                    <option value="">Select Warehouse</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Stock Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Edit Stock Item</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">SKU</label>
                <input type="text" name="sku" id="edit_sku" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Product Name *</label>
                <input type="text" name="product_name" id="edit_product_name" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Quantity *</label>
                <input type="number" name="quantity" id="edit_quantity" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Unit</label>
                <select name="unit" id="edit_unit" class="w-full px-4 py-2 border rounded-lg">
                    <option value="pcs">Pieces (pcs)</option>
                    <option value="kg">Kilogram (kg)</option>
                    <option value="g">Gram (g)</option>
                    <option value="ltr">Liter (ltr)</option>
                    <option value="box">Box</option>
                    <option value="carton">Carton</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>

<style>
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-active { background: #d1fae5; color: #059669; }
    .status-low_stock { background: #fed7aa; color: #ea580c; }
    .status-out_of_stock { background: #fee2e2; color: #dc2626; }
</style>

<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
        document.getElementById('addModal').classList.add('flex');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
        document.getElementById('addModal').classList.remove('flex');
    }
    
    function openEditModal(id) {
        fetch(`/stock/${id}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_sku').value = data.sku || '';
                document.getElementById('edit_product_name').value = data.product_name;
                document.getElementById('edit_quantity').value = data.quantity;
                document.getElementById('edit_unit').value = data.unit || 'pcs';
                document.getElementById('editForm').action = `/stock/${id}`;
                
                document.getElementById('editModal').classList.remove('hidden');
                document.getElementById('editModal').classList.add('flex');
            });
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }
</script>
@endsection