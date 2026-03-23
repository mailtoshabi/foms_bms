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

if (!function_exists('studentWhatsappMessage')) {
    function studentWhatsappMessage($student, $password)
    {
        $message = "Hello {$student->name},\n\n".
                "Your admission to FOMS Academy is successful.\n\n".
                "Admission No: {$student->admission_no}\n".
                "User Name: {$student->phone}\n".
                "Password: {$password}\n\n".
                "Login: ".route('student.login');

        $phone = '91'.$student->phone;

        return "https://wa.me/".$phone."?text=".urlencode($message);
    }
}

if (!function_exists('teacherWhatsappMessage')) {
    function teacherWhatsappMessage($teacher, $password)
    {
        $message = "Hello {$teacher->name},\n\n".
                "Your admission to FOMS Academy is successful.\n\n".
                "Unique No: {$teacher->admission_no}\n".
                "User Name: {$teacher->phone}\n".
                "Password: {$password}\n\n".
                "Login: ".route('teacher.login');

        $phone = '91'.$teacher  ->phone;

        return "https://wa.me/".$phone."?text=".urlencode($message);
    }
}






