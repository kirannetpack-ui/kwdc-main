@extends('layouts.app')
@section('title', 'Equipment request #'.$request->id)
@section('header', 'Equipment request')
@section('content')
@php
    $assigned = $request->assignedEquipment ?: $request->equipment;
    $canManage = auth()->user()->isAdmin() || $assigned?->owner_id === auth()->id();
@endphp
<a class="inline-flex items-center gap-2 text-sm mb-6 font-semibold text-gray-600 hover:text-orange-600" href="{{ route('equipment-requests.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Equipment requests</a>

<div class="grid gap-6">
    <section class="rounded-3xl bg-white border border-gray-200 p-6 md:p-8 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase font-bold text-orange-600 mb-2">Request #{{ $request->id }}</p>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $request->equipment_type }}</h1>
            </div>
            <span class="inline-flex items-center rounded-full bg-gray-100 px-4 py-2 text-sm font-bold text-gray-700">{{ ucfirst($request->status) }}</span>
        </div>
        <dl class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mt-8">
            <div class="rounded-2xl bg-gray-50 p-4"><dt class="text-xs uppercase font-bold text-gray-500 mb-2">Location</dt><dd class="font-semibold text-gray-900">{{ $request->location }}</dd></div>
            <div class="rounded-2xl bg-gray-50 p-4"><dt class="text-xs uppercase font-bold text-gray-500 mb-2">Dates</dt><dd class="font-semibold text-gray-900">{{ $request->start_date?->format('M j, Y') }} - {{ $request->end_date?->format('M j, Y') }}</dd></div>
            <div class="rounded-2xl bg-gray-50 p-4"><dt class="text-xs uppercase font-bold text-gray-500 mb-2">Client</dt><dd class="font-semibold text-gray-900">{{ $request->client?->name }}</dd></div>
            <div class="rounded-2xl bg-gray-50 p-4"><dt class="text-xs uppercase font-bold text-gray-500 mb-2">Equipment</dt><dd class="font-semibold text-gray-900">{{ $assigned?->display_name ?? 'Awaiting assignment' }}</dd></div>
        </dl>
    </section>

    <section class="rounded-3xl bg-white border border-gray-200 p-6 md:p-8 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-3">Requirements</h2>
        <p class="whitespace-pre-line text-gray-700">{{ $request->description ?: 'No additional requirements.' }}</p>
        @if($request->notes)<p class="whitespace-pre-line mt-4 text-gray-600">{{ $request->notes }}</p>@endif
    </section>
</div>

@if($request->client_id === auth()->id() && $request->status === 'pending')
<form class="mt-6" method="POST" action="{{ route('equipment-requests.destroy', $request) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">Cancel request</button></form>
@endif
@if($canManage)
<section class="mt-6 rounded-3xl bg-white border border-gray-200 p-6 md:p-8 shadow-sm">
    <h2 class="text-lg font-bold text-gray-900 mb-5">Operations</h2>
    <div class="flex flex-wrap gap-3">
        @if($request->status === 'pending')
            <form class="grid gap-3 w-full md:max-w-xl" method="POST" action="{{ route('equipment-requests.approve', $request) }}">
                @csrf
                <label class="font-semibold text-gray-700" for="assigned_equipment_id">Assign equipment</label>
                <select id="assigned_equipment_id" name="assigned_equipment_id" class="form-control" required>
                    <option value="">Choose available equipment</option>
                    @foreach($availableEquipment ?? [] as $equipment)
                        <option value="{{ $equipment->id }}" @selected(($assigned?->id ?? null) === $equipment->id)>
                            {{ $equipment->display_name }} - {{ $equipment->location ?? 'No location' }}
                        </option>
                    @endforeach
                </select>
                @error('assigned_equipment_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn btn-primary">Approve</button>
                    <button type="submit" formaction="{{ route('equipment-requests.reject', $request) }}" formnovalidate class="btn btn-outline-danger">Reject</button>
                </div>
            </form>
        @elseif($request->status === 'approved')
            <form method="POST" action="{{ route('equipment-requests.fulfill', $request) }}">@csrf<button type="submit" class="btn btn-primary">Mark fulfilled</button></form>
        @elseif($request->status === 'fulfilled')
            <form method="POST" action="{{ route('equipment-requests.return', $request) }}">@csrf<button type="submit" class="btn btn-primary">Mark returned</button></form>
        @endif
    </div>
</section>
@endif
@endsection
