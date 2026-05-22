<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isApproved()) {
            if ($request->routeIs('approval.pending')) {
                return $next($request);
            }

            return redirect()
                ->route('approval.pending')
                ->with('error', 'Your account is waiting for admin approval. Refresh this page after an admin approves your account.');
        }

        return $next($request);
    }
}
