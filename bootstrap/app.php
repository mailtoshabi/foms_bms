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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'last.login' => \App\Http\Middleware\LastLoginTracker::class,
            'role' => \App\Http\Middleware\RolePermission::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
        $middleware->append(\App\Http\Middleware\LastLoginTracker::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
