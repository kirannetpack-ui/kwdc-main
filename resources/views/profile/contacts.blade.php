@extends('layouts.app')

@section('title', 'Manage Contacts')
@section('header', 'Manage Notification Contacts')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800">Additional Contacts</h3>
        <button onclick="openAddModal()" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
            <i class="fas fa-plus mr-2"></i> Add Contact
        </button>
    </div>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Phone</th>
                    <th class="px-6 py-3 text-left">WhatsApp</th>
                    <th class="px-6 py-3 text-left">Relation</th>
                    <th class="px-6 py-3 text-left">Receive Emails</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                <tr>
                    <td class="px-6 py-4">{{ $contact->name }} @if($contact->is_primary)<span class="text-xs text-orange-500 ml-1">(Primary)</span>@endif</td>
                    <td class="px-6 py-4">{{ $contact->email ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $contact->phone ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $contact->whatsapp ?? '-' }}</td>
                    <td class="px-6 py-4">{{ ucfirst($contact->relation ?? 'General') }}</td>
                    <td class="px-6 py-4">
                        @if($contact->receive_emails)
                            <span class="text-green-500"><i class="fas fa-check-circle"></i></span>
                        @else
                            <span class="text-gray-400"><i class="fas fa-times-circle"></i></span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <form action="{{ route('profile.contacts.destroy', $contact->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Delete this contact?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add Contact Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Add Contact</h3>
        <form method="POST" action="{{ route('profile.contacts.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Name *</label>
                <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Email</label>
                <input type="email" name="email" class="w-full px-4 py-2 border rounded-lg">
                <p class="text-xs text-gray-500 mt-1">Will receive email notifications</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Phone</label>
                <input type="text" name="phone" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">WhatsApp</label>
                <input type="text" name="whatsapp" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Relation</label>
                <select name="relation" class="w-full px-4 py-2 border rounded-lg">
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="accountant">Accountant</option>
                    <option value="staff">Staff</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="receive_emails" value="1" class="mr-2" checked>
                    <span>Receive email notifications</span>
                </label>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg">Add Contact</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
        document.getElementById('addModal').classList.add('flex');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
        document.getElementById('addModal').classList.remove('flex');
    }
</script>
@endsection