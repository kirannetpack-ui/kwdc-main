<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id')->get();
        return view('admin.roles.index', compact('users'));
    }

    public function toggle(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $role = $request->role;
        $allowed = ['is_driver', 'is_equipment_owner', 'is_property_owner', 'is_client'];

        if (in_array($role, $allowed)) {
            $user->$role = !$user->$role;
            $user->save();
            return redirect()->route('admin.roles.index')->with('success', "Role updated for {$user->name}");
        }
        return redirect()->route('admin.roles.index')->with('error', 'Invalid role');
    }
// Show edit form
public function edit($id)
{
    $user = User::findOrFail($id);
    return view('admin.roles.edit', compact('user'));
}

// Update user (name, email)
public function update(Request $request, $id)
{
    $user = User::findOrFail($id);
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $user->id,
    ]);
    $user->update($request->only('name', 'email'));
    return redirect()->route('admin.roles.index')->with('success', 'User updated.');
}

// Delete user
public function destroy($id)
{
    $user = User::findOrFail($id);
    // Prevent admin from deleting themselves
    if ($user->id == auth()->id()) {
        return redirect()->route('admin.roles.index')->with('error', 'You cannot delete your own account.');
    }
    $user->delete();
    return redirect()->route('admin.roles.index')->with('success', 'User deleted.');
}

public function ban(Request $request, $id)
{
    $user = User::findOrFail($id);

    if ($user->id === auth()->id()) {
        return redirect()->route('admin.roles.index')->with('error', 'You cannot ban your own account.');
    }

    $request->validate([
        'ban_reason' => 'required|string|max:500',
    ]);

    $user->ban($request->input('ban_reason'));

    return redirect()->route('admin.roles.index')->with('success', "Account for {$user->name} ({$user->email}) has been banned.");
}

public function unban($id)
{
    $user = User::findOrFail($id);
    $user->unban();

    return redirect()->route('admin.roles.index')->with('success', "Account for {$user->name} has been restored and unbanned.");
}
}