@extends('layouts.app')

@section('title', 'Equipment Owners')
@section('header', 'Equipment Owners Management')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800">All Equipment Owners</h3>
        <button class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition">
            <i class="fas fa-plus mr-2"></i> Add Owner
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipment Count</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($equipmentOwners ?? [] as $owner)
                <tr>
                    <td class="px-6 py-4">{{ $owner->name }}</td>
                    <td class="px-6 py-4">{{ $owner->email }}</td>
                    <td class="px-6 py-4">{{ $owner->phone }}</td>
                    <td class="px-6 py-4">{{ $owner->equipment_count ?? 0 }}</td>
                    <td class="px-6 py-4">
                        <button class="text-blue-500 hover:text-blue-700 mr-2"><i class="fas fa-edit"></i></button>
                        <button class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No equipment owners found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection