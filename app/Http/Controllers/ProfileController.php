<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserContact;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        if ($user && $user->role === 'security_agency') {
            return redirect()->route('security.profile');
        }

        $contacts = UserContact::where('user_id', $user->id)->get();
        return view('profile.edit', compact('user', 'contacts'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $emailChanged = $request->email !== $user->email;

        $user->fill($request->only([
            'name',
            'email',
            'phone',
            'address',
            'city',
            'state',
            'country',
            'postal_code',
        ]));

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/avatars');
            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            if ($user->profile_photo && file_exists(public_path($user->profile_photo))) {
                @unlink(public_path($user->profile_photo));
            }
            $file->move($dest, $filename);
            $user->profile_photo = 'uploads/avatars/' . $filename;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Profile and preferences updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Password updated successfully!');
    }

    public function contacts()
    {
        $user = Auth::user();
        $contacts = UserContact::where('user_id', $user->id)->get();
        return view('profile.contacts', compact('contacts'));
    }

    public function addContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'relationship' => 'nullable|string|max:50',
        ]);

        UserContact::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'relationship' => $request->relationship,
        ]);

        return redirect()->route('profile.contacts')
            ->with('success', 'Contact added successfully!');
    }

    public function destroyContact($id)
    {
        $contact = UserContact::where('user_id', Auth::id())->findOrFail($id);
        $contact->delete();

        return redirect()->route('profile.contacts')
            ->with('success', 'Contact deleted successfully!');
    }

    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
