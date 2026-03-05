<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show Login Form
    |--------------------------------------------------------------------------
    */
    public function showLoginForm()
    {
        // Prevent logged-in staff from accessing login page again
        if (Auth::guard('staff')->check()) {
            return redirect()->route('staff.dashboard');
        }

        return view('staff.auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Login
    |--------------------------------------------------------------------------
    */
    public function login(Request $request)
    {
        $request->validate([
            'phone'    => ['required', 'digits:10'],
            'password' => ['required'],
        ]);

        $credentials = $request->only('phone', 'password');

        if (Auth::guard('staff')->attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate(); // Prevent session fixation

            return redirect()->intended(route('staff.dashboard'));
        }

        return back()
            ->withErrors([
                'phone' => 'Invalid phone number or password.',
            ])
            ->onlyInput('phone');
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */
    public function logout(Request $request)
    {
        Auth::guard('staff')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}
