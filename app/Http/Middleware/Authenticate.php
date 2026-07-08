<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        \Log::info('Auth Redirect Check', ['path' => $request->path(), 'expectsJson' => $request->expectsJson()]);
        if ($request->expectsJson()) {
            return null;
        }

        $path = $request->path();
        $referer = $request->headers->get('referer');

        if (str_starts_with($path, 'admin') || ($referer && str_contains($referer, '/admin'))) {
            return route('admin.login');
        }

        if (str_starts_with($path, 'staff') || str_starts_with($path, 'departments') || ($referer && (str_contains($referer, '/staff') || str_contains($referer, '/departments')))) {
            return route('staff.login');
        }

        if (str_starts_with($path, 'teacher') || ($referer && str_contains($referer, '/teacher'))) {
            return route('teacher.login');
        }

        if (str_starts_with($path, 'student') || ($referer && str_contains($referer, '/student'))) {
            return route('student.login');
        }

        return route('admin.login');
    }
}
