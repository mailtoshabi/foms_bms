<?php

namespace App\Http\Middleware;

use Closure;

class DailySalaryFeeRunner
{
    public function handle($request, Closure $next)
    {
        if (auth()->guard('staff')->check()) {

            if (!session()->has('salary_checked')) {

                runDailySalaryFeeProcess();

                session(['salary_checked' => true]);
            }
        }

        return $next($request);
    }
}
