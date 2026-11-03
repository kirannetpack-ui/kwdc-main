<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tenants of {{ $warehouse->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if($tenants->count())
                    <table class="min-w-full border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-2">Client</th>
                                <th class="border p-2">Space taken</th>
                                <th class="border p-2">Duration</th>
                                <th class="border p-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $req)
                            <tr>
                                <td class="border p-2">{{ $req->client->name }}<br>{{ $req->client->email }}</td>
                                <td class="border p-2">
                                    @if($req->assigned_warehouse_id == $warehouse->id)
                                        {{ $req->required_space }} m³
                                    @else
                                        {{ $req->pivot->allocated_space ?? $req->required_space }} m³
                                    @endif
                                </td>
                                <td class="border p-2">{{ $req->duration_months }} months</td>
                                <td class="border p-2">
                                    <form method="POST" action="{{ route('admin.warehouse.release', [$warehouse->id, $req->id]) }}" onsubmit="return confirm('Remove this tenant? Space will be freed.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded">Remove</button>
                                    </form>
                                </table>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No tenants assigned.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>