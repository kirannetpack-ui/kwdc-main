<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage User Roles</h2>
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
                                <th class="border p-2">ID</th>
                                <th class="border p-2">Name</th>
                                <th class="border p-2">Email</th>
                                <th class="border p-2">Admin</th>
                                <th class="border p-2">Driver</th>
                                <th class="border p-2">Equipment Owner</th>
                                <th class="border p-2">Property Owner</th>
                                <th class="border p-2">Client</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td class="border p-2">{{ $user->id }}</td>
                                <td class="border p-2">{{ $user->name }}</td>
                                <td class="border p-2">{{ $user->email }}</td>
                                <td class="border p-2">{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                                <td class="border p-2">{{ $user->is_driver ? 'Yes' : 'No' }}</td>
                                <td class="border p-2">{{ $user->is_equipment_owner ? 'Yes' : 'No' }}</td>
                                
                                <!-- Property Owner Toggle -->
                                <td class="border p-2">
                                    <form method="POST" action="{{ route('admin.users.toggleRole', $user->id) }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="role" value="is_property_owner">
                                        <button type="submit" style="background-color: {{ $user->is_property_owner ? '#22c55e' : '#9ca3af' }}; color: white; font-weight: bold; padding: 4px 12px; border-radius: 6px; border: none; cursor: pointer;">
                                            {{ $user->is_property_owner ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                
                                <!-- Client Toggle -->
                                <td class="border p-2">
                                    <form method="POST" action="{{ route('admin.users.toggleRole', $user->id) }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="role" value="is_client">
                                        <button type="submit" style="background-color: {{ $user->is_client ? '#22c55e' : '#9ca3af' }}; color: white; font-weight: bold; padding: 4px 12px; border-radius: 6px; border: none; cursor: pointer;">
                                            {{ $user->is_client ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>