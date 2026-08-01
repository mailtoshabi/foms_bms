<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckTeacherBlocked
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('teacher')->check()) {
            $teacher = Auth::guard('teacher')->user();
            if ($teacher->is_blocked) {
                Auth::guard('teacher')->logout();
                return redirect()->route('teacher.login')->withErrors([
                    'phone' => 'Your account is blocked. Please contact administration.'
                ]);
            }
        }

        return $next($request);
    }
}
