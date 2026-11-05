@extends('layouts.app')
@section('title', 'Business Profile & KYC')
@section('header', 'Business Profile & KYC')

@section('content')
@php
    $sections = [
        'Company and contact' => [
            'name' => ['Business name', 'text', true],
            'email' => ['Business contact email', 'email', true],
            'phone' => ['Support phone', 'tel', false],
            'address' => ['Business address', 'text', true],
        ],
        'Registration and tax' => [
            'registration_number' => ['Company registration number', 'text', false],
            'pan' => ['PAN / VAT number', 'text', false],
        ],
        'Business bank transfer' => [
            'bank_name' => ['Bank name', 'text', false],
            'account_name' => ['Account holder', 'text', false],
            'account_number' => ['Account number', 'text', false],
            'branch' => ['Bank branch', 'text', false],
        ],
        'Bank-issued payment QR' => [
            'merchant_name' => ['Merchant name', 'text', false],
            'qr_url' => ['HTTPS link to the bank-issued QR image', 'url', false],
        ],
    ];
    $status = old('registration_status', $details['registration_status'] ?? 'pending');
@endphp

<div class="mx-auto max-w-5xl space-y-5">
    <header class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Business Profile &amp; KYC</h1>
            <p class="text-sm text-slate-600">Manage the company information used on invoices and the bank details shown to customers when enabled.</p>
        </div>
        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-800">
            {{ $status === 'registered' ? 'Registered' : 'Registration pending' }}
        </span>
    </header>

    @if(session('success'))
        <p role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-emerald-800">{{ session('success') }}</p>
    @endif
    @if(config('kwdc.demo'))
        <p class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-amber-800">Demo preview. Save business and bank details in the production workspace.</p>
    @endif
    @if($errors->any())
        <p role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-rose-800">Check the highlighted fields and try again.</p>
    @endif

    <form method="POST" action="{{ route('admin.business-profile.update') }}" class="space-y-4" autocomplete="off">
        @csrf
        @method('PUT')
        <fieldset @disabled(config('kwdc.demo')) class="space-y-4">
            @foreach($sections as $title => $fields)
                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="mb-4 text-base font-bold text-slate-900">{{ $title }}</h2>
                    @if($title === 'Registration and tax')
                        <label for="registration_status" class="mb-4 block text-sm font-semibold">Registration status
                            <select id="registration_status" name="registration_status" class="form-select mt-1">
                                <option value="pending" @selected($status === 'pending')>Pending registration</option>
                                <option value="registered" @selected($status === 'registered')>Registered</option>
                            </select>
                            @error('registration_status')<span class="text-rose-700">{{ $message }}</span>@enderror
                        </label>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($fields as $field => [$label, $type, $required])
                            <label for="{{ $field }}" class="block text-sm font-semibold text-slate-800">
                                {{ $label }}
                                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" class="form-control mt-1 @error($field) is-invalid @enderror"
                                    value="{{ old($field, $details[$field] ?? '') }}" @required($required)
                                    @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                                @error($field)<span id="{{ $field }}-error" class="text-rose-700">{{ $message }}</span>@enderror
                            </label>
                        @endforeach
                    </div>

                    @if(in_array($title, ['Business bank transfer', 'Bank-issued payment QR'], true))
                        @php($toggle = $title === 'Business bank transfer' ? 'bank_enabled' : 'qr_enabled')
                        <input type="hidden" name="{{ $toggle }}" value="0">
                        <label class="mt-4 flex items-center gap-2 text-sm font-semibold">
                            <input type="checkbox" name="{{ $toggle }}" value="1" @checked(old($toggle, $details[$toggle] ?? false))>
                            {{ $toggle === 'bank_enabled' ? 'Show bank transfer to customers' : 'Show payment QR to customers' }}
                        </label>
                    @endif
                </section>
            @endforeach

            <button type="submit" class="btn btn-primary">Save business profile</button>
        </fieldset>
    </form>
</div>
@endsection
