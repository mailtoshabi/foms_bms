<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ClassRoom;
use App\Models\ClassHour;
use Illuminate\Http\Request;

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
                'homeworks.files',
                'homeworks.teacher',
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
                return response()->json(['success' => false, 'error' => 'Link expired. Please wait for the teacher to update the link.'], 400);
            }
            return back()->with('error', 'Link expired. Please wait for the teacher to update the link.');
        }

        $classHour->update([
            'join_student_at' => now()
        ]);

        // Log student join log
        \App\Models\ClassHourStudentJoin::firstOrCreate([
            'class_hour_id' => $classHour->id,
            'student_id' => Auth::guard('student')->id()
        ], [
            'joined_at' => now()
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->away($classHour->google_meet_link);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        try {
            $student = Auth::guard('student')->user();
            if ($student) {
                $student->update([
                    'fcm_token' => $request->fcm_token
                ]);
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function checkBuzzer()
    {
        if (utility('buzzer_status', 'on') !== 'on') {
            return response()->json(['success' => false, 'error' => 'Buzzer is disabled.'], 403);
        }

        if (!Auth::guard('student')->check()) {
            return response()->json(['success' => false]);
        }

        $studentId = Auth::guard('student')->id();
        $intervalMs = (int)utility('buzzer_interval', 20000);
        $cacheSeconds = max(5, $intervalMs / 1000);

        // Cache the active buzzer check to prevent hammering the database
        $buzzerData = cache()->remember("student_{$studentId}_active_buzzer", $cacheSeconds, function () use ($studentId) {
            $buzzer = \App\Models\ClassHourBuzzer::with('classHour')
                ->where('student_id', $studentId)
                ->where('is_active', true)
                ->whereHas('classHour', function ($q) {
                    $q->where('status', 'pending');
                })
                ->first();

            if ($buzzer) {
                return [
                    'buzzer_id' => encrypt($buzzer->id),
                    'google_meet_link' => $buzzer->classHour->google_meet_link,
                    'class_hour_id' => encrypt($buzzer->class_hour_id)
                ];
            }

            return null;
        });

        if ($buzzerData) {
            return response()->json(array_merge(['success' => true], $buzzerData));
        }

        return response()->json(['success' => false]);
    }

    public function readBuzzer($id)
    {
        try {
            $buzzerId = decrypt($id);
            $buzzer = \App\Models\ClassHourBuzzer::where('student_id', Auth::guard('student')->id())
                ->findOrFail($buzzerId);

            $buzzer->update(['is_active' => false]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Full-screen buzzer alert page.
     *
     * Opened by the service worker when a push notification is tapped.
     * Because the page is opened via user interaction (tap), the browser
     * allows autoplay of audio immediately on load — solving the screen-off
     * sound problem for PWA.
     */
    public function buzzerAlert(Request $request)
    {
        $classHourId  = $request->query('class_hour_id');
        $googleMeetLink = null;

        if ($classHourId) {
            try {
                $classHour = \App\Models\ClassHour::find(decrypt($classHourId));
                if ($classHour) {
                    $googleMeetLink = $classHour->google_meet_link;
                }
            } catch (\Exception $e) {
                // Invalid encrypted ID — just show the alert without a join link
            }
        }

        return view('student.buzzer-alert', compact('classHourId', 'googleMeetLink'));
    }
}
