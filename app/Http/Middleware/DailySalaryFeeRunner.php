<?php

namespace App\Http\Middleware;

use Closure;

class DailySalaryFeeRunner
{
    public function handle($request, Closure $next)
    {
        if (auth()->guard('staff')->check()) {
            $staff = auth()->guard('staff')->user();
            if ($staff->is_blocked) {
                auth()->guard('staff')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('staff.login')->withErrors([
                    'phone' => 'Your account is blocked. Please contact administration.'
                ]);
            }

            if (!session()->has('salary_checked')) {

                runDailySalaryFeeProcess();

                session(['salary_checked' => true]);
            }
        }

        return $next($request);
    }
}
