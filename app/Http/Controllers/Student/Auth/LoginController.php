<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();
        return view('student.auth.login', compact('countries'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'phone' => 'required|string|min:7|max:15',
            'password' => 'required'
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone);

        $students = \App\Models\Student::where('country_id', $request->country_id)
            ->where('phone', $phone)
            ->get();

        foreach ($students as $student) {
            if (\Illuminate\Support\Facades\Hash::check($request->password, $student->password)) {
                if ($student->is_blocked) {
                    return back()->withErrors([
                        'phone' => 'Your account is blocked. Please contact administration.'
                    ])->onlyInput('phone', 'country_id');
                }

                Auth::guard('student')->login($student, true);
                return redirect()->intended('/student/dashboard');
            }
        }

        return back()->withErrors([
            'phone' => 'Invalid country, phone, or password.'
        ])->onlyInput('phone', 'country_id');
    }

    public function switchAccount($encryptedId)
    {
        $currentStudent = Auth::guard('student')->user();
        if (!$currentStudent) {
            return redirect()->route('student.login');
        }

        try {
            $targetStudentId = decrypt($encryptedId);
        } catch (\Exception $e) {
            abort(403, 'Invalid switch request.');
        }

        $isRelated = $currentStudent->relatedStudents()->where('related_student_id', $targetStudentId)->exists();
        if (!$isRelated) {
            abort(403, 'Unauthorized switch request.');
        }

        $targetStudent = \App\Models\Student::findOrFail($targetStudentId);
        if ($targetStudent->is_blocked) {
            return back()->with('error', 'Cannot switch to this account because it is blocked.');
        }

        Auth::guard('student')->login($targetStudent, true);

        return redirect()->route('student.dashboard')->with('success', "Switched to {$targetStudent->name}'s account.");
    }

    public function logout()
    {
        Auth::guard('student')->logout();
        return redirect('/student/login');
    }
}
