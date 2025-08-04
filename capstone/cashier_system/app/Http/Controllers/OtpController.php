<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function showForm() {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->email_verified_at) {
            return redirect()->route('admin.dashboard');
        }

        return view('login.verify');
    }

    public function verify(Request $request) {
        $request->validate([
            'otp_code' => 'required|digits:6',
        ]);

        if (!session()->has('otp_code') || !session()->has('otp_user_id')) {
            return redirect()->route('login')->withErrors(['otp_code' => 'Session expired.']);
        }

        if (now()->greaterThan(session('otp_expires_at'))) {
            return back()->withErrors(['otp_code' => 'OTP expired.']);
        }

        if (session('otp_attempts') >= 5) {
            return back()->withErrors(['otp_code' => 'Too many failed attempts.']);
        }

        if ($request->otp_code != session('otp_code')) {
            session(['otp_attempts' => session('otp_attempts') + 1]);
            return back()->withErrors(['otp_code' => 'Incorrect OTP.']);
        }

        $user = User::where('email', session('otp_email'))->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['otp_code' => 'User not found.']);
        }

        $user->email_verified_at = now();
        $user->save();

        session()->forget(['otp_email', 'otp_code', 'otp_expires_at', 'otp_attempts']);

        return redirect()->route('admin.dashboard')->with('success', 'Email verified. You may now log in.');
    }

    public function resend() {
        if (!session()->has('otp_user_id')) {
            return redirect()->route('register')->withErrors('Session expired.');
        }

        $otp = rand(100000, 999999);

        session([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_attempts' => 0,
        ]);

        $user = User::find(session('otp_user_id'));

        Mail::to($user->email)->send(new SendOtpCode($otp));

        return back()->with('message', 'A new OTP has been sent to your email.');
    }
}
