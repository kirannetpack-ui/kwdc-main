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
                                <th class="border p-2">Status</th>
                                <th class="border p-2">Security Action</th>
				<th class="border p-2">Edit</th>
				<th class="border p-2">Delete</th>
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
                                        <button type="submit" style="background-color: {{ $user->is_driver ? '#22c55e' : '#9ca3af' }}; color: white; font-weight: bold; padding: 4px 12px; border-radius: 6px; border: none; cursor: pointer;">
                                            {{ $user->is_driver ? 'Yes' : 'No' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="border p-2 text-center">
                                    <form method="POST" action="{{ route('admin.roles.toggle', $user->id) }}">
                                        @csrf
                                        <input type="hidden" name="role" value="is_equipment_owner">
                                        <button type="submit" style="background-color: {{ $user->is_equipment_owner ? '#22c55e' : '#9ca3af' }}; color: white; font-weight: bold; padding: 4px 12px; border-radius: 6px; border: none; cursor: pointer;">
                                            {{ $user->is_equipment_owner ? 'Yes' : 'No' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="border p-2 text-center">
                                    <form method="POST" action="{{ route('admin.roles.toggle', $user->id) }}">
                                        @csrf
                                        <input type="hidden" name="role" value="is_property_owner">
                                        <button type="submit" style="background-color: {{ $user->is_property_owner ? '#22c55e' : '#9ca3af' }}; color: white; font-weight: bold; padding: 4px 12px; border-radius: 6px; border: none; cursor: pointer;">
                                            {{ $user->is_property_owner ? 'Yes' : 'No' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="border p-2 text-center">
                                    <form method="POST" action="{{ route('admin.roles.toggle', $user->id) }}">
                                        @csrf
                                        <input type="hidden" name="role" value="is_client">
                                        <button type="submit" style="background-color: {{ $user->is_client ? '#22c55e' : '#9ca3af' }}; color: white; font-weight: bold; padding: 4px 12px; border-radius: 6px; border: none; cursor: pointer;">
                                            {{ $user->is_client ? 'Yes' : 'No' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="border p-2 text-center">
                                    @if($user->isBanned())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700 border border-red-300" title="{{ $user->ban_reason }}">
                                            🚨 Banned
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                            Active
                                        </span>
                                    @endif
                                </td>
                                <td class="border p-2 text-center">
                                    @if($user->id !== auth()->id())
                                        @if($user->isBanned())
                                            <form method="POST" action="{{ route('admin.users.unban', $user->id) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="text-xs px-2.5 py-1 bg-green-600 hover:bg-green-700 text-white font-semibold rounded shadow-xs" title="Unban Account">
                                                    Unban
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" onclick="promptBanUser({{ $user->id }}, '{{ addslashes($user->name) }}')" class="text-xs px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white font-semibold rounded shadow-xs" title="Ban Account">
                                                Ban Account
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">Current Admin</span>
                                    @endif
                                </td>
<td class="border p-2 text-center">
    <a href="{{ route('admin.roles.edit', $user->id) }}" class="text-blue-600 hover:underline">Edit</a>
</td>
<td class="border p-2 text-center">
    <form method="POST" action="{{ route('admin.roles.destroy', $user->id) }}" onsubmit="return confirm('Delete this user?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:underline">Delete</button>
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

    <script>
    function promptBanUser(userId, userName) {
        const reason = prompt(`Enter mandatory security reason for banning user account "${userName}":`);
        if (reason && reason.trim()) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}/ban`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'ban_reason';
            reasonInput.value = reason.trim();
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</x-app-layout>