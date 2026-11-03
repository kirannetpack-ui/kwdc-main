<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">All Invoices</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                        <tr>
                            <td>{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->warehouseRequest->client->name }}<br>{{ $inv->warehouseRequest->client->email }}</td>
                            <td>Rs. {{ number_format($inv->amount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}</td>
                            <td>{{ ucfirst($inv->status) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>