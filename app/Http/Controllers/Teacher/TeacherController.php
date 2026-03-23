<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ClassRoom;
use App\Models\ClassHour;
use App\Models\StudentAttendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{

/*
|--------------------------------------------------------------------------
| Assigned Classes List
|--------------------------------------------------------------------------
*/

public function classes()
{
    $teacher = Auth::guard('teacher')->user();

    $classes = $teacher->classRooms()
        ->with(['course','classType'])
        ->latest()
        ->paginate(10);

    return view('teacher.classes.index',compact('classes'));
}


/*
|--------------------------------------------------------------------------
| Show Class Details
|--------------------------------------------------------------------------
*/

public function classShow($id)
{
    $teacher = Auth::guard('teacher')->user();

    $class = ClassRoom::with([
        'course',
        'classType',
        'students',
        'classHours' => function ($query) {
            $query->latest()->limit(10);
        }
    ])->findOrFail(decrypt($id));

    // =========================
    // Attendance Calculation
    // =========================

    // Get completed class hour IDs
    $classHourIds = $class->classHours()
        ->where('status', 'completed')
        ->pluck('id');

    // Total completed classes
    $totalClasses = $classHourIds->count();

    // Attendance stats per student
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

    return view('teacher.classes.show', compact(
        'class',
        'attendanceStats',
        'totalClasses'
    ));
}


/*
|--------------------------------------------------------------------------
| Start Class (Google Meet)
|--------------------------------------------------------------------------
*/

public function startClass(Request $request)
{

    $request->validate([
        'class_room_id' => 'required',
        'google_meet_link' => 'required|url'
    ]);

    $teacher = Auth::guard('teacher')->user();

    $class = ClassRoom::findOrFail($request->class_room_id);

    $classHour = ClassHour::create([

        'class_room_id' => $class->id,

        'teacher_id' => $teacher->id,

        'duration' => $class->slot_duration,

        'google_meet_link' => $request->google_meet_link,

        'class_started_at' => now(),
        'status' => 'pending'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Create Student Attendance Rows
    |--------------------------------------------------------------------------
    */

    // foreach($class->students as $student){

    //     StudentAttendance::create([

    //         'class_hour_id' => $classHour->id,

    //         'student_id' => $student->id

    //     ]);
    // }

    return back()->with('success','Class started successfully.');

}

    public function updateClassHour(Request $request, $id)
    {
        $request->validate([
            'google_meet_link' => 'required|url'
        ]);

        $classHour = ClassHour::findOrFail($id);

        $classHour->update([
            'google_meet_link' => $request->google_meet_link
        ]);

        return back()->with('success','Class hour updated successfully');
    }

        public function getClassHourStudents($id)
    {
        $classHour = ClassHour::with('classRoom.students')->findOrFail($id);

        return response()->json([
            'students' => $classHour->classRoom->students
        ]);
    }

        public function markClassHourCompleted(Request $request, $id)
    {
        $classHour = ClassHour::with('classRoom.students')->findOrFail($id);

        // Prevent re-submission
        if ($classHour->status === 'completed') {
            return back()->with('error','Already marked as completed.');
        }

        $students = $classHour->classRoom->students;

        DB::transaction(function () use ($students, $classHour, $request) {

            if (StudentAttendance::where('class_hour_id',$classHour->id)->exists()) {
                throw new \Exception('Attendance already recorded');
            }

            $data = [];

            foreach ($students as $student) {
                $data[] = [
                    'class_hour_id' => $classHour->id,
                    'student_id' => $student->id,
                    'is_present' => isset($request->attendance[$student->id]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            StudentAttendance::insert($data);

            $classHour->update([
                'status' => 'completed'
            ]);

        });

        // Mark class as completed
        $classHour->update([
            'status' => 'completed'
        ]);

        return back()->with('success','Attendance saved and class completed.');
    }

}
