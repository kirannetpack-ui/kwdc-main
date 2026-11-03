<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Profile</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(session('success'))
                    <div class="bg-green-100 text-green-700 p-3 mb-4">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="bg-red-100 text-red-700 p-3 mb-4">{{ implode(', ', $errors->all()) }}</div>
                @endif

                <!-- Profile Photo -->
                <div class="flex justify-center mb-6">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/'.$user->profile_photo) }}" class="w-32 h-32 rounded-full object-cover border">
                    @else
                        <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-user text-4xl text-gray-500"></i>
                        </div>
                    @endif
                </div>

                <!-- Update Profile Form -->
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mb-6 border-b pb-6">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-medium">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium">Mobile Number</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium">Profile Photo</label>
                        <input type="file" name="photo" accept="image/*" class="w-full">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Profile</button>
                </form>

                <!-- Change Password Form -->
                <form method="POST" action="{{ route('profile.change-password') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-medium">Current Password</label>
                        <input type="password" name="current_password" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium">New Password</label>
                        <input type="password" name="new_password" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" required class="w-full border-gray-300 rounded">
                    </div>
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>