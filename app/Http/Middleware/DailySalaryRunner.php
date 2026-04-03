<?php

namespace App\Http\Middleware;

use Closure;

class DailySalaryRunner
{
    public function handle($request, Closure $next)
    {
        if (auth()->guard('staff')->check()) {

            if (!session()->has('salary_checked')) {

                runDailySalaryProcess();

                session(['salary_checked' => true]);
            }
        }

        return $next($request);
    }
}
