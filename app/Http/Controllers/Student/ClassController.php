<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ClassRoom;
use App\Models\ClassHour;

class ClassController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $classes = $student->class_rooms()->with(['course', 'classType', 'teachers'])->get();

        return view('student.classes.index', compact('classes'));
    }

    public function show($id)
    {
        $student = Auth::guard('student')->user();

        $class = $student->class_rooms()
            ->with([
                'course',
                'classType',
                'teachers',
                'notes.files',
                'notes.teacher',
                'classHours' => function ($query) {
                    $query->latest();
                }
            ])
            ->findOrFail(decrypt($id));

        // Attendance stats
        $classHourIds = $class->classHours()
            ->where('status', 'completed')
            ->pluck('id');

        $totalClasses = $classHourIds->count();

        $attendanceStats = DB::table('student_attendance')
            ->select(
                'student_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(is_present) as present')
            )
            ->whereIn('class_hour_id', $classHourIds)
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        // Current student's attendance
        $myAttendance = $attendanceStats[$student->id] ?? null;
        $myPresent = $myAttendance->present ?? 0;
        $myPercentage = $totalClasses > 0 ? round(($myPresent / $totalClasses) * 100) : 0;

        return view('student.classes.show', compact(
            'class',
            'totalClasses',
            'myPresent',
            'myPercentage'
        ));
    }

    public function joinClass($id)
    {
        $classHour = ClassHour::findOrFail(decrypt($id));

        $classHour->update([
            'join_student_at' => now()
        ]);

        return redirect()->away($classHour->google_meet_link);
    }
}
