<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Insurance Management</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(session('success'))
                    <div class="bg-green-100 text-green-700 p-3 mb-4">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 text-red-700 p-3 mb-4">{{ session('error') }}</div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-2">Request #</th>
                                <th class="border p-2">Client</th>
                                <th class="border p-2">Insurance Status</th>
                                <th class="border p-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            <tr>
                                <td class="border p-2">#{{ $req->id }}</td>
                                <td class="border p-2">
                                    {{ $req->client->name ?? 'N/A' }}<br>
                                    <span class="text-xs">{{ $req->client->email ?? '' }}</span>
                                </td>
                                <td class="border p-2">
                                    @if($req->insurance)
                                        <span class="px-2 py-1 rounded text-xs 
                                            @if($req->insurance->status == 'active') bg-green-200 text-green-800
                                            @elseif($req->insurance->status == 'expired') bg-red-200 text-red-800
                                            @else bg-yellow-200 text-yellow-800 @endif">
                                            {{ ucfirst($req->insurance->status) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Not created</span>
                                    @endif
                                </td>
                                <td class="border p-2">
                                   <a href="{{ route('admin.insurance.show', $req->id) }}" class="bg-blue-500 text-white px-2 py-1 rounded inline-block text-center">View Insurance</a>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-gray-500">No warehouse requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>