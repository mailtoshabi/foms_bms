<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'last.login' => \App\Http\Middleware\LastLoginTracker::class,
            'role' => \App\Http\Middleware\RolePermission::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\LastLoginTracker::class,
            'throttle:global',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, $exception, $request) {
            if ($response->getStatusCode() === 429) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Too many requests. Please try again later.'
                    ], 429);
                }
                return back()->with('error', 'Too many requests. Please wait a moment before trying again.');
            }

            if ($response->getStatusCode() === 419) {
                $path = request()->path();
                $referer = request()->headers->get('referer');
                $loginRoute = 'admin.login'; // Default
    
                if (str_starts_with($path, 'admin') || ($referer && str_contains($referer, '/admin'))) {
                    $loginRoute = 'admin.login';
                } elseif (str_starts_with($path, 'departments') || str_starts_with($path, 'staff') || ($referer && (str_contains($referer, '/departments') || str_contains($referer, '/staff')))) {
                    $loginRoute = 'staff.login';
                } elseif (str_starts_with($path, 'teacher') || ($referer && str_contains($referer, '/teacher'))) {
                    $loginRoute = 'teacher.login';
                } elseif (str_starts_with($path, 'student') || ($referer && str_contains($referer, '/student'))) {
                    $loginRoute = 'student.login';
                }

                return redirect()->route($loginRoute)->with('error', 'Your session has expired. Please log in again.');
            }
            return $response;
        });
    })->create();
