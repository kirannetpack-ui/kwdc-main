<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Margin Tiers</h2>
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

                <!-- Add Tier Form -->
                <form method="POST" action="{{ route('admin.margin-tiers.store') }}" class="mb-6 border-b pb-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <input type="number" step="0.01" name="min_amount" placeholder="Min amount" required class="border p-2">
                        <input type="number" step="0.01" name="max_amount" placeholder="Max amount (leave empty for unlimited)" class="border p-2">
                        <input type="number" step="0.01" name="margin_percentage" placeholder="Margin %" required class="border p-2">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add Tier</button>
                    </div>
                </form>

                <!-- Tiers Table -->
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2">Min</th>
                            <th class="border p-2">Max</th>
                            <th class="border p-2">Margin %</th>
                            <th class="border p-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tiers as $tier)
                        <tr>
                            <td class="border p-2">{{ number_format($tier->min_amount, 2) }}</td>
                            <td class="border p-2">{{ $tier->max_amount ? number_format($tier->max_amount, 2) : '∞' }}</td>
                            <td class="border p-2">{{ $tier->margin_percentage }}%</td>
                            <td class="border p-2">
                                <button onclick="editTier({{ $tier->id }}, {{ $tier->min_amount }}, '{{ $tier->max_amount }}', {{ $tier->margin_percentage }})" class="text-blue-600">Edit</button>
                                <form method="POST" action="{{ route('admin.margin-tiers.destroy', $tier->id) }}" class="inline" onsubmit="return confirm('Delete this tier?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Edit Modal -->
                <div id="edit-modal" style="display:none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded shadow-lg w-96">
                        <h3 class="font-bold mb-4">Edit Tier</h3>
                        <form method="POST" id="edit-form">
                            @csrf
                            @method('PUT')
                            <input type="number" step="0.01" name="min_amount" id="edit-min" required class="w-full border p-2 mb-2">
                            <input type="number" step="0.01" name="max_amount" id="edit-max" placeholder="Max (leave empty for unlimited)" class="w-full border p-2 mb-2">
                            <input type="number" step="0.01" name="margin_percentage" id="edit-margin" required class="w-full border p-2 mb-4">
                            <div class="flex justify-end">
                                <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function editTier(id, min, max, margin) {
                        document.getElementById('edit-min').value = min;
                        document.getElementById('edit-max').value = max ? max : '';
                        document.getElementById('edit-margin').value = margin;
                        // Use the current URL + the ID to build the action
                        var updateUrl = "{{ url('/admin/margin-tiers') }}/" + id;
                        document.getElementById('edit-form').action = updateUrl;
                        document.getElementById('edit-modal').style.display = 'flex';
                    }
                    function closeModal() {
                        document.getElementById('edit-modal').style.display = 'none';
                    }
                </script>
            </div>
        </div>
    </div>
</x-app-layout>