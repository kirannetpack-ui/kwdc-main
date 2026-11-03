@extends('layouts.app')

@section('title', 'Register Boxes')
@section('header', 'Register New Box/Batch')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <form method="POST" action="{{ route('boxes.store') }}" enctype="multipart/form-data" id="boxForm">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">📦 Batch/Box Information</h3>
            </div>
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Invoice Number *</label>
                <input type="text" name="invoice_number" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Shipper Name *</label>
                <input type="text" name="shipper_name" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Select Warehouse *</label>
                <select name="warehouse_id" id="warehouse_id" required class="w-full px-4 py-2 border rounded-lg">
                    <option value="">Select Warehouse</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" 
                            data-contact="{{ $warehouse->contact_person }} - {{ $warehouse->contact_phone }}">
                        {{ $warehouse->name }} - {{ $warehouse->location }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Total Number of Boxes *</label>
                <input type="number" name="total_boxes" id="total_boxes" min="1" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Entry Date</label>
                <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2 border rounded-lg bg-gray-100" readonly>
            </div>
            
            <div class="col-span-2">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2 mt-4">📄 Documents</h3>
            </div>
            
            <!-- Invoice Document -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Invoice Document *</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-orange-500 transition" onclick="document.getElementById('invoice_doc').click()">
                    <i class="fas fa-file-invoice text-3xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500 text-sm">Click to upload invoice</p>
                    <p class="text-gray-400 text-xs">PDF, JPG, PNG (Max 5MB)</p>
                    <input type="file" name="invoice_document" id="invoice_doc" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div id="invoice_preview" class="mt-2 hidden">
                    <span class="text-sm text-green-600"><i class="fas fa-check-circle"></i> <span id="invoice_filename"></span></span>
                </div>
                @error('invoice_document') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            
            <!-- Packing List Document -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Packing List</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-orange-500 transition" onclick="document.getElementById('packing_doc').click()">
                    <i class="fas fa-boxes text-3xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500 text-sm">Click to upload packing list</p>
                    <p class="text-gray-400 text-xs">PDF, JPG, PNG (Max 5MB)</p>
                    <input type="file" name="packing_list_document" id="packing_doc" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div id="packing_preview" class="mt-2 hidden">
                    <span class="text-sm text-green-600"><i class="fas fa-check-circle"></i> <span id="packing_filename"></span></span>
                </div>
                @error('packing_list_document') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            
            <!-- Insurance Document -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Insurance Document</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-orange-500 transition" onclick="document.getElementById('insurance_doc').click()">
                    <i class="fas fa-shield-alt text-3xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500 text-sm">Click to upload insurance</p>
                    <p class="text-gray-400 text-xs">PDF, JPG, PNG (Max 5MB)</p>
                    <input type="file" name="insurance_document" id="insurance_doc" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div id="insurance_preview" class="mt-2 hidden">
                    <span class="text-sm text-green-600"><i class="fas fa-check-circle"></i> <span id="insurance_filename"></span></span>
                </div>
                @error('insurance_document') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            
            <!-- Additional Documents -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Other Documents</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-orange-500 transition" onclick="document.getElementById('other_docs').click()">
                    <i class="fas fa-file-alt text-3xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500 text-sm">Click to upload other documents</p>
                    <p class="text-gray-400 text-xs">Multiple files (PDF, JPG, PNG, Max 5MB each)</p>
                    <input type="file" name="other_documents[]" id="other_docs" class="hidden" accept=".pdf,.jpg,.jpeg,.png" multiple>
                </div>
                <div id="other_preview" class="mt-2">
                    <ul id="other_files_list" class="text-sm text-gray-600"></ul>
                </div>
                @error('other_documents.*') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div class="col-span-2">
                <label class="block text-gray-700 font-semibold mb-2">Notes (Optional)</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg" placeholder="Any special instructions or notes..."></textarea>
            </div>
        </div>
        
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                <div>
                    <p class="text-blue-800 font-semibold">Required Documents</p>
                    <p class="text-blue-600 text-sm">Invoice document is mandatory. Packing list and insurance are recommended.</p>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end space-x-4 mt-6">
            <a href="{{ route('boxes.index') }}" class="px-6 py-2 bg-gray-300 rounded-lg">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                <i class="fas fa-qrcode mr-2"></i> Generate Boxes
            </button>
        </div>
    </form>
</div>

<script>
    // Preview functions for documents
    function showFilePreview(input, previewId, nameId) {
        const preview = document.getElementById(previewId);
        const nameSpan = document.getElementById(nameId);
        if (input.files && input.files[0]) {
            nameSpan.textContent = input.files[0].name;
            preview.classList.remove('hidden');
        }
    }
    
    function showMultipleFilesPreview(input, listId) {
        const list = document.getElementById(listId);
        list.innerHTML = '';
        if (input.files) {
            for (let i = 0; i < input.files.length; i++) {
                const li = document.createElement('li');
                li.className = 'text-green-600';
                li.innerHTML = '<i class="fas fa-check-circle mr-1"></i> ' + input.files[i].name;
                list.appendChild(li);
            }
        }
    }
    
    document.getElementById('invoice_doc').addEventListener('change', function() {
        showFilePreview(this, 'invoice_preview', 'invoice_filename');
    });
    document.getElementById('packing_doc').addEventListener('change', function() {
        showFilePreview(this, 'packing_preview', 'packing_filename');
    });
    document.getElementById('insurance_doc').addEventListener('change', function() {
        showFilePreview(this, 'insurance_preview', 'insurance_filename');
    });
    document.getElementById('other_docs').addEventListener('change', function() {
        showMultipleFilesPreview(this, 'other_files_list');
    });
    
    document.getElementById('total_boxes').addEventListener('change', function() {
        const total = parseInt(this.value);
        if (total > 100) {
            alert('Maximum 100 boxes per batch. Please create multiple batches if needed.');
            this.value = 100;
        }
    });
</script>
@endsection