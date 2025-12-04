<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserAuthMiddleware;
use App\Mail\ForgotPasswordMail;
use App\Mail\SendOtpCode;
use App\Models\Credential;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\AuditLogger;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function register() {
        return view('login.register');
    }

    public function registerPost(Request $request){
        Log::info("registration start");
    
        DB::beginTransaction();
        try {
            // Validate input
            $request->validate([
                'email' => 'required|email|unique:users,email',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'password' => ['required', 'string', new StrongPassword, 'confirmed'],
            ]);

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

        $user = User::where('email', $request->email)->first();
        $remember = $request->filled('remember');

        // If no user exists → generic error
        if (!$user) {
            return back()->with('error', 'Invalid login credentials.');
        }

        // ---------------------------------------------------
        // 1. Check if account is permanently locked
        // ---------------------------------------------------
        if ($user->locked_at !== null) {
            return redirect()->route('forgot.password.email')
                ->with('error', 'Your account is locked. Please reset your password to regain access.');
        }

        // ---------------------------------------------------
        // 2. Attempt login
        // ---------------------------------------------------
        if (Auth::attempt($request->only('email', 'password'), $remember)) {

            // SUCCESS → clear failed attempts
            Cache::forget('login_failures_' . $user->email);

            // normal login setup
            session()->put('user', $user);
            session()->put('loginId', $user->id);

            // email verification flow
            if (is_null($user->email_verified_at)) {
                return $this->sendVerificationOtp($user, 'Please verify your email via OTP.');
            }

            if ($user->email_verified_at->lt(now()->subMonth(1))) {
                $user->email_verified_at = null;
                $user->save();

                return $this->sendVerificationOtp($user, 'Your verification has expired. A new OTP was sent.');
            }

            // Audit log
            AuditLogger::log(
                event: 'user_login',
                auditableType: 'App\\Models\\User',
                auditableId: $user->id,
                oldValues: [],
                newValues: [
                    'message' => 'User Login',
                    'user' => $user->email,
                    'timestamp' => now()->toDateTimeString(),
                ],
                tags: 'login'
            );

            return redirect()->route('admin.dashboard');
        }

        // ---------------------------------------------------
        // 3. FAILED LOGIN ATTEMPT
        // ---------------------------------------------------

        // Get current failures from cache (default: 0)
        $failures = Cache::get('login_failures_' . $user->email, 0) + 1;

        Cache::put('login_failures_' . $user->email, $failures, now()->addHours(12));

        // If reached 3 incorrect attempts → lock account permanently
        if ($failures >= 3) {

            $user->locked_at = now();
            $user->save();

            // Audit log
            AuditLogger::log(
                event: 'user_locked',
                auditableType: 'App\\Models\\User',
                auditableId: $user->id,
                oldValues: [],
                newValues: [
                    'message' => 'Account locked after multiple failed login attempts',
                    'email' => $user->email,
                    'timestamp' => now()->toDateTimeString(),
                ],
                tags: 'security'
            );

            // Notify user by email
            Mail::to($user->email)->send(new \App\Mail\AccountLockedMail($user));

            return redirect()->route('forgot.password.email')
                ->with('error', 'Your account has been locked because of multiple failed login attempts. Please reset your password to regain access.');
        }

        return back()->with('error', 'Invalid login credentials.');
    }
    
    protected function sendVerificationOtp($user, $message) {
        $otp = rand(100000, 999999);

        session([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_attempts' => 0,
            'otp_user_id' => $user->id,
            'otp_email' => $user->email,
        ]);

        Mail::to($user->email)->send(new SendOtpCode($otp));

        return redirect()->route('otp.verify.form')->withErrors(['otp_code' => $message]);
    }

    public function forgotPassword() {
        session()->forget('otp'); // clears old OTP so we start fresh
        return view('login.forgot-password-email');
    }

    public function forgotPasswordOtp(Request $request) {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No user found with that email.']);
        }

        $otp = rand(100000, 999999);

        session([
            'otp_email' => $request->email,
            'otp_code' => $otp,
            'otp_expires' => now()->addMinutes(5),
        ]);

        Mail::to($user->email)->send(new ForgotPasswordMail($user, $otp));

        return redirect()->route('forgot.password.form')->with('success', 'OTP sent to your email.');
    }

    public function forgotPasswordForm() {
        if (!session()->has('otp_email')) {
            return redirect()->route('forgot.password.email.form');
        }
        return view('login.forgot-password-form');
    }

    public function forgotPasswordPost(Request $request) {
    
        $request->validate([
            'otp' => 'required',
            'new_password' => ['required', 'string', new StrongPassword, 'confirmed'],
        ]);

        if (
            $request->otp != session('otp_code') ||
            now()->greaterThan(session('otp_expires'))
        ) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        $user = User::where('email', session('otp_email'))->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // unlock only if previously locked
        if ($user->locked_at) {
            $user->locked_at = null;

            // Audit log
            AuditLogger::log(
                event: 'user_unlocked',
                auditableType: 'App\\Models\\User',
                auditableId: $user->id,
                oldValues: [],
                newValues: [
                    'message' => 'Account unlocked after password reset',
                    'email' => $user->email,
                    'timestamp' => now()->toDateTimeString(),
                ],
                tags: 'security'
            );
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        session()->forget(['otp_email', 'otp_code', 'otp_expires']);

        return redirect()->route('login')->with('success', 'Password reset successfully.');
    }

    public function resendOtp(Request $request)
    {
        if (!session('otp_sent') || !session('otp_email')) {
            return redirect()->route('forgot.password.email')->with('error', 'Please start the password reset process first.');
        }

        // Optional: prevent spamming (30 seconds cooldown)
        if (session('otp_last_sent') && now()->diffInSeconds(session('otp_last_sent')) < 30) {
            return back()->with('error', 'Please wait before requesting a new OTP.');
        }

        $user = User::where('email', session('otp_email'))->first();
        if (!$user) {
            return redirect()->route('forgot.password.email')->with('error', 'User not found.');
        }

        // Generate new OTP
        $otp = rand(100000, 999999);

        // Update session values
        session([
            'otp_code' => $otp,
            'otp_expires' => now()->addMinutes(10),
            'otp_last_sent' => now(),
        ]);

        // Send new OTP
        Mail::to($user->email)->send(new ForgotPasswordMail($user, $otp));

        return back()->with('success', 'A new OTP has been sent to your email.');
    }

    public function logout(Request $request) {

        $user = Auth::user();

            // Audit log
            AuditLogger::log(
                event: 'user_logout',
                auditableType: 'App\\Models\\User',
                auditableId: $user->id,
                oldValues: [],
                newValues: [                
                        'message' => 'User Logout',
                        'user' => $user->email,
                        'timestamp' => now()->toDateTimeString(),
                    ],
                tags: 'logout'
            );

            $request->session()->flush();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login');

    }

}
