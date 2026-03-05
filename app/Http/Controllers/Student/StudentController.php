<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function create()
    {
        $sources = \App\Models\Source::where('is_active', true)->get();

        return view('staff.student_leads.create', compact('sources'));
    }
}
