<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Insurance for Request #{{ $warehouseRequest->id }} (Client: {{ $warehouseRequest->client->name }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.insurance.update', $insurance->id ?? 0) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Provider</label>
                        <input type="text" name="provider" value="{{ old('provider', $insurance->provider) }}" class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Policy Number</label>
                        <input type="text" name="policy_number" value="{{ old('policy_number', $insurance->policy_number) }}" class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Premium (NPR)</label>
                        <input type="number" step="0.01" name="premium" value="{{ old('premium', $insurance->premium) }}" class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $insurance->start_date ? \Carbon\Carbon::parse($insurance->start_date)->format('Y-m-d') : now()->format('Y-m-d')) }}" class="w-full border-gray-300 rounded" readonly disabled>
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $insurance->end_date ? \Carbon\Carbon::parse($insurance->end_date)->format('Y-m-d') : now()->addMonths(6)->format('Y-m-d')) }}" class="w-full border-gray-300 rounded" required>
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded" required>
                            <option value="active" {{ $insurance->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ $insurance->status == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ $insurance->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save Insurance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>