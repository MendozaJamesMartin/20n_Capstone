<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Already logged in, but not verified
        if (Auth::check() && is_null(Auth::user()->email_verified_at)) {
            // Allow OTP page to load
            if ($request->routeIs('otp.verify.form') || $request->routeIs('otp.verify') || $request->routeIs('otp.resend')) {
                return $next($request);
            }

            return redirect()->route('otp.verify.form')
                ->with('error', 'You must verify your email before accessing this page.');
        }

        return $next($request);
    }
}
