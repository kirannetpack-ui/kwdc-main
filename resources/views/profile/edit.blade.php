@extends('layouts.app')

@section('title', 'My Profile')
@section('header', 'Profile Settings')

@push('styles')
<style>
    .kwdc-glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03);
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Success / Error Alert -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-3 shadow-sm">
        <i class="fas fa-check-circle text-emerald-600 text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        <!-- Profile Header Card with Avatar Upload -->
        <div class="kwdc-glass-card p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-6">
            <div class="relative group">
                <div class="w-24 h-24 rounded-3xl overflow-hidden bg-gradient-to-br from-orange-500 to-amber-600 text-white flex items-center justify-center font-extrabold text-3xl shadow-md border-2 border-white ring-4 ring-orange-500/10" id="avatarPreviewContainer">
                    @if($user->profile_photo && file_exists(public_path($user->profile_photo)))
                        <img src="{{ asset($user->profile_photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover" id="avatarPreviewImg">
                    @else
                        <span id="avatarInitial">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        <img src="" alt="Preview" class="w-full h-full object-cover hidden" id="avatarPreviewImg">
                    @endif
                </div>
                
                <label for="profile_photo_input" class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-slate-900 hover:bg-orange-500 text-white flex items-center justify-center text-xs shadow-lg cursor-pointer transition transform hover:scale-110" title="Upload Photo">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" id="profile_photo_input" name="profile_photo" accept="image/*" class="hidden">
            </div>

            <div class="text-center sm:text-left space-y-1.5 flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ $user->name }}</h1>
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200 w-max mx-auto sm:mx-0">
                        {{ ucfirst(str_replace('_', ' ', $user->role ?? 'User')) }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 font-medium">{{ $user->email }}</p>
                <p class="text-[11px] text-slate-500">Click the camera icon to upload a JPG, PNG, or WebP profile image (max 3MB).</p>
                @error('profile_photo') <p class="text-rose-500 text-xs font-semibold">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Personal Information Card -->
        <div class="kwdc-glass-card p-6 sm:p-8 space-y-6">
            <div class="pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fas fa-user-gear text-orange-500 text-sm"></i>
                <h2 class="font-extrabold text-slate-900 text-sm">Personal Details</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition">
                    @error('name') <p class="text-rose-500 text-[11px] font-semibold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition">
                    @error('email') <p class="text-rose-500 text-[11px] font-semibold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+977 98XXXXXXXX" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition">
                    @error('phone') <p class="text-rose-500 text-[11px] font-semibold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Role (System Assigned)</label>
                    <input type="text" value="{{ ucfirst(str_replace('_', ' ', $user->role ?? 'User')) }}" disabled class="w-full px-3.5 py-2.5 rounded-xl border border-slate-100 text-xs text-slate-400 bg-slate-50 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Address</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="Street address or ward" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">City</label>
                    <input type="text" name="city" value="{{ old('city', $user->city ?? 'Kathmandu') }}" placeholder="Kathmandu, Lalitpur, Pokhara..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition">
                </div>
            </div>
        </div>

        <!-- Security / Password Card -->
        <div class="kwdc-glass-card p-6 sm:p-8 space-y-6">
            <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-shield-alt text-orange-500 text-sm"></i>
                    <h2 class="font-extrabold text-slate-900 text-sm">Password & Security</h2>
                </div>
                <span class="text-[11px] text-slate-400 font-medium">Leave blank to keep current password</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">New Password</label>
                    <input type="password" name="password" placeholder="Minimum 8 characters" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition">
                    @error('password') <p class="text-rose-500 text-[11px] font-semibold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation" placeholder="Re-type new password" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-xs font-bold text-slate-600 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-sm transition flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span>Save Profile Changes</span>
            </button>
        </div>
    </form>

    <!-- Two-Factor Authentication (2FA) Security Card -->
    <div class="kwdc-glass-card p-6 sm:p-8 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-orange-50 border border-orange-200 text-orange-600 flex items-center justify-center text-sm shadow-xs">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 tracking-tight">Two-Factor Authentication (2FA)</h2>
                    <p class="text-[11px] text-slate-500">Require an instant Nepal SMS & OTP verification code for login and high-value cargo clearances.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('profile.two-factor.toggle') }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $user->two_factor_enabled ? 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' }}">
                    @if($user->two_factor_enabled)
                        <i class="fas fa-toggle-on text-emerald-600 mr-1"></i> Enabled (Disable)
                    @else
                        <i class="fas fa-toggle-off text-slate-400 mr-1"></i> Disabled (Enable 2FA)
                    @endif
                </button>
            </form>
        </div>
        @if($user->two_factor_enabled)
        <div class="p-3 bg-emerald-50/70 border border-emerald-200/80 rounded-xl text-[11px] text-emerald-800 flex items-center gap-2">
            <i class="fas fa-lock text-emerald-600"></i>
            <span>Your account is actively protected. 6-digit OTP codes are delivered to {{ $user->phone ?: $user->email }}.</span>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('profile_photo_input');
    const img = document.getElementById('avatarPreviewImg');
    const initial = document.getElementById('avatarInitial');

    if (input && img) {
        input.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    if (initial) initial.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endsection
