@extends('layouts.app')

@section('title', 'My Invoices')
@section('header', 'My Invoices')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-xl font-bold mb-4">My Invoices</h2>
    
    @if($invoices->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3">Invoice #</th>
                        <th class="text-left py-3">Amount</th>
                        <th class="text-left py-3">Due Date</th>
                        <th class="text-left py-3">Status</th>
                        <th class="text-left py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr class="border-b">
                        <td class="py-3">{{ $invoice->invoice_number }}</td>
                        <td class="py-3">रू {{ number_format($invoice->amount, 2) }}</td>
                        <td class="py-3">{{ $invoice->due_date }}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 rounded-full text-xs 
                                @if($invoice->status == 'paid') bg-green-100 text-green-600
                                @elseif($invoice->status == 'overdue') bg-red-100 text-red-600
                                @else bg-yellow-100 text-yellow-600 @endif">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="py-3">
                            <a href="#" class="text-blue-500 hover:text-blue-700">View Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-file-invoice text-4xl mb-3"></i>
            <p>No invoices found</p>
            <a href="{{ route('my-requests.create') }}" class="inline-block mt-3 text-orange-500 hover:text-orange-600">
                Make a warehouse request to generate invoices
            </a>
        </div>
    @endif
</div>
@endsection