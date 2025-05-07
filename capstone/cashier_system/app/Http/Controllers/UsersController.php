<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function getUsersList () {
        $users = User::all();
        return view('common.users.users-list', compact('users'));
    }

    public function updateUserRole (Request $request, $user_id) {
        $validated = $request->validate([
            'role' => 'required|in:Superadmin,Admin'
        ]);
    
        $user = User::findOrFail($user_id);
        $user->update([
            'role' => $validated['role'],
        ]);
    
        return redirect()->route('users.list')->with('success', 'Item updated successfully!');
    }

    public function showUserDetails ($id) {
        
    }

}
