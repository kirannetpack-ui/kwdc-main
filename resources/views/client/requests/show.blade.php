@extends('layouts.app')

@section('title', 'Request Details')
@section('header', 'Request Details')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="mb-6">
        <h3 class="text-lg font-bold text-gray-800">Request #{{ $request->id }}</h3>
        <p class="text-gray-500">Submitted on {{ $request->created_at->format('F j, Y') }}</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-gray-600"><strong>Warehouse:</strong> {{ $request->warehouse->name ?? 'N/A' }}</p>
            <p class="text-gray-600 mt-2"><strong>Location:</strong> {{ $request->warehouse->location ?? 'N/A' }}</p>
            <p class="text-gray-600 mt-2"><strong>Required Area:</strong> {{ number_format($request->required_area) }} sq ft</p>
        </div>
        <div>
            <p class="text-gray-600"><strong>Duration:</strong> {{ $request->duration_months }} months</p>
            <p class="text-gray-600 mt-2"><strong>Status:</strong> 
                <span class="status-badge status-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
            </p>
            <p class="text-gray-600 mt-2"><strong>Purpose:</strong> {{ $request->purpose }}</p>
        </div>
    </div>
    
    @if($request->status === 'assigned' && $end = $request->agreementEnd())
        <div class="mt-6 border-t pt-4">
            <p>Agreement ends {{ $end->format('d M Y') }} ({{ max(0, (int) today()->diffInDays($end, false)) }} days left)</p>
            <p>Renewal priority ends {{ $request->renewalDeadline()->format('d M Y') }}.</p>
            @if($request->renewalChoice() === 'renew')
                <p>Your renewal request is awaiting confirmation.</p>
            @elseif($request->renewalChoice() === 'vacate')
                <p>You confirmed that you will vacate at the end of the agreement.</p>
            @elseif($request->isOpenForPrebooking())
                <p>Your renewal priority has ended. You can submit a new warehouse request.</p>
            @elseif(today()->gte($end->copy()->subMonthsNoOverflow(2)))
                <form action="{{ route('my-requests.renewal', $request->id) }}" method="POST" class="mt-3 flex gap-3">
                    @csrf
                    <button name="decision" value="renew" class="btn btn-primary">Request renewal</button>
                    <button name="decision" value="vacate" class="btn btn-outline-secondary">Vacate at agreement end</button>
                </form>
            @endif
        </div>
    @endif

    <div class="mt-6 flex justify-end">
        <a href="{{ route('my-requests.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
            Back to Requests
        </a>
    </div>
</div>
@endsection