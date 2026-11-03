@extends('layouts.app')

@section('title', 'Job Details')
@section('header', 'Job Details')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Job #{{ $job->id }}</h2>
        <a href="{{ url()->previous() }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="font-semibold text-gray-700 mb-2">Equipment Details</h3>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <p><span class="text-gray-500">Name:</span> {{ $job->equipment->name ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Type:</span> {{ $job->equipment->type ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Model:</span> {{ $job->equipment->model ?? 'N/A' }}</p>
            </div>
        </div>
        
        <div>
            <h3 class="font-semibold text-gray-700 mb-2">Client Details</h3>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <p><span class="text-gray-500">Name:</span> {{ $job->client->name ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Email:</span> {{ $job->client->email ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Phone:</span> {{ $job->client->phone ?? 'N/A' }}</p>
            </div>
        </div>
        
        <div>
            <h3 class="font-semibold text-gray-700 mb-2">Job Period</h3>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <p><span class="text-gray-500">Start Date:</span> {{ \Carbon\Carbon::parse($job->start_date)->format('F d, Y') }}</p>
                <p><span class="text-gray-500">End Date:</span> {{ \Carbon\Carbon::parse($job->end_date)->format('F d, Y') }}</p>
                <p><span class="text-gray-500">Duration:</span> {{ \Carbon\Carbon::parse($job->start_date)->diffInDays(\Carbon\Carbon::parse($job->end_date)) }} days</p>
            </div>
        </div>
        
        <div>
            <h3 class="font-semibold text-gray-700 mb-2">Job Status</h3>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <p>
                    <span class="text-gray-500">Status:</span>
                    <span class="ml-2 px-2 py-1 rounded-full text-xs 
                        @if($job->status == 'pending') bg-yellow-100 text-yellow-600
                        @elseif($job->status == 'accepted') bg-blue-100 text-blue-600
                        @elseif($job->status == 'in_progress') bg-purple-100 text-purple-600
                        @elseif($job->status == 'completed') bg-green-100 text-green-600
                        @else bg-red-100 text-red-600 @endif">
                        {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                    </span>
                </p>
                @if($job->price)
                    <p><span class="text-gray-500">Price:</span> रू {{ number_format($job->price, 2) }}</p>
                @endif
                @if($job->proposed_price)
                    <p><span class="text-gray-500">Proposed Price:</span> रू {{ number_format($job->proposed_price, 2) }}</p>
                @endif
            </div>
        </div>
        
        <div class="md:col-span-2">
            <h3 class="font-semibold text-gray-700 mb-2">Location</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p>{{ $job->location }}</p>
            </div>
        </div>
        
        <div class="md:col-span-2">
            <h3 class="font-semibold text-gray-700 mb-2">Description / Requirements</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p>{{ $job->description }}</p>
            </div>
        </div>
    </div>
    
    @if($job->status == 'pending' && auth()->user()->role == 'equipment_owner')
    <div class="mt-6 pt-4 border-t flex justify-end space-x-3">
        <form action="{{ route('equipment.jobs.accept', $job->id) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition"
                    onclick="return confirm('Accept this job?')">
                <i class="fas fa-check mr-2"></i>Accept Job
            </button>
        </form>
        <form action="{{ route('equipment.jobs.reject', $job->id) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition"
                    onclick="return confirm('Reject this job?')">
                <i class="fas fa-times mr-2"></i>Reject
            </button>
        </form>
        <button onclick="showProposePriceModal()" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition">
            <i class="fas fa-tag mr-2"></i>Propose Price
        </button>
    </div>
    @endif
</div>

<!-- Propose Price Modal -->
<div id="priceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Propose Price</h3>
            <button onclick="closePriceModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('equipment.jobs.propose-price', $job->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Proposed Price (रू)</label>
                <input type="number" name="proposed_price" required 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="Enter your price">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Message (Optional)</label>
                <textarea name="message" rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                          placeholder="Add any notes or conditions..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closePriceModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                    Submit Price
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showProposePriceModal() {
    const modal = document.getElementById('priceModal');
    modal.classList.remove('hidden');
}

function closePriceModal() {
    const modal = document.getElementById('priceModal');
    modal.classList.add('hidden');
}
</script>
@endsection