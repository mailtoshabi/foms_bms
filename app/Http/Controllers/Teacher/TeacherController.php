<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ClassRoom;
use App\Models\ClassHour;
use App\Models\Fee;
use App\Models\StudentAttendance;
use Carbon\Carbon;
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
        'notes.files',
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

    $hourlyWage = DB::table('teacher_class_room')
        ->where('class_room_id', $class->id)
        ->where('teacher_id', $teacher->id)
        ->value('hourly_wage');

    $classHour = ClassHour::create([

        'class_room_id' => $class->id,

        'teacher_id' => $teacher->id,

        'duration' => $class->slot_duration,

        'google_meet_link' => $request->google_meet_link,

        'class_started_at' => now(),

        'hourly_wage' => $hourlyWage,

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

    /*
    |--------------------------------------------------------------------------
    | Sessions – All class hours for the logged-in teacher
    |--------------------------------------------------------------------------
    */
    public function sessions(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();

        $query = ClassHour::with('classRoom.course')
            ->where('teacher_id', $teacher->id);

        if ($request->filled('filter')) {
            match ($request->filter) {
                'status_pending'   => $query->where('status', 'pending'),
                'status_completed' => $query->where('status', 'completed'),
                'salary_0'         => $query->where('status', 'completed')->where('has_salary_calculated', false),
                'salary_1'         => $query->where('has_salary_calculated', true),
                default            => null,
            };
        }

        if ($request->filled('date_from')) {
            $query->whereDate('class_started_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('class_started_at', '<=', $request->date_to);
        }

        $sessions = $query->latest()->paginate(15)->withQueryString();

        return view('teacher.classes.sessions', compact('sessions'));
    }


public function markClassHourCompleted(Request $request, $id)
{
    $classHour = ClassHour::with('classRoom.students','classRoom.teachers','classRoom.classType')
        ->findOrFail($id);

    if ($classHour->status === 'completed') {
        return back()->with('error','Already marked as completed.');
    }

    $class = $classHour->classRoom;
    $students = $class->students;
    $teacher = $class->teachers->first();

    try {

        DB::transaction(function () use ($students, $classHour, $request, $teacher, $class) {

            // duplicate attendance
            if (StudentAttendance::where('class_hour_id',$classHour->id)->exists()) {
                throw new \Exception('Attendance already recorded');
            }

            // =========================
            // Insert Attendance (Bulk)
            // =========================
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

            // =========================
            // Salary Cycle Init
            // =========================
            if ($teacher && is_null($teacher->salary_cycle_day)) {

                $hasPreviousCompletedClass = ClassHour::whereHas('classRoom.teachers', function ($q) use ($teacher) {
                        $q->where('teacher_id', $teacher->id);
                    })
                    ->where('status','completed')
                    ->exists();

                if (!$hasPreviousCompletedClass) {
                    $teacher->update([
                        'salary_cycle_day' => now()->day
                    ]);
                }
            }

            // =========================
            // Mark class completed
            // =========================
            $classHour->update([
                'status' => 'completed'
            ]);

            // =========================
            // Update Class Starting Date (First Class Hour)
            // =========================
            $previousClassHours = ClassHour::where('class_room_id', $class->id)
                ->where('id', '!=', $classHour->id)
                ->exists();

            if (!$previousClassHours && is_null($class->starting_date)) {
                $class->update([
                    'starting_date' => now()->toDateString()
                ]);
            }

            // =========================
            // MONTHLY FEE LOGIC 🔥
            // =========================

            // Only create fees for 'individual' class types
            if ($class->classType && strtolower($class->classType->name) === 'individual') {

                $requiredClasses = $class->classes_per_week * 4;

                // Get unprocessed completed class hours
                $completedClassHours = ClassHour::where('class_room_id', $class->id)
                    ->where('status','completed')
                    ->where('has_fee_calculated', false)
                    ->orderBy('class_started_at')
                    ->get();

                if ($completedClassHours->count() >= $requiredClasses) {

                    // Take only required cycle
                    $cycleClassHours = $completedClassHours->take($requiredClasses);

                    // Generate fees for each student
                    foreach ($students as $student) {

                        $amount = max(0, $class->monthly_fee - ($student->monthly_fee_discount ?? 0));

                        if ($amount <= 0) continue;

                        // 🔒 Avoid duplicate monthly fee for same cycle
                        Fee::create([
                            'student_id' => $student->id,
                            'class_room_id' => $class->id,
                            'type' => 'monthly',
                            'amount' => $amount,
                            'due_date' => now()->addDays(7),
                            'status' => 'unpaid'
                        ]);

                    }

                    // Mark class hours as calculated
                    ClassHour::whereIn('id', $cycleClassHours->pluck('id'))
                        ->update([
                            'has_fee_calculated' => true
                        ]);
                }
            }

        });

    } catch (\Exception $e) {

        return back()->with('error', $e->getMessage());
    }

    return back()->with('success','Attendance saved and class completed.');
}



// public function classHourDetails($id)
// {
//     $classHour = ClassHour::with('classRoom','teacher','studentAttendances.student')
//         ->findOrFail($id);

//     return view('teacher.class_hours.show', compact('classHour'));

// }
// public function classHourStudents($id)
// {
//     $classHour = ClassHour::with('classRoom.students')->findOrFail($id);

//     return response()->json([
//         'students' => $classHour->classRoom->students
//     ]);
// }
// public function updateClassHourLink(Request $request, $id)
// {
//     $request->validate([
//         'google_meet_link' => 'required|url'
//     ]);

//     $classHour = ClassHour::findOrFail($id);

//     $classHour->update([
//         'google_meet_link' => $request->google_meet_link
//     ]);

//     return back()->with('success','Session Link updated successfully');
// }

// public function classHourStudentsJson($id)
// {
//     $classHour = ClassHour::with('classRoom.students')->findOrFail($id);

//     return response()->json([
//         'students' => $classHour->classRoom->students
//     ]);

// }
// public function classHourDetailsJson($id)
// {
//     $classHour = ClassHour::with('classRoom','teacher','studentAttendances.student')
//         ->findOrFail($id);

//     return response()->json([
//         'classHour' => $classHour
//     ]);

// }
// public function markClassHourCompletedJson(Request $request, $id)
// {
//     $classHour = ClassHour::with('classRoom.students','classRoom.teachers','classRoom.classType')
//         ->findOrFail($id);

//     if ($classHour->status === 'completed') {
//         return response()->json(['error' => 'Already marked as completed.'], 400);
//     }

//     $class = $classHour->classRoom;
//     $students = $class->students;

// }

}
