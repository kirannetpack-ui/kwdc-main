@extends('layouts.app')

@section('title', 'Track Box')
@section('header', 'Track Box: ' . $box->batch_number)

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    @if($box->isBanned())
    <div class="mb-6 p-4 rounded-xl bg-red-50 border-2 border-red-500 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-ban"></i>
            </div>
            <div>
                <h4 class="text-red-900 font-bold text-base">🚨 CARGO QR CODE BANNED & REVOKED</h4>
                <p class="text-red-800 text-sm mt-0.5">
                    This box tracking QR has been flagged and suspended. Gate access, loading, and transit release are strictly prohibited under Muluki Civil Code 2074.
                </p>
                <div class="mt-2 text-xs bg-white text-red-900 px-3 py-1.5 rounded-lg border border-red-300 inline-block font-mono">
                    <strong>Reason:</strong> {{ $box->ban_reason ?: 'Cargo security impound / documentation irregularity.' }}
                    @if($box->banned_at)
                    <span class="text-red-600 ml-2">({{ $box->banned_at->format('Y-m-d H:i') }})</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-bold mb-4">Box Information</h3>
            <div class="space-y-2">
                <p><strong>Batch Number:</strong> {{ $box->batch_number }}</p>
                <p><strong>Box Number:</strong> {{ $box->box_number }}/{{ $box->total_boxes }}</p>
                <p><strong>Invoice Number:</strong> {{ $box->invoice_number }}</p>
                <p><strong>Shipper:</strong> {{ $box->shipper_name }}</p>
                <p><strong>Warehouse:</strong> {{ $box->warehouse->name ?? 'N/A' }}</p>
                <p><strong>Entry Date:</strong> {{ $box->entry_date->format('Y-m-d H:i') }}</p>
                <p><strong>Status:</strong> 
                    @if($box->isBanned())
                        <span class="status-badge status-banned">
                            <i class="fas fa-ban mr-1"></i> Banned / Revoked
                        </span>
                    @else
                        <span class="status-badge status-{{ $box->status }}">
                            {{ ucfirst($box->status) }}
                        </span>
                    @endif
                </p>
                @if($box->received_at)
                <p><strong>Received At:</strong> {{ $box->received_at->format('Y-m-d H:i') }}</p>
                <p><strong>Received By:</strong> {{ $box->received_by ?? 'N/A' }}</p>
                @endif
            </div>
        </div>
        
        <div>
            <h3 class="text-lg font-bold mb-4">Tracking Timeline</h3>
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center mr-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <p class="font-semibold">Box Created</p>
                        <p class="text-sm text-gray-500">{{ $box->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
                
                @if($box->status != 'pending')
                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center mr-3">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div>
                        <p class="font-semibold">In Transit</p>
                        <p class="text-sm text-gray-500">Box is on the way</p>
                    </div>
                </div>
                @endif
                
                @if($box->status == 'delivered' || $box->status == 'received')
                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center mr-3">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div>
                        <p class="font-semibold">Delivered/Received</p>
                        <p class="text-sm text-gray-500">{{ $box->received_at ? $box->received_at->format('Y-m-d H:i') : 'N/A' }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    @if($box->invoice_document || $box->packing_list_document || $box->insurance_document)
    <div class="mt-6 pt-4 border-t">
        <h3 class="text-lg font-bold mb-4">Documents</h3>
        <div class="flex flex-wrap gap-3">
            @if($box->invoice_document)
            <a href="{{ route('documents.private.show', ['path' => $box->invoice_document]) }}" target="_blank" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-file-invoice mr-2"></i> Invoice
            </a>
            @endif
            @if($box->packing_list_document)
            <a href="{{ route('documents.private.show', ['path' => $box->packing_list_document]) }}" target="_blank" class="bg-green-500 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-boxes mr-2"></i> Packing List
            </a>
            @endif
            @if($box->insurance_document)
            <a href="{{ route('documents.private.show', ['path' => $box->insurance_document]) }}" target="_blank" class="bg-orange-500 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-shield-alt mr-2"></i> Insurance
            </a>
            @endif
        </div>
    </div>
    @endif
</div>

<style>
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-in_transit { background: #dbeafe; color: #2563eb; }
    .status-delivered { background: #d1fae5; color: #059669; }
    .status-received { background: #d1fae5; color: #059669; }
    .status-banned { background: #fee2e2; color: #b91c1c; font-weight: 700; border: 1px solid #f87171; }
</style>
@endsection
