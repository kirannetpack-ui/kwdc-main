@extends('layouts.app')

@section('title', 'My Warehouses')
@section('header', 'My Warehouses')

@section('content')

<div class="card mb-4">
    <div class="card-body">
        <form id="ai-warehouse-search">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-magic"></i></span>
                <input type="text" 
                       id="search-query" 
                       class="form-control form-control-lg" 
                       placeholder="Try: '10,000 sq ft warehouse with cold storage near Kalimati under Rs 50'">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> AI Search
                </button>
            </div>
            <small class="text-muted mt-2 d-block">
                <i class="fas fa-robot"></i> Our local AI understands natural language.
            </small>
        </form>
    </div>
</div>

<!-- Container to display warehouses -->
<div id="warehouse-results">
    @includeIf('warehouses.partials.list', ['warehouses' => $warehouses])
</div>

<script>
document.getElementById('ai-warehouse-search').addEventListener('submit', async function(e) {
    e.preventDefault();
    const query = document.getElementById('search-query').value;
    const resultsDiv = document.getElementById('warehouse-results');
    
    resultsDiv.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p>AI is thinking...</p></div>';

    const response = await fetch('/warehouses/search?q=' + encodeURIComponent(query));
    const data = await response.json();

    // If results found, replace the list
    if (data.warehouses.length > 0) {
        let html = '<div class="row g-3">';
        data.warehouses.forEach(w => {
            html += `
                <div class="col-md-4">
                    <div class="warehouse-card p-3 border rounded">
                        <h5>${w.name}</h5>
                        <p class="text-muted small">${w.location}</p>
                        <p class="small mb-0">${w.area_sqft} sq ft | रू ${w.price_per_sqft}/sqft</p>
                        <a href="/warehouses/${w.id}" class="btn btn-sm btn-outline-primary mt-2">View</a>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        resultsDiv.innerHTML = html;
    } else {
        resultsDiv.innerHTML = `<div class="alert alert-warning">No warehouses found matching your description. Try adjusting your search!</div>`;
    }
});
</script>

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
            <img src="{{ Storage::url($warehouse->front_photo) }}" alt="{{ $warehouse->name }}" class="w-full h-40 object-cover rounded-lg mb-3">
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