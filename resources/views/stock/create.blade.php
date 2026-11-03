@extends('layouts.app')

@section('title', 'Add New Stock')

@section('header', 'Add New Stock Batch')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <form action="{{ route('stock.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Basic Product Information -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Basic Product Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Product Name *</label>
                <input type="text" name="product_name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-orange-500" required>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Unit *</label>
                <select name="unit" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-orange-500" required>
                    <option value="pieces">Pieces</option>
                    <option value="kg">Kilograms (kg)</option>
                    <option value="g">Grams (g)</option>
                    <option value="liters">Liters (L)</option>
                    <option value="ml">Milliliters (ml)</option>
                    <option value="boxes">Boxes</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-orange-500"></textarea>
            </div>
        </div>
        
        <!-- Box/Quantity Information -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Box & Quantity Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Number of Boxes *</label>
                <input type="number" name="number_of_boxes" id="number_of_boxes" class="w-full px-4 py-2 border rounded-lg" required min="1">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Quantity per Box *</label>
                <input type="number" name="quantity_per_box" id="quantity_per_box" class="w-full px-4 py-2 border rounded-lg" required min="1">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Total Quantity</label>
                <input type="text" id="total_quantity_display" class="w-full px-4 py-2 border rounded-lg bg-gray-100" readonly>
            </div>
        </div>
        
        <!-- Invoice Information -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Invoice Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Invoice Number *</label>
                <input type="text" name="invoice_number" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-orange-500" required>
                <p class="text-xs text-gray-500 mt-1">Enter the invoice number from your supplier</p>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Upload Invoice *</label>
                <input type="file" name="invoice_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border rounded-lg" required>
                <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG (Max 5MB)</p>
            </div>
        </div>
        
        <!-- Warehouse Assignment -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Warehouse Assignment</h3>
        <div class="grid grid-cols-1 gap-6 mb-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Assign to Warehouse</label>
                <select name="warehouse_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-orange-500">
                    <option value="">Select Warehouse (Optional)</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }} - {{ $warehouse->location }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <!-- Dates -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Product Dates</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Manufacturing Date</label>
                <input type="date" name="manufacturing_date" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-orange-500">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Expiry Date</label>
                <input type="date" name="expiry_date" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-orange-500">
            </div>
        </div>
        
        <!-- Document Uploads -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Supporting Documents</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">GRN (Goods Receipt Note)</label>
                <input type="file" name="grn_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Quality Certificate</label>
                <input type="file" name="quality_certificate" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Other Documents</label>
                <input type="file" name="other_documents" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border rounded-lg">
            </div>
        </div>
        
        <!-- Pricing (Optional) -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Pricing Information (Optional)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Purchase Price (per unit)</label>
                <input type="number" name="purchase_price" step="0.01" class="w-full px-4 py-2 border rounded-lg" placeholder="0.00">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Selling Price (per unit)</label>
                <input type="number" name="selling_price" step="0.01" class="w-full px-4 py-2 border rounded-lg" placeholder="0.00">
            </div>
        </div>
        
        <!-- QR Code Info -->
        <div class="mt-6 p-4 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-lg border border-orange-200">
            <div class="flex items-center">
                <i class="fas fa-qrcode text-orange-600 text-3xl mr-3"></i>
                <div>
                    <p class="text-sm font-semibold text-orange-800">QR Code will be automatically generated</p>
                    <p class="text-xs text-orange-600 mt-1">The QR code will contain: Batch ID, Invoice Number, Warehouse Details, Client Information, and Product Details</p>
                    <p class="text-xs text-orange-600 mt-1">Batch ID Format: <strong>BID-CLT-2024-0001</strong> (Auto-generated)</p>
                </div>
            </div>
        </div>
        
        <!-- Submit Buttons -->
        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('stock.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                <i class="fas fa-plus mr-2"></i> Add Stock Batch
            </button>
        </div>
    </form>
</div>

<script>
    // Auto-calculate total quantity
    const numBoxes = document.getElementById('number_of_boxes');
    const qtyPerBox = document.getElementById('quantity_per_box');
    const totalDisplay = document.getElementById('total_quantity_display');
    
    function calculateTotal() {
        const boxes = parseInt(numBoxes.value) || 0;
        const perBox = parseInt(qtyPerBox.value) || 0;
        const total = boxes * perBox;
        totalDisplay.value = total;
    }
    
    numBoxes.addEventListener('input', calculateTotal);
    qtyPerBox.addEventListener('input', calculateTotal);
</script>
@endsection