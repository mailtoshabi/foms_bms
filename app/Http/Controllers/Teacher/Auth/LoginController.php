<?php

namespace App\Http\Controllers\Teacher\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('teacher.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|digits:10',
            'password' => 'required'
        ]);

        $credentials = [
            'phone'    => $request->phone,
            'password' => $request->password,
        ];

        if (Auth::guard('teacher')->attempt($credentials)) {
            return redirect()->intended('/teacher/dashboard');
        }

        return back()->withErrors([
            'phone' => 'Invalid phone or password.'
        ])->onlyInput('phone');
    }

    public function logout()
    {
        Auth::guard('teacher')->logout();
        return redirect('/teacher/login');
    }
}
