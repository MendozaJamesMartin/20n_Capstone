<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserAuthMiddleware;
use App\Mail\ForgotPasswordMail;
use App\Mail\SendOtpCode;
use App\Models\Credential;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->last_name = $request->last_name;
            $user->suffix = $request->suffix;
            $user->password = Hash::make($request->password); // Hash the password
            $user->role = 'Admin';
            $user->save();
    
            Log::info("User created with ID: " . $user->id);
    
            // Save credentials
            $credentials = new Credential();
            $credentials->user_id = $user->id;
            $credentials->is_deleted = 0;
            $credentials->password = $user->password; // Already hashed above
            $credentials->save();
    
            Log::info("Credential created");
    
            DB::commit();

            return redirect()->route('users.list');
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

    public function loginPost(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:4',
        ]);

        $remember = $request->filled('remember');
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            session()->put('user', $user);
            session()->put('loginId', $user->id);

            if (is_null($user->email_verified_at)) {
                // Generate OTP and send email
                $otp = rand(100000, 999999);

                session([
                    'otp_code' => $otp,
                    'otp_expires_at' => now()->addMinutes(10),
                    'otp_attempts' => 0,
                    'otp_user_id' => $user->id,
                    'otp_email' => $user->email,
                ]);

                Mail::to($user->email)->send(new SendOtpCode($otp));

                return redirect()->route('otp.verify.form');
            }

        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login')->with('error', 'Invalid Credentials');
    }
    
    public function forgotPassword() {
        return view('login.forgot-password');
    }

    public function forgotPasswordPost(Request $request) {
        Log::info("Forgot Password start");
    
        DB::beginTransaction();
        try {

            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return back()->with('error', 'No user found with that email.');
            }
        
            Log::info("User found with ID: " . $user->id);

            // Generate a new random password
            $newPassword = Str::random(10);

            // Update user's password
            $user->password = Hash::make($newPassword);
            $user->save();
    
            // Save credentials
            $credentials = new Credential();
            $credentials->user_id = $user->id;
            $credentials->is_deleted = 0;
            $credentials->password = $user->password; // Already hashed above
            $credentials->save();
    
            Log::info("Credential for new password created");
    
            DB::commit();

            // Send the email
            Mail::to($user->email)->send(new ForgotPasswordMail($user, $newPassword));

            return redirect()->route('login')->with('success', 'Reset password due to forget, successful!');
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Password reset error: " . $e->getMessage());
    
            $message = "ERROR";
            if ($e->errorInfo[1] == 1062) {
                $message = "Email or Student ID already exists";
            }
    
            return back()->with('error', $message);
        }
    }

    public function logout(Request $request) {

            $request->session()->flush();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');

    }
}
