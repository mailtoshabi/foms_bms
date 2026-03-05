<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('role', function ($roles) {

            if (!Auth::guard('staff')->check()) {
                return false;
            }

            $staff = Auth::guard('staff')->user();

            // Operation department sees everything
            if ($staff->hasRole('operation')) {
                return true;
            }

            return $staff->hasRole($roles);
        });
    }
}
