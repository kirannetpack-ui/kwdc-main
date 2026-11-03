@extends('layouts.app')

@section('title', 'Stock Details')

@section('header', 'Stock Batch Details')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('stock.index') }}" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-2"></i> Back to Stock List
        </a>
    </div>

    <!-- Batch Header with QR Code -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-4 border-b">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <h3 class="text-2xl font-bold text-gray-800">{{ $stock->product_name }}</h3>
                @if($stock->status == 'in_stock')
                    <span class="status-badge status-approved">In Stock</span>
                @elseif($stock->status == 'partial')
                    <span class="status-badge status-on_the_way">Partial</span>
                @elseif($stock->status == 'dispatched')
                    <span class="status-badge status-assigned">Dispatched</span>
                @else
                    <span class="status-badge status-rejected">Expired</span>
                @endif
            </div>
            <div class="flex flex-wrap gap-3 text-sm">
                <span class="font-mono bg-gray-100 px-2 py-1 rounded">Batch: {{ $stock->batch_id }}</span>
                <span class="font-mono bg-gray-100 px-2 py-1 rounded">SKU: {{ $stock->sku }}</span>
            </div>
        </div>
        <div class="text-center mt-4 md:mt-0">
            @if($stock->qr_code_path && Storage::disk('public')->exists($stock->qr_code_path))
                <div class="bg-white p-2 rounded-lg shadow-md inline-block">
                    <img src="{{ asset('storage/' . $stock->qr_code_path) }}" alt="QR Code" class="w-28 h-28">
                </div>
                <div class="mt-2 flex gap-2 justify-center">
                    <a href="{{ route('stock.download-qr', $stock->id) }}" class="text-sm text-blue-500 hover:text-blue-700">
                        <i class="fas fa-download mr-1"></i> Download QR
                    </a>
                    <button onclick="printQRCode()" class="text-sm text-green-500 hover:text-green-700">
                        <i class="fas fa-print mr-1"></i> Print QR
                    </button>
                </div>
            @else
                <div class="bg-gray-100 p-4 rounded-lg text-center">
                    <i class="fas fa-qrcode text-gray-400 text-4xl mb-2"></i>
                    <p class="text-sm text-gray-500">QR Code not available</p>
                    <button onclick="regenerateQR({{ $stock->id }})" class="mt-2 text-sm text-orange-500">Regenerate</button>
                </div>
            @endif
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Left Column -->
        <div class="space-y-6">
            <!-- Product Information -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-box text-orange-500 mr-2"></i> Product Information
                </h4>
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-600 w-1/3">Product Name:</td>
                        <td class="py-2 font-medium">{{ $stock->product_name }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-600">Description:</td>
                        <td class="py-2">{{ $stock->description ?? '-' }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-600">Unit:</td>
                        <td class="py-2"><span class="bg-white px-2 py-1 rounded">{{ $stock->unit }}</span></td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Batch ID:</td>
                        <td class="py-2 font-mono text-orange-600">{{ $stock->batch_id }}</td>
                    </tr>
                </table>
            </div>

            <!-- Box & Quantity Details -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-cubes text-orange-500 mr-2"></i> Box & Quantity Details
                </h4>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-white rounded p-3 text-center">
                        <p class="text-xs text-gray-500">Number of Boxes</p>
                        <p class="text-xl font-bold text-gray-800">{{ $stock->number_of_boxes }}</p>
                    </div>
                    <div class="bg-white rounded p-3 text-center">
                        <p class="text-xs text-gray-500">Quantity per Box</p>
                        <p class="text-xl font-bold text-gray-800">{{ $stock->quantity_per_box }}</p>
                    </div>
                </div>
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-600">Total Quantity:</td>
                        <td class="py-2 font-bold text-green-600">{{ number_format($stock->total_quantity) }} {{ $stock->unit }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Remaining Quantity:</td>
                        <td class="py-2 font-bold {{ $stock->remaining_quantity < $stock->total_quantity * 0.2 ? 'text-red-600' : 'text-orange-600' }}">
                            {{ number_format($stock->remaining_quantity) }} {{ $stock->unit }}
                            @if($stock->remaining_quantity < $stock->total_quantity * 0.2)
                                <span class="text-xs text-red-500 ml-2">(Low Stock Warning)</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Pricing Information -->
            @if($stock->purchase_price || $stock->selling_price)
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-tag text-orange-500 mr-2"></i> Pricing Information
                </h4>
                <table class="w-full text-sm">
                    @if($stock->purchase_price)
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-600">Purchase Price (per {{ $stock->unit }}):</td>
                        <td class="py-2 font-medium">Rs. {{ number_format($stock->purchase_price, 2) }}</td>
                    </tr>
                    @endif
                    @if($stock->selling_price)
                    <tr>
                        <td class="py-2 text-gray-600">Selling Price (per {{ $stock->unit }}):</td>
                        <td class="py-2 font-medium">Rs. {{ number_format($stock->selling_price, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Invoice Information -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-file-invoice text-orange-500 mr-2"></i> Invoice Information
                </h4>
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-600 w-1/3">Invoice Number:</td>
                        <td class="py-2 font-mono">{{ $stock->invoice_number ?? '-' }}</td>
                    </tr>
                    @if($stock->invoice_file_path)
                    <tr>
                        <td class="py-2 text-gray-600">Invoice File:</td>
                        <td class="py-2">
                            <a href="{{ route('stock.download-document', ['id' => $stock->id, 'type' => 'invoice']) }}" 
                               class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-file-pdf mr-1"></i> Download Invoice
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Warehouse & Client Information -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-warehouse text-orange-500 mr-2"></i> Warehouse & Client
                </h4>
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-600 w-1/3">Warehouse:</td>
                        <td class="py-2">{{ $stock->warehouse_name ?? 'Not Assigned' }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-600">Client Name:</td>
                        <td class="py-2">{{ $stock->client_name }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Client Code:</td>
                        <td class="py-2 font-mono">{{ $stock->client_code }}</td>
                    </tr>
                </table>
            </div>

            <!-- Important Dates -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-calendar-alt text-orange-500 mr-2"></i> Important Dates
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white rounded p-2 text-center">
                        <p class="text-xs text-gray-500">Manufacturing</p>
                        <p class="text-sm font-medium">{{ $stock->manufacturing_date ? $stock->manufacturing_date->format('d M Y') : '-' }}</p>
                    </div>
                    <div class="bg-white rounded p-2 text-center">
                        <p class="text-xs text-gray-500">Expiry Date</p>
                        <p class="text-sm font-medium {{ $stock->expiry_date && $stock->expiry_date->isPast() ? 'text-red-600' : '' }}">
                            {{ $stock->expiry_date ? $stock->expiry_date->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <div class="bg-white rounded p-2 text-center">
                        <p class="text-xs text-gray-500">Received Date</p>
                        <p class="text-sm font-medium">{{ $stock->received_date ? $stock->received_date->format('d M Y') : '-' }}</p>
                    </div>
                    <div class="bg-white rounded p-2 text-center">
                        <p class="text-xs text-gray-500">Added On</p>
                        <p class="text-sm font-medium">{{ $stock->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Supporting Documents -->
            @if($stock->grn_file_path || $stock->quality_certificate_path || $stock->other_documents_path)
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-paperclip text-orange-500 mr-2"></i> Supporting Documents
                </h4>
                <div class="space-y-2">
                    @if($stock->grn_file_path)
                        <a href="{{ route('stock.download-document', ['id' => $stock->id, 'type' => 'grn']) }}" 
                           class="flex items-center p-2 bg-white rounded hover:bg-gray-50">
                            <i class="fas fa-file-alt text-blue-500 mr-3"></i>
                            <span class="text-sm">GRN (Goods Receipt Note)</span>
                            <i class="fas fa-download ml-auto text-gray-400"></i>
                        </a>
                    @endif
                    @if($stock->quality_certificate_path)
                        <a href="{{ route('stock.download-document', ['id' => $stock->id, 'type' => 'certificate']) }}" 
                           class="flex items-center p-2 bg-white rounded hover:bg-gray-50">
                            <i class="fas fa-certificate text-green-500 mr-3"></i>
                            <span class="text-sm">Quality Certificate</span>
                            <i class="fas fa-download ml-auto text-gray-400"></i>
                        </a>
                    @endif
                    @if($stock->other_documents_path)
                        <a href="{{ route('stock.download-document', ['id' => $stock->id, 'type' => 'other']) }}" 
                           class="flex items-center p-2 bg-white rounded hover:bg-gray-50">
                            <i class="fas fa-file-pdf text-red-500 mr-3"></i>
                            <span class="text-sm">Other Documents</span>
                            <i class="fas fa-download ml-auto text-gray-400"></i>
                        </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- QR Code Data (Hidden for printing) -->
    <div id="qrPrintArea" style="display: none;">
        <div style="text-align: center; padding: 20px;">
            <img src="{{ asset('storage/' . $stock->qr_code_path) }}" alt="QR Code" style="width: 200px; height: 200px;">
            <h3>{{ $stock->product_name }}</h3>
            <p>Batch ID: {{ $stock->batch_id }}</p>
            <p>SKU: {{ $stock->sku }}</p>
            <p>Quantity: {{ $stock->remaining_quantity }} {{ $stock->unit }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 pt-4 border-t flex flex-wrap justify-between gap-3">
        <div>
            <button onclick="window.print()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                <i class="fas fa-print mr-2"></i> Print Details
            </button>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('stock.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                <i class="fas fa-list mr-2"></i> Back to List
            </a>
            <a href="{{ route('stock.create') }}" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                <i class="fas fa-plus mr-2"></i> Add New Stock
            </a>
        </div>
    </div>
</div>

<script>
    function printQRCode() {
        const printContents = document.getElementById('qrPrintArea').innerHTML;
        const originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }
    
    function regenerateQR(stockId) {
        if (confirm('Regenerate QR code? This will create a new QR code for this batch.')) {
            window.location.href = `/stock/${stockId}/regenerate-qr`;
        }
    }
</script>

<style>
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    .status-approved { background: #d1fae5; color: #059669; }
    .status-on_the_way { background: #fed7aa; color: #ea580c; }
    .status-assigned { background: #dbeafe; color: #2563eb; }
    .status-rejected { background: #fee2e2; color: #dc2626; }
    
    @media print {
        .sidebar, .top-bar, .bg-orange-500, .bg-gray-500, .bg-blue-500, 
        .status-badge, a[href*="download"], button, form, .no-print {
            display: none !important;
        }
        body {
            background: white;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>
@endsection