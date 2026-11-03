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
    
    <div class="mt-6 flex justify-end">
        <a href="{{ route('my-requests.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
            Back to Requests
        </a>
    </div>
</div>
@endsection