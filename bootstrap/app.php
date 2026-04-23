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
        $exceptions->respond(function ($view, $request) {
            if ($view instanceof \Illuminate\Http\Response && $view->getStatusCode() === 419) {
                $path = $request->path();
                $loginRoute = 'admin.login'; // Default

                if (str_starts_with($path, 'admin')) {
                    $loginRoute = 'admin.login';
                } elseif (str_starts_with($path, 'departments')) {
                    $loginRoute = 'staff.login';
                } elseif (str_starts_with($path, 'teacher')) {
                    $loginRoute = 'teacher.login';
                } elseif (str_starts_with($path, 'student')) {
                    $loginRoute = 'student.login';
                }

                return redirect()->route($loginRoute)->with('error', 'Your session has expired. Please log in again.');
            }
            return $view;
        });
    })->create();
