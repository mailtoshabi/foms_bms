<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassNote;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
{

$id = auth()->guard('student')->id();
$student = Student::with([
        'class_rooms.course',
        'class_rooms.classType',
        'fees',
        'attendances',
        'notes'
    ])->findOrFail($id);

    $currentClass = $student->class_rooms->first();

    $teacher = null;

    if ($currentClass) {
        $teacher = Teacher::whereHas('classRooms', function($q) use ($currentClass){
            $q->where('class_rooms.id',$currentClass->id);
        })->first();
    }

    $totalAttendance = $student->attendances()->count();
    $presentAttendance = $student->attendances()->where('is_present',1)->count();

    $attendancePercent = $totalAttendance > 0
        ? round(($presentAttendance / $totalAttendance) * 100)
        : 0;

    $totalFees = $student->class_rooms->sum('monthly_fee');
    $paidFees = $student->fees()->sum('amount');

    $feeDue = $totalFees - $paidFees;

    $latestNotes = ClassNote::where('student_id',$student->id)
        ->latest()
        ->take(5)
        ->get();

    return view('student.dashboard', compact(
        'student',
        'currentClass',
        'teacher',
        'attendancePercent',
        'feeDue',
        'latestNotes'
    ));
}
}
