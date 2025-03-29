<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserAuthMiddleware;
use App\Models\Admin;
use App\Models\Credential;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function register() {
        return view('login.register');
    }

    public function registerPost(Request $request){
        Log::info("registration start");
    
        DB::beginTransaction();
        try {
            // Create and save User first
            $user = new User();
            $user->email = $request->email;
            $user->password = Hash::make($request->password); // Hash the password
            $user->user_type = 'Student';
            $user->save();
    
            Log::info("User created with ID: " . $user->id);
    
            // Create and save Student AFTER user is saved
            $student = new Student();
            $student->user_id = $user->id; // Now user_id exists
            $student->student_id = $request->student_id;
            $student->first_name = $request->first_name;
            $student->middle_name = $request->middle_name;
            $student->last_name = $request->last_name;
            $student->suffix = $request->suffix;
            $student->save();
    
            Log::info("Student created with ID: " . $student->id);
    
            // Save credentials
            $credentials = new Credential();
            $credentials->user_id = $user->id;
            $credentials->is_deleted = 0;
            $credentials->password = $user->password; // Already hashed above
            $credentials->save();
    
            Log::info("Credential created");
    
            DB::commit();
            return back()->with('success', 'Register successful!');
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Registration error: " . $e->getMessage());
    
            $message = "ERROR";
            if ($e->errorInfo[1] == 1062) {
                $message = "Email or Student ID already exists";
            }
    
            return back()->with('error', $message);
        }
    }

    public function registerAdmin() {
        return view('login.register-admin');
    }

    public function registerPostAdmin(Request $request){
        Log::info("registration start");
    
        DB::beginTransaction();
        try {
            // Create and save User first
            $user = new User();
            $user->email = $request->email;
            $user->password = Hash::make($request->password); // Hash the password
            $user->user_type = 'Admin';
            $user->save();
    
            Log::info("User created with ID: " . $user->id);
    
            // Create and save Student AFTER user is saved
            $admin = new Admin();
            $admin->user_id = $user->id; // Now user_id exists
            $admin->name = $request->name;
            $admin->role = $request->role;
            $admin->save();
    
            Log::info("Student created with ID: " . $admin->id);
    
            // Save credentials
            $credentials = new Credential();
            $credentials->user_id = $user->id;
            $credentials->is_deleted = 0;
            $credentials->password = $user->password; // Already hashed above
            $credentials->save();
    
            Log::info("Credential created");
    
            DB::commit();
            return back()->with('success', 'Register successful!');
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Registration error: " . $e->getMessage());
    
            $message = "ERROR";
            if ($e->errorInfo[1] == 1062) {
                $message = "Email or Student ID already exists";
            }
    
            return back()->with('error', $message);
        }
    }

    public function login() {
        return view('login.login');
    }

    public function loginAdmin() {
        return view('login.login-admin');
    }

    public function loginPostAdmin(Request $request) {
        Log::info('loginPost');

        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string|min:4',
        ]);

        $middleware = new AdminMiddleware();

        $response = $middleware->handle($request, function($request) {
            Log::info("Nakapasok");
            return redirect()->route('admin.home');
        });

        Log::info("loginPost EXIT");

        return $response;
    }

    public function loginPost(Request $request) {
        Log::info('loginPost');

        $request->validate([
            'email' => 'required|string',
            'student_id' => 'required|string',
            'password' => 'required|string|min:4',
        ]);

        $middleware = new UserAuthMiddleware();

        $response = $middleware->handle($request, function($request) {
            Log::info("Nakapasok");
            return redirect()->route('student.home');
        });

        Log::info("loginPost EXIT");

        return $response;
    }
    
    public function logout(Request $request) {

            $request->session()->flush();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');

    }
}
