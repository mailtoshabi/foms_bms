<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassNote;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Models\TeacherSalary;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{


public function dashboard()
{
    $teacher = Auth::guard('teacher')->user();

    // Assigned Classes
    $classes = $teacher->classRooms()->with(['course','classType'])->get();

    // Attendance
    $totalClasses = $teacher->attendances()->count();
    $present = $teacher->attendances()->where('is_present',1)->count();

    $attendancePercent = $totalClasses
        ? round(($present/$totalClasses)*100,2)
        : 0;

    // Salary history
    $salaries = TeacherSalary::where('teacher_id',$teacher->id)
        ->latest()
        ->take(5)
        ->get();

    // Latest class notes
    $notes = ClassNote::where('teacher_id',$teacher->id)
        ->latest()
        ->take(5)
        ->get();

    return view('teacher.dashboard',compact(
        'teacher',
        'classes',
        'attendancePercent',
        'salaries',
        'notes'
    ));
}
}
