<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('student.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|string|min:7|max:15',
            'password' => 'required'
        ]);

        $credentials = [
            'phone'    => $request->phone,
            'password' => $request->password,
        ];

        if (Auth::guard('student')->attempt($credentials)) {
            return redirect()->intended('/student/dashboard');
        }

        return back()->withErrors([
            'phone' => 'Invalid phone or password.'
        ])->onlyInput('phone');
    }

    public function logout()
    {
        Auth::guard('student')->logout();
        return redirect('/student/login');
    }
}
