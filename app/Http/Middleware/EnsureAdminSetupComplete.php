<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSetupComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->needsAdminOnboarding() && !$request->routeIs('admin.setup.*')) {
            return redirect()
                ->route('admin.setup.show')
                ->with('error', 'Complete your admin setup first by choosing your managed project name and users.');
        }

        return $next($request);
    }
}
