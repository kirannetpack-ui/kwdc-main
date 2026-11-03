@extends('layouts.app')

@section('title', 'Edit Driver - ' . $driver->name)
@section('header', 'Edit Driver')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.drivers.show', $driver->id) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-orange-600 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Driver Details
        </a>
        <h2 class="text-2xl font-bold text-gray-900 mt-2">Edit Driver Information</h2>
        <p class="text-sm text-gray-500">Update account status, contact information, and operational details.</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
        <form method="POST" action="{{ route('admin.drivers.update', $driver->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $driver->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $driver->email) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $driver->phone) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
                </div>

                <!-- Rating -->
                <div>
                    <label for="avg_rating" class="block text-sm font-semibold text-gray-700 mb-1">Rating (0 - 5)</label>
                    <input type="number" step="0.1" min="0" max="5" name="avg_rating" id="avg_rating" value="{{ old('avg_rating', $driver->avg_rating ?? 5.0) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
                </div>
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-sm font-semibold text-gray-700 mb-1">Operating Address / Region</label>
                <input type="text" name="address" id="address" value="{{ old('address', $driver->address) }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
            </div>

            <!-- Account Status -->
            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $driver->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 text-orange-600 rounded border-gray-300 focus:ring-orange-500">
                    <div>
                        <span class="text-sm font-semibold text-gray-900">Active Account</span>
                        <p class="text-xs text-gray-500">Driver can receive dispatch and pickup orders when active.</p>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                <a href="{{ route('admin.drivers.show', $driver->id) }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-orange-600 text-white text-sm font-semibold hover:bg-orange-700 transition shadow-sm">
                    <i class="fas fa-save mr-1.5"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
