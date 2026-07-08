<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckStudentBlocked
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            if ($student->is_blocked) {
                Auth::guard('student')->logout();
                return redirect()->route('student.login')->withErrors([
                    'phone' => 'Your account is blocked. Please contact administration.'
                ]);
            }
        }

        return $next($request);
    }
}
