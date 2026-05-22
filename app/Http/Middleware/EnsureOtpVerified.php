<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !session('otp_verified')) {
            return redirect()->route('otp.verify');
        }

        return $next($request);
    }
}