@extends('layouts.app')

@section('title', 'Equipment Jobs')
@section('header', 'Equipment Jobs')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
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

    <div class="mb-4">
        <h3 class="text-lg font-bold text-gray-800">Client Equipment Requests</h3>
        <p class="text-sm text-gray-500">View and respond to client requests for equipment</p>
    </div>

    <div class="space-y-4">
        @forelse($requests ?? [] as $request)
        <div class="border rounded-lg p-4 hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold text-lg">Request #{{ $request->id }}</p>
                    <p class="text-gray-600">Client: {{ $request->client->name ?? 'N/A' }}</p>
                    <p class="text-gray-600">Equipment Type: {{ ucfirst($request->equipment_type) }}</p>
                    @if($request->equipment_name)
                    <p class="text-gray-600">Preferred: {{ $request->equipment_name }}</p>
                    @endif
                    <p class="text-gray-600">Duration: {{ $request->duration_days }} days</p>
                    <p class="text-gray-600">Location: {{ $request->location }}</p>
                    <p class="text-sm text-gray-500">Requested: {{ $request->created_at->format('Y-m-d H:i') }}</p>
                    @if($request->description)
                    <p class="text-sm text-gray-600 mt-2">Requirements: {{ $request->description }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <span class="status-badge status-{{ $request->status }}">
                        {{ ucfirst($request->status) }}
                    </span>
                    @if($request->proposed_budget)
                    <p class="text-sm text-gray-600 mt-2">Client Budget: रु {{ number_format($request->proposed_budget) }}</p>
                    @endif
                </div>
            </div>
            
            @if($request->status == 'pending')
            <div class="mt-4 pt-3 border-t flex justify-end space-x-3">
                <button onclick="showPriceModal({{ $request->id }})" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                    <i class="fas fa-tag mr-2"></i> Propose Price
                </button>
            </div>
            @elseif($request->status == 'price_proposed')
            <div class="mt-4 pt-3 border-t">
                <p class="text-orange-600">Waiting for client response on your price proposal...</p>
            </div>
            @elseif($request->status == 'accepted')
            <div class="mt-4 pt-3 border-t">
                <p class="text-green-600">Job accepted! Waiting for job completion.</p>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-12">
            <i class="fas fa-briefcase text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No job requests found</p>
            <p class="text-gray-400">When clients request equipment that matches your inventory, jobs will appear here</p>
        </div>
        @endforelse
    </div>
    
    <div class="mt-4">
        {{ $requests->links() }}
    </div>
</div>

<!-- Price Proposal Modal -->
<div id="priceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-96">
        <h3 class="text-lg font-bold mb-4">Propose Price for Equipment</h3>
        <form id="priceForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Your Quote (NPR)</label>
                <input type="number" name="price" required class="w-full px-4 py-2 border rounded-lg" placeholder="Enter your price">
                <p class="text-xs text-gray-500 mt-1">This price will be sent to the client for approval</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closePriceModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg">Submit Quote</button>
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
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-price_proposed { background: #e0e7ff; color: #4338ca; }
    .status-accepted { background: #d1fae5; color: #059669; }
    .status-completed { background: #dbeafe; color: #2563eb; }
    .status-cancelled { background: #fee2e2; color: #dc2626; }
</style>

@push('scripts')
<script>
    function showPriceModal(requestId) {
        const modal = document.getElementById('priceModal');
        const form = document.getElementById('priceForm');
        form.action = '/equipment/propose-price/' + requestId;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    function closePriceModal() {
        const modal = document.getElementById('priceModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
@endsection