<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LastLoginTracker
{
    public function handle(Request $request, Closure $next)
    {
        // Skip assets / ajax polling / health check
        if ($request->is('up') || $request->is('storage/*')) {
            return $next($request);
        }

        $guards = ['admin', 'staff', 'teacher', 'student'];

        foreach ($guards as $guard) {

            if (Auth::guard($guard)->check()) {

                $user = Auth::guard($guard)->user();

                // Reduce DB writes
                if (method_exists($user, 'getConnection') && \Illuminate\Support\Facades\Schema::hasColumn($user->getTable(), 'last_login_at')) {
                    if (!$user->last_login_at || now()->diffInMinutes($user->last_login_at) >= 5) {

                        $user->last_login_at = now();
                        $user->last_login_ip = $request->ip();
                        $user->save();
                    }
                }

                break;
            }
        }

        return $next($request);
    }
}
