<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Partner Price Proposals</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if($offers->count())
                    <table class="min-w-full border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th>Partner</th>
                                <th>Job Type</th>
                                <th>Job ID</th>
                                <th>Proposed Price</th>
                                <th>Admin Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offers as $offer)
                            <tr>
                                <td>{{ $offer->partner->name }} ({{ $offer->partner->email }})</td>
                                <td>{{ str_replace('_', ' ', $offer->job_type) }}</td>
                                <td>#{{ $offer->job_id }}</td>
                                <td>Rs. {{ number_format($offer->proposed_price, 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.partner-offers.approve', $offer->id) }}">
                                        @csrf
                                        <select name="margin_type" required>
                                            <option value="fixed">Fixed (NPR)</option>
                                            <option value="percentage">Percentage (%)</option>
                                        </select>
                                        <input type="number" step="0.01" name="margin_value" placeholder="Amount or %" required>
                                        <input type="text" name="admin_notes" placeholder="Notes (optional)">
                                        <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded">Approve</button>
                                    </form>
                                  </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No pending proposals.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>