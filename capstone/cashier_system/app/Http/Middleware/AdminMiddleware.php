<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('ENTER ADMIN MIDDLEWARE HANDLE=====>');

        $user = $request->session()->get('user');
        $type = $user->user_type;
        if ($type != 'admin') {
            Log::info('Access Attempt by non-admin');
            abort(403, 'You do not have permission to view this page');
        } else {
            Log::info('Accessed by Admin');
            return $next($request);
        }

        Log::info('EXIT ADMIN MIDDLEWARE HANDLE=====>');

        return $next($request);
    }
}
