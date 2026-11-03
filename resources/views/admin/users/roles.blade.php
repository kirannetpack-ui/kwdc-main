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
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Driver</th>
                                <th>Equipment Owner</th>
                                <th>Property Owner</th>
                                <th>Client</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td class="border p-2">{{ $user->id }}</td>
                                <td class="border p-2">{{ $user->name }}</td>
                                <td class="border p-2">{{ $user->email }}</td>
                                <td class="border p-2 text-center">
                                    <form method="POST" action="{{ route('admin.roles.toggle', $user->id) }}">
                                        @csrf
                                        <input type="hidden" name="role" value="is_driver">
                                        <button type="submit" class="px-2 py-1 rounded text-white {{ $user->is_driver ? 'bg-green-600' : 'bg-gray-400' }}">
                                            {{ $user->is_driver ? 'Yes' : 'No' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="border p-2 text-center">
                                    <form method="POST" action="{{ route('admin.roles.toggle', $user->id) }}">
                                        @csrf
                                        <input type="hidden" name="role" value="is_equipment_owner">
                                        <button type="submit" class="px-2 py-1 rounded text-white {{ $user->is_equipment_owner ? 'bg-green-600' : 'bg-gray-400' }}">
                                            {{ $user->is_equipment_owner ? 'Yes' : 'No' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="border p-2 text-center">
                                    <form method="POST" action="{{ route('admin.roles.toggle', $user->id) }}">
                                        @csrf
                                        <input type="hidden" name="role" value="is_property_owner">
                                        <button type="submit" class="px-2 py-1 rounded text-white {{ $user->is_property_owner ? 'bg-green-600' : 'bg-gray-400' }}">
                                            {{ $user->is_property_owner ? 'Yes' : 'No' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="border p-2 text-center">
                                    <form method="POST" action="{{ route('admin.roles.toggle', $user->id) }}">
                                        @csrf
                                        <input type="hidden" name="role" value="is_client">
                                        <button type="submit" class="px-2 py-1 rounded text-white {{ $user->is_client ? 'bg-green-600' : 'bg-gray-400' }}">
                                            {{ $user->is_client ? 'Yes' : 'No' }}
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