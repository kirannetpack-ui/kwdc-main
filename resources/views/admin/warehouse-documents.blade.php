@extends('layouts.app')

@section('title', 'Warehouse Documents')
@section('header', 'Warehouse Documents')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800">All Documents</h3>
        <button class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition">
            <i class="fas fa-upload mr-2"></i> Upload Document
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uploaded By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($documents ?? [] as $doc)
                <tr>
                    <td class="px-6 py-4">{{ $doc->name }}</td>
                    <td class="px-6 py-4">{{ $doc->warehouse->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $doc->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $doc->created_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4">
                        <button class="text-blue-500 hover:text-blue-700 mr-2"><i class="fas fa-download"></i></button>
                        <button class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No documents found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection