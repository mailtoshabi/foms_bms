<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Models\ClassHour;

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
        // Force HTTPS on production so PWA / service worker works correctly
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Student right-sidebar: pass upcoming class hours
        View::composer(['student.layouts.right-sidebar', 'student.layouts.topbar', 'student.layouts.horizontal'], function ($view) {
            $classHours = collect();
            if (Auth::guard('student')->check()) {
                $student = Auth::guard('student')->user();
                $classRoomIds = $student->class_rooms()->pluck('class_rooms.id');
                $classHours = ClassHour::whereIn('class_room_id', $classRoomIds)
                    ->where('status', 'pending')
                    ->with('classRoom')
                    ->latest()
                    ->take(15)
                    ->get();
            }
            $view->with('classHours', $classHours);
            $view->with('pendingClassHoursCount', $classHours->count());
        });

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
