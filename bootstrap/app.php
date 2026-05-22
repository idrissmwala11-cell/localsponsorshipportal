<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
   ->withMiddleware(function ($middleware) {
   $middleware->alias([
        'otp' => \App\Http\Middleware\EnsureOtpVerified::class,
        'approved' => \App\Http\Middleware\EnsureAccountApproved::class,
        'admin' => \App\Http\Middleware\EnsureAdmin::class,
        'official_admin' => \App\Http\Middleware\EnsureOfficialAdmin::class,
        'admin_setup' => \App\Http\Middleware\EnsureAdminSetupComplete::class,
    ]);
})
    
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
