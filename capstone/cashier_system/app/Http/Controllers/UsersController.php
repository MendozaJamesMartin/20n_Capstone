<?php

namespace App\Http\Controllers;

use App\Models\Credential;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\AuditLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function getUsersList () {
        $users = User::all();
        return view('common.users.users-list', compact('users'));
    }

    public function updateUserRole (Request $request, $user_id) {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'role' => 'required|in:Superadmin,Admin'
            ]);
        
            $user = User::findOrFail($user_id);

            // Prevent demoting the last remaining Superadmin
            if (
                $user->role === 'Superadmin' &&                      // Target is currently a Superadmin
                $validated['role'] !== 'Superadmin' &&               // New role is not Superadmin
                User::where('role', 'Superadmin')->count() <= 1      // Only 1 Superadmin exists
            ) {
                return back()->withErrors(['role' => 'At least one Superadmin must remain in the system.']);
            }

            $user->update([
                'role' => $validated['role'],
            ]);
        
            DB::commit();
            return redirect()->route('users.list')->with('success', 'User updated successfully!');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Transaction update failed: " . $e->getMessage());
            return back()->with('error', 'Failed to update User Role');
        }
    }

    public function showUserProfile () {
        $userProfile = Auth::user();
        return view('common.users.user-profile', compact('userProfile'));
    }

    public function updateProfile (Request $request) {
        Log::info("Update Profile start");

        /** @var \App\Models\User $userProfile */
        $userProfile = Auth::user();

        DB::beginTransaction();
        try {
            Log::info("input new profile validation");

            $validated = $request->validate([
                'first_name'   => 'required|string|max:255',
                'middle_name'  => 'required|string|max:255',
                'last_name'    => 'required|string|max:255',
                'suffix'       => 'nullable|string|max:10',
            ]);

            $userProfile->update($validated);

            Log::info("Saving new user details in database");

            DB::commit();
            return redirect()->route('user.profile')->with('success', 'Profile updated successfully!');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("User profile update failed: " . $e->getMessage());

            return back()->with('error', 'Failed to update profile. Please try again.');
        }
    }

    public function newPassword (Request $request) {

        Log::info("New Password start");

        /** @var \App\Models\User $userProfile */
        $userProfile = Auth::user();

        DB::beginTransaction();
        try {

            $request->validate([
                'password' => ['required', 'string', new StrongPassword, 'confirmed'],
            ]);

            Log::info("Input new password");
            $userProfile->password = Hash::make($request->password); // Hash the password
            $userProfile->save();

            Log::info("Create new Credentials");
            // Save credentials
            $credentials = new Credential();
            $credentials->user_id = $userProfile->id;
            $credentials->is_deleted = 0;
            $credentials->password = $userProfile->password; // Already hashed above
            $credentials->save();
    
            Log::info("Credential created");

            DB::commit();

            return redirect()->route('user.profile')->with('success', 'Password changed successfully');
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Failed to change password");
            return back()->with('error', 'Failed to change password');
        }
    }

}
