<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards)
    {
        if (! $request->expectsJson()) {

            if (in_array('admin', $guards)) {
                redirect()->route('admin.login')->send();
                exit;
            }

            if (in_array('staff', $guards)) {
                redirect()->route('staff.login')->send();
                exit;
            }

            if (in_array('teacher', $guards)) {
                redirect()->route('teacher.login')->send();
                exit;
            }

            if (in_array('student', $guards)) {
                redirect()->route('student.login')->send();
                exit;
            }

            redirect()->route('admin.login')->send();
            exit;
        }

        throw new AuthenticationException('Unauthenticated.', $guards);
    }
}
