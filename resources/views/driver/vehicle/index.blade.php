<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Vehicles</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <a href="{{ route('driver.vehicles.create') }}" class="bg-green-500 text-white px-4 py-2 rounded inline-block mb-4">+ Register New Vehicle</a>
                @if(session('success'))
                    <div class="bg-green-100 text-green-700 p-3 mb-4">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 text-red-700 p-3 mb-4">{{ session('error') }}</div>
                @endif
                @if($vehicles->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">Type</th>
                                    <th class="border p-2">Reg No</th>
                                    <th class="border p-2">Driver Name</th>
                                    <th class="border p-2">Phone</th>
                                    <th class="border p-2">Capacity (boxes)</th>
                                    <th class="border p-2">Current Load</th>
                                    <th class="border p-2">Status</th>
                                    <th class="border p-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicles as $v)
                                <tr>
                                    <td class="border p-2">{{ $v->type }}</td>
                                    <td class="border p-2">{{ $v->registration_number }}</td>
                                    <td class="border p-2">{{ $v->driver_name }}</td>
                                    <td class="border p-2">{{ $v->driver_phone }}</td>
                                    <td class="border p-2">{{ $v->capacity_boxes ?? '-' }}</td>
                                    <td class="border p-2">{{ $v->current_load }}</td>
                                    <td class="border p-2">{{ $v->is_available ? 'Available' : 'Busy' }}</td>
                                    <td class="border p-2">
                                        <a href="{{ route('driver.vehicles.edit', $v->id) }}" class="text-blue-600">Edit</a>
                                        <form method="POST" action="{{ route('driver.vehicles.destroy', $v->id) }}" class="inline" onsubmit="return confirm('Delete this vehicle?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 ml-2">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>No vehicles registered yet. Click the button above to add one.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>