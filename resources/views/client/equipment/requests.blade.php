@extends('layouts.app')

@section('title', 'My Equipment Requests')
@section('header', 'My Equipment Requests')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800">My Equipment Requests</h3>
        <a href="{{ route('client.equipment.request') }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
            <i class="fas fa-plus mr-2"></i> New Request
        </a>
    </div>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif
    
    <div class="space-y-4">
        @forelse($requests ?? [] as $request)
        <div class="border rounded-lg p-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold">Request #{{ $request->id }}</p>
                    <p class="text-gray-600">Equipment: {{ ucfirst($request->equipment_type) }} {{ $request->equipment_name ? '(' . $request->equipment_name . ')' : '' }}</p>
                    <p class="text-gray-600">Duration: {{ $request->duration_days }} days</p>
                    <p class="text-gray-600">Location: {{ $request->location }}</p>
                    <p class="text-sm text-gray-500">Requested: {{ $request->created_at->format('Y-m-d H:i') }}</p>
                </div>
                <div class="text-right">
                    <span class="status-badge status-{{ $request->status }}">
                        {{ ucfirst($request->status) }}
                    </span>
                    @if($request->proposed_budget)
                    <p class="text-sm text-gray-600 mt-2">Your Budget: रु {{ number_format($request->proposed_budget) }}</p>
                    @endif
                    @if($request->quoted_price)
                    <p class="text-sm text-orange-600 font-semibold mt-2">Quoted: रु {{ number_format($request->quoted_price) }}</p>
                    @endif
                </div>
            </div>
            
            @if($request->status == 'price_proposed' && $request->quoted_price)
            <div class="mt-3 pt-3 border-t flex justify-end space-x-3">
                <form action="{{ route('client.equipment.request.accept', $request->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                        <i class="fas fa-check mr-2"></i> Accept Quote
                    </button>
                </form>
                <form action="{{ route('client.equipment.request.reject', $request->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        <i class="fas fa-times mr-2"></i> Reject
                    </button>
                </form>
                <button onclick="negotiatePrice({{ $request->id }}, {{ $request->quoted_price }})" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    <i class="fas fa-negotiate mr-2"></i> Negotiate
                </button>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-12">
            <i class="fas fa-tools text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No equipment requests yet</p>
            <a href="{{ route('client.equipment.request') }}" class="text-orange-500 mt-2 inline-block">Request Equipment →</a>
        </div>
        @endforelse
    </div>
    
    <div class="mt-4">
        {{ $requests->links() }}
    </div>
</div>

<!-- Negotiate Modal -->
<div id="negotiateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-96">
        <h3 class="text-lg font-bold mb-4">Negotiate Price</h3>
        <form id="negotiateForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Your Counter Offer (NPR)</label>
                <input type="number" name="counter_price" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeNegotiateModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg">Submit</button>
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

<script>
    function negotiatePrice(id, currentPrice) {
        const modal = document.getElementById('negotiateModal');
        const form = document.getElementById('negotiateForm');
        form.action = '/client/equipment/request/' + id + '/negotiate';
        document.querySelector('#negotiateForm input[name="counter_price"]').value = currentPrice;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    function closeNegotiateModal() {
        const modal = document.getElementById('negotiateModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection