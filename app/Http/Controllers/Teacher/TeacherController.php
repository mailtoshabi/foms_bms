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
            ->with(['course', 'classType'])
            ->latest()
            ->paginate(utility('pagination', 50));

        return view('teacher.classes.index', compact('classes'));
    }


    /*
    |--------------------------------------------------------------------------
    | Show Class Details
    |--------------------------------------------------------------------------
    */

    public function classShow($id)
    {
        $teacher = Auth::guard('teacher')->user();

        $class = $teacher->classRooms()->with([
            'course',
            'classType',
            'students',
            'notes.files',
            'homeworks.files',
            'classHours' => function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id)
                    ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                    ->latest()
                    ->limit(15);
            }
        ])->findOrFail(decrypt($id));

        // =========================
        // Attendance Calculation (Current Cycle Only)
        // =========================

        // Get completed class hour IDs for the current billing cycle
        $classHourQuery = $class->classHours()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->where('has_fee_calculated', false);

        if ($class->classType && strtolower($class->classType->name) === 'group' && $class->starting_date) {
            // Determine current billing cycle dates based on starting_date day of month
            $startingDate = Carbon::parse($class->starting_date);
            $targetDay = $startingDate->day;

            $today = now();
            // Get start of this month
            $currentCycleStart = $today->copy()->startOfMonth();
            // Capped to the number of days in the current month
            $daysInMonth = $currentCycleStart->daysInMonth;
            $dayToUse = min($targetDay, $daysInMonth);
            $currentCycleStart->day($dayToUse);

            if ($today->lt($currentCycleStart)) {
                // If today is before this month's billing day, the cycle started in the previous month
                $currentCycleStart->subMonth()->startOfMonth();
                $daysInPrevMonth = $currentCycleStart->daysInMonth;
                $dayToUsePrev = min($targetDay, $daysInPrevMonth);
                $currentCycleStart->day($dayToUsePrev);
            }

            if ($currentCycleStart->lt($startingDate)) {
                $currentCycleStart = $startingDate->copy();
            }

            // Capped billing cycle end date (next month's billing day)
            $currentCycleEnd = $currentCycleStart->copy()->addMonth()->startOfMonth();
            $daysInNextMonth = $currentCycleEnd->daysInMonth;
            $dayToUseNext = min($targetDay, $daysInNextMonth);
            $currentCycleEnd->day($dayToUseNext);

            $classHourQuery->whereBetween('completed_at', [
                $currentCycleStart->toDateTimeString(),
                $currentCycleEnd->toDateTimeString()
            ]);
        }

        $classHourIds = $classHourQuery->pluck('id');

        // Total completed classes in current cycle
        $totalClasses = $classHourIds->count();

        // Attendance stats per student in current cycle
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

        if ($class->is_completed) {
            return back()->with('error', 'Cannot start class. This class is already marked as completed.');
        }

        // Check group class hour limits for each monthly billing cycle
        if ($class->classType && strtolower($class->classType->name) === 'group') {
            $limit = ($class->classes_per_week ?? 0) * 4;
            if ($limit > 0 && $class->starting_date) {
                // Determine current billing cycle dates based on starting_date day of month
                $startingDate = Carbon::parse($class->starting_date);
                $targetDay = $startingDate->day;

                $today = now();
                // Get start of this month
                $currentCycleStart = $today->copy()->startOfMonth();
                // Capped to the number of days in the current month
                $daysInMonth = $currentCycleStart->daysInMonth;
                $dayToUse = min($targetDay, $daysInMonth);
                $currentCycleStart->day($dayToUse);

                if ($today->lt($currentCycleStart)) {
                    // If today is before this month's billing day, the cycle started in the previous month
                    $currentCycleStart->subMonth()->startOfMonth();
                    $daysInPrevMonth = $currentCycleStart->daysInMonth;
                    $dayToUsePrev = min($targetDay, $daysInPrevMonth);
                    $currentCycleStart->day($dayToUsePrev);
                }

                if ($currentCycleStart->lt($startingDate)) {
                    $currentCycleStart = $startingDate->copy();
                }

                // Capped billing cycle end date (next month's billing day)
                $currentCycleEnd = $currentCycleStart->copy()->addMonth()->startOfMonth();
                $daysInNextMonth = $currentCycleEnd->daysInMonth;
                $dayToUseNext = min($targetDay, $daysInNextMonth);
                $currentCycleEnd->day($dayToUseNext);

                $classHoursCount = ClassHour::where('class_room_id', $class->id)
                    ->where('status', 'completed')
                    ->where('has_fee_calculated', false)
                    ->whereBetween('completed_at', [
                        $currentCycleStart->toDateTimeString(),
                        $currentCycleEnd->toDateTimeString()
                    ])
                    ->count();

                if ($classHoursCount >= $limit) {
                    return back()->with('error', 'Cannot start class. You have already completed the maximum allowed ' . $limit . ' classes for the current monthly billing cycle.');
                }
            }
        }

        $hasPending = ClassHour::where('class_room_id', $class->id)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return back()->with('error', 'Cannot start class. You already have a pending session for this class.');
        }

        $hourlyWage = DB::table('teacher_class_room')
            ->where('class_room_id', $class->id)
            ->where('teacher_id', $teacher->id)
            ->value('hourly_wage');

        $classHour = ClassHour::create([

            'class_room_id' => $class->id,

            'teacher_id' => $teacher->id,

            'duration' => $class->slot_duration,

            'google_meet_link' => $request->google_meet_link,

            'hourly_wage' => $hourlyWage,

            'status' => 'pending',

            'link_updated_at' => now()
        ]);

        return back()->with('success', 'Class started successfully.');

    }

    public function updateClassHour(Request $request, $id)
    {
        $classHour = ClassHour::with('classRoom')->findOrFail($id);

        if ($classHour->classRoom->is_completed) {
            return back()->with('error', 'Cannot update. The class is already marked as completed.');
        }

        $request->validate([
            'google_meet_link' => 'required|url'
        ]);

        $classHour->update([
            'google_meet_link' => $request->google_meet_link,

            'link_updated_at' => now()
        ]);

        return back()->with('success', 'Class hour updated successfully');
    }

    public function getClassHourStudents($id)
    {
        $classHour = ClassHour::with(['classRoom.students.classHourJoins' => function ($q) use ($id) {
            $q->where('class_hour_id', $id);
        }])->findOrFail($id);

        $students = $classHour->classRoom->students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'admission_no' => $student->admission_no,
                'has_joined' => $student->classHourJoins->isNotEmpty()
            ];
        });

        return response()->json([
            'students' => $students
        ]);
    }

    public function buzzClassHour(Request $request, $id)
    {
        $classHour = ClassHour::with('classRoom')->findOrFail($id);

        if ($classHour->status !== 'pending') {
            return response()->json(['success' => false, 'error' => 'Buzzer can only be sent for pending/active sessions.'], 400);
        }

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'required|integer|exists:students,id'
        ]);

        $firebaseService = new \App\Services\FirebaseService();
        $className = $classHour->classRoom->name ?? 'Class Session';

        $students = \App\Models\Student::whereIn('id', $request->student_ids)->get();

        foreach ($students as $student) {
            // Create or update active buzzer alert
            \App\Models\ClassHourBuzzer::updateOrCreate([
                'class_hour_id' => $classHour->id,
                'student_id' => $student->id,
            ], [
                'is_active' => true,
                'updated_at' => now()
            ]);

            // Dispatch Firebase Push alert if token exists
            if ($student->fcm_token) {
                $firebaseService->sendNotification(
                    $student->fcm_token,
                    "Class Join Reminder: {$className}",
                    "Your teacher is buzzing you to join the class session immediately.",
                    [
                        'class_hour_id' => encrypt($classHour->id),
                        'google_meet_link' => $classHour->google_meet_link
                    ]
                );
            }
        }

        return response()->json(['success' => true, 'message' => 'Buzzer alerts sent successfully.']);
    }

    public function joinClass(Request $request, $id)
    {
        $classHour = ClassHour::findOrFail(decrypt($id));

        if ($classHour->status === 'completed') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'The class is already marked as completed.'], 400);
            }
            return back()->with('error', 'The class is already marked as completed.');
        }

        if (!\Carbon\Carbon::parse($classHour->link_updated_at)->isToday()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Link expired. Please edit the link first.'], 400);
            }
            return back()->with('error', 'Link expired. Please edit the link first.');
        }

        $classHour->update([
            'join_teacher_at' => now()
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->away($classHour->google_meet_link);
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
                'status_pending' => $query->where('status', 'pending'),
                'status_completed' => $query->where('status', 'completed'),
                'salary_0' => $query->where('status', 'completed')->where('has_salary_calculated', false),
                'salary_1' => $query->where('has_salary_calculated', true),
                default => null,
            };
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->class_room_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('link_updated_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('link_updated_at', '<=', $request->date_to);
        }

        $sessions = $query->latest()->paginate(utility('pagination', 50))->withQueryString();

        $classRooms = $teacher->classRooms()->with('course')->get();

        return view('teacher.classes.sessions', compact('sessions', 'classRooms'));
    }


    public function markClassHourCompleted(Request $request, $id)
    {
        $classHour = ClassHour::with('classRoom.students', 'classRoom.teachers', 'classRoom.classType')
            ->findOrFail($id);

        if ($classHour->classRoom->is_completed) {
            return back()->with('error', 'Cannot mark completed. The class is already marked as completed.');
        }

        if ($classHour->status === 'completed') {
            return back()->with('error', 'Already marked as completed.');
        }

        if (is_null($classHour->join_student_at)) {
            return back()->with('error', 'Cannot mark completed. No student has joined the class yet.');
        }

        $class = $classHour->classRoom;
        $students = $class->students;
        $teacher = $class->teachers->first();

        try {

            DB::transaction(function () use ($students, $classHour, $request, $teacher, $class) {

                // duplicate attendance
                if (StudentAttendance::where('class_hour_id', $classHour->id)->exists()) {
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
                        ->where('status', 'completed')
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
                    'status' => 'completed',
                    'completed_at' => now()
                ]);

                // =========================
                // Update Class Starting Date (First Class Hour)
                // =========================
                $previousClassHours = ClassHour::where('class_room_id', $class->id)
                    ->where('id', '!=', $classHour->id)
                    ->where('status', 'completed')
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

                    // Get unprocessed completed class hours chronologically
                    $completedClassHours = ClassHour::where('class_room_id', $class->id)
                        ->where('status', 'completed')
                        ->where('has_fee_calculated', false)
                        ->orderBy('completed_at')
                        ->orderBy('id')
                        ->get();

                    if ($completedClassHours->count() >= $requiredClasses) {

                        // Take only required cycle
                        $cycleClassHours = $completedClassHours->take($requiredClasses);

                        // Generate fees for each student
                        foreach ($students as $student) {

                            // Skip inactive, blocked, or monthly fee exempted students
                            if ($student->status !== 'active' || $student->is_blocked || $student->is_monthly_fee_exempted) {
                                continue;
                            }

                            $amount = max(0, $class->monthly_fee - ($student->monthly_fee_discount ?? 0));

                            if ($amount <= 0)
                                continue;

                            // 🔒 Avoid duplicate monthly fee for same cycle
                            $fee = Fee::create([
                                'student_id' => $student->id,
                                'class_room_id' => $class->id,
                                'type' => 'monthly',
                                'amount' => $amount,
                                'due_date' => now()->addDays(7),
                                'status' => 'unpaid'
                            ]);

                            app(\App\Services\FeeService::class)->applyWalletBalance($fee);

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

        return back()->with('success', 'Attendance saved and session completed.');
    }

}
