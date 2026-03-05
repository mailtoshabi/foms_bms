<?php

// use Illuminate\Support\Facades\Request;


use Illuminate\Support\Facades\Route;

if (!function_exists('set_active')) {
    function set_active($routes)
    {
        $currentRoute = Route::currentRouteName();

        if (is_array($routes)) {
            foreach ($routes as $route) {
                if (Route::is($route)) {
                    return 'mm-active';
                }
            }
        } else {
            if (Route::is($routes)) {
                return 'mm-active';
            }
        }

        return '';
    }
}

use App\Models\Utility;

if (!function_exists('utility')) {
    function utility($key, $default = null)
    {
        static $settings;

        if (!$settings) {
            $settings = Utility::pluck('value','key')->toArray();
        }

        return $settings[$key] ?? $default;
    }
}






