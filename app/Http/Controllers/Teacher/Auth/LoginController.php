<?php

namespace App\Http\Controllers\Teacher\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();
        return view('teacher.auth.login', compact('countries'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'phone'      => 'required|string|min:7|max:15',
            'password'   => 'required'
        ]);
    
        $credentials = [
            'country_id' => $request->country_id,
            'phone'      => $request->phone,
            'password'   => $request->password,
        ];
    
        if (Auth::guard('teacher')->attempt($credentials)) {
            return redirect()->intended('/teacher/dashboard');
        }
    
        return back()->withErrors([
            'phone' => 'Invalid country, phone, or password.'
        ])->onlyInput('phone', 'country_id');
    }

    public function logout()
    {
        Auth::guard('teacher')->logout();
        return redirect('/teacher/login');
    }
}
