@extends('layouts.app')

@section('title', 'Register Vehicle')
@section('header', 'Register Your Vehicle')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <form method="POST" action="{{ route('driver.vehicles.store') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Vehicle Type *</label>
                <select name="type" required class="w-full px-4 py-2 border rounded-lg">
                    <option value="truck">Truck</option>
                    <option value="van">Van</option>
                    <option value="pickup">Pickup</option>
                    <option value="trailer">Trailer</option>
                    <option value="motorbike">Motorbike</option>
                    <option value="scooter">Scooter</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Registration Number *</label>
                <input type="text" name="registration_number" required class="w-full px-4 py-2 border rounded-lg" placeholder="Ba 1 Cha 1234">
                @error('registration_number') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Plate Number</label>
                <input type="text" name="plate_number" class="w-full px-4 py-2 border rounded-lg" placeholder="Optional">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Model</label>
                <input type="text" name="model" class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Capacity (kg)</label>
                <input type="number" name="capacity" class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Color</label>
                <input type="text" name="color" class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Manufacturing Year</label>
                <input type="number" name="year" class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Insurance Valid Until</label>
                <input type="date" name="insurance_valid_until" class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Fitness Valid Until</label>
                <input type="date" name="fitness_valid_until" class="w-full px-4 py-2 border rounded-lg">
            </div>
        </div>
        
        <div class="flex justify-end space-x-4 mt-4">
            <a href="{{ route('driver.vehicles.index') }}" class="px-6 py-2 bg-gray-300 rounded-lg">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg">Register Vehicle</button>
        </div>
    </form>
</div>
@endsection