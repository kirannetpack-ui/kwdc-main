@extends('layouts.app')

@section('title', 'My Warehouses')
@section('header', 'My Warehouses')

@section('content')

<div class="card mb-4">
    <div class="card-body">
        <a class="btn btn-primary" href="{{ route('my-requests.create') }}">Search warehouse availability</a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md p-6">
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800">My Registered Warehouses</h3>
        <a href="{{ route('warehouses.create') }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition">
            <i class="fas fa-plus mr-2"></i> Register New Warehouse
        </a>
    </div>

    @if($warehouses->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($warehouses as $warehouse)
        <div class="border rounded-lg p-4 hover:shadow-lg transition">
            @if($warehouse->front_photo)
            <img src="{{ Storage::disk('public')->url($warehouse->front_photo) }}" alt="{{ $warehouse->name }}" class="w-full h-40 object-cover rounded-lg mb-3">
            @else
            <div class="w-full h-40 bg-gray-200 rounded-lg mb-3 flex items-center justify-center">
                <i class="fas fa-warehouse text-4xl text-gray-400"></i>
            </div>
            @endif
            
            <h3 class="font-bold text-lg">{{ $warehouse->name }}</h3>
            <p class="text-gray-600 text-sm">{{ $warehouse->location ?? $warehouse->address }}</p>
            <p class="text-orange-600 font-bold mt-2">रु {{ number_format($warehouse->price_per_unit ?? 0) }}/sq ft</p>
            
            <div class="mt-3 flex justify-between items-center">
                <span class="status-badge status-{{ $warehouse->status }}">
                    {{ ucfirst($warehouse->status ?? 'Pending') }}
                </span>
                <div class="flex space-x-2">
                    <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="text-blue-500 hover:text-blue-700">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Delete this warehouse?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="mt-6">
        {{ $warehouses->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <i class="fas fa-warehouse text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-lg">No warehouses registered yet</p>
        <p class="text-gray-400">Click the "Register New Warehouse" button to get started</p>
    </div>
    @endif
</div>
@endsection