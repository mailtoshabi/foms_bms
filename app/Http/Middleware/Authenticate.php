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

        if (str_starts_with($path, 'admin')) {
            return route('admin.login');
        }

        if (str_starts_with($path, 'staff') || str_starts_with($path, 'departments')) {
            return route('staff.login');
        }

        if (str_starts_with($path, 'teacher')) {
            return route('teacher.login');
        }

        if (str_starts_with($path, 'student')) {
            return route('student.login');
        }

        return route('admin.login');
    }
}
