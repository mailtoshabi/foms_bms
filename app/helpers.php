<?php

// use Illuminate\Support\Facades\Request;


use App\Services\FeeService;
use Illuminate\Support\Facades\Route;

if (!function_exists('set_active')) {
    function set_active($routes)
    {
        $currentRoute = Route::currentRouteName();

        if (is_array($routes)) {
            foreach ($routes as $route) {
                if (Route::is($route)) {
                    return 'mm-active';
                }
            }
        } else {
            if (Route::is($routes)) {
                return 'mm-active';
            }
        }

        return '';
    }
}

use App\Models\Utility;

if (!function_exists('utility')) {
    function utility($key, $default = null)
    {
        static $settings;

        if (!$settings) {
            $settings = Utility::pluck('value', 'key')->toArray();
        }

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('studentWhatsappMessage')) {
    function studentWhatsappMessage($student, $password)
    {
        $message = "Hello {$student->name},\n\n" .
            "Your admission to FOMS Academy is successful.\n\n" .
            "Admission No: {$student->admission_no}\n" .
            "User Name: {$student->phone}\n" .
            "Password: {$password}\n\n" .
            "Login: " . route('student.login');

        $countryCode = $student->country ? preg_replace('/[^0-9]/', '', $student->country->code) : '91';
        $phone = $countryCode . $student->phone;

        return "https://wa.me/" . $phone . "?text=" . urlencode($message);
    }
}

if (!function_exists('teacherWhatsappMessage')) {
    function teacherWhatsappMessage($teacher, $password)
    {
        $message = "Hello {$teacher->name},\n\n" .
            "Your admission to FOMS Academy is successful.\n\n" .
            "Unique No: {$teacher->admission_no}\n" .
            "User Name: {$teacher->phone}\n" .
            "Password: {$password}\n\n" .
            "Login: " . route('teacher.login');

        $countryCode = $teacher->country ? preg_replace('/[^0-9]/', '', $teacher->country->code) : '91';
        $phone = $countryCode . $teacher->phone;

        return "https://wa.me/" . $phone . "?text=" . urlencode($message);
    }
}


use App\Models\Teacher;
use App\Services\SalaryService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

if (!function_exists('runDailySalaryFeeProcess')) {

    function runDailySalaryFeeProcess()
    {
        $today = Carbon::today()->toDateString();

        $utility = Utility::firstOrCreate(
            ['key' => 'daily_process_last_run'],
            ['value' => null]
        );

        // ✅ Already ran today
        if ($utility->value === $today) {
            return;
        }

        DB::transaction(function () use ($utility, $today) {

            $utility->refresh();

            if ($utility->value === $today) {
                return;
            }

            // ============================
            // 🔹 1. Teacher Salary
            // ============================
            foreach (Teacher::cursor() as $teacher) {
                app(SalaryService::class)
                    ->processTeacherSalary($teacher->id);
            }

            // ============================
            // 🔹 2. Group Class Fees
            // ============================
            app(FeeService::class)
                ->generateGroupFeesForToday();

            // ✅ Mark completed
            $utility->update([
                'value' => $today
            ]);
        });
    }
}

use App\Models\ClassHour;
use App\Models\StudentAttendance;
use App\Models\ClassNote;
use Illuminate\Support\Facades\Cache;

if (!function_exists('topTeachers')) {
    function topTeachers()
    {
        return Cache::remember('top_teachers', 300, function () {

            // ── Query 1: classes count + total minutes per teacher ────────────
            $classStats = ClassHour::where('status', 'completed')
                ->selectRaw('teacher_id, COUNT(*) as total_classes, SUM(duration) as total_minutes')
                ->groupBy('teacher_id')
                ->get()
                ->keyBy('teacher_id');

            // ── Query 2: attendance totals per teacher ────────────────────────
            $attendanceStats = DB::table('student_attendance')
                ->join('class_hours', 'student_attendance.class_hour_id', '=', 'class_hours.id')
                ->where('class_hours.status', 'completed')
                ->selectRaw('class_hours.teacher_id, COUNT(*) as total, SUM(student_attendance.is_present = 1) as present')
                ->groupBy('class_hours.teacher_id')
                ->get()
                ->keyBy('teacher_id');

            // ── Query 3: earnings per teacher ─────────────────────────────────
            $earningsStats = DB::table('class_hours')
                ->join('teacher_class_room', function ($join) {
                    $join->on('class_hours.class_room_id', '=', 'teacher_class_room.class_room_id')
                        ->on('class_hours.teacher_id', '=', 'teacher_class_room.teacher_id');
                })
                ->where('class_hours.status', 'completed')
                ->whereNotNull('class_hours.duration')
                ->selectRaw('class_hours.teacher_id, SUM((class_hours.duration / 60) * teacher_class_room.hourly_wage) as total_earnings')
                ->groupBy('class_hours.teacher_id')
                ->get()
                ->keyBy('teacher_id');

            // ── Query 4: class notes count per teacher ────────────────────────
            $notesStats = ClassNote::selectRaw('teacher_id, COUNT(*) as total_notes')
                ->groupBy('teacher_id')
                ->get()
                ->keyBy('teacher_id');

            // ── Query 5: only teachers who have conducted at least one class ──
            $teacherIds = $classStats->keys()->all();
            $teachers = Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');

            $data = [];

            foreach ($teachers as $teacher) {
                $cs = $classStats->get($teacher->id);
                $as = $attendanceStats->get($teacher->id);
                $es = $earningsStats->get($teacher->id);
                $ns = $notesStats->get($teacher->id);

                $totalClasses = $cs->total_classes ?? 0;
                $totalHours = ($cs->total_minutes ?? 0) / 60;
                $attendancePercent = ($as && $as->total)
                    ? ($as->present / $as->total) * 100
                    : 0;
                $earnings = $es->total_earnings ?? 0;
                $notesCount = $ns->total_notes ?? 0;

                // New Scoring Formula (Weights: Classes 35%, Hours 25%, Attendance 15%, Notes 15%, Earnings 10%)
                $score =
                    ($totalClasses * 0.35) +
                    ($totalHours * 0.25) +
                    ($attendancePercent * 0.15) +
                    ($notesCount * 0.15) +
                    (($earnings / 100) * 0.10);

                $data[] = [
                    'teacher' => $teacher,
                    'classes' => $totalClasses,
                    'hours' => round($totalHours, 2),
                    'attendance' => round($attendancePercent, 2),
                    'notes' => $notesCount,
                    'earnings' => round($earnings, 2),
                    'score' => round($score, 2),
                ];
            }

            usort($data, fn($a, $b) => $b['score'] <=> $a['score']);

            return array_slice($data, 0, 5);
        });
    }
}

if (!function_exists('teacherRankData')) {
    function teacherRankData($teacherId)
    {
        $totalClasses = ClassHour::where('teacher_id', $teacherId)
            ->where('status', 'completed')
            ->count();

        $totalHours = ClassHour::where('teacher_id', $teacherId)
            ->where('status', 'completed')
            ->sum('duration') / 60;

        $attendanceStats = DB::table('student_attendance')
            ->join('class_hours', 'student_attendance.class_hour_id', '=', 'class_hours.id')
            ->where('class_hours.teacher_id', $teacherId)
            ->where('class_hours.status', 'completed')
            ->selectRaw('COUNT(*) as total, SUM(student_attendance.is_present = 1) as present')
            ->first();

        $attendancePercent = ($attendanceStats->total)
            ? ($attendanceStats->present / $attendanceStats->total) * 100
            : 0;

        $earningsRow = DB::table('class_hours')
            ->join('teacher_class_room', function ($join) {
                $join->on('class_hours.class_room_id', '=', 'teacher_class_room.class_room_id')
                    ->on('class_hours.teacher_id', '=', 'teacher_class_room.teacher_id');
            })
            ->where('class_hours.teacher_id', $teacherId)
            ->where('class_hours.status', 'completed')
            ->whereNotNull('class_hours.duration')
            ->selectRaw('SUM((class_hours.duration / 60) * teacher_class_room.hourly_wage) as total_earnings')
            ->first();

        $earnings = $earningsRow->total_earnings ?? 0;
        $totalNotes = ClassNote::where('teacher_id', $teacherId)->count();

        // Updated Formula: Classes 35%, Hours 25%, Attendance 15%, Notes 15%, Earnings 10%
        $score = ($totalClasses * 0.35) +
            ($totalHours * 0.25) +
            ($attendancePercent * 0.15) +
            ($totalNotes * 0.15) +
            (($earnings / 100) * 0.10);

        $score = round($score, 2);

        if ($score >= 70) {
            $stars = 5;
            $label = 'Elite';
            $color = 'warning';
        } elseif ($score >= 50) {
            $stars = 4;
            $label = 'Expert';
            $color = 'primary';
        } elseif ($score >= 30) {
            $stars = 3;
            $label = 'Advanced';
            $color = 'info';
        } elseif ($score >= 15) {
            $stars = 2;
            $label = 'Intermediate';
            $color = 'secondary';
        } else {
            $stars = 1;
            $label = 'Beginner';
            $color = 'light';
        }

        return compact('score', 'stars', 'label', 'color', 'totalClasses', 'totalHours', 'attendancePercent', 'totalNotes');
    }
}

if (!function_exists('generateAdmissionNo')) {
    function generateAdmissionNo()
    {
        $now = now();
        $year = $now->format('y');
        $month = $now->format('m');

        $countThisMonth = \App\Models\Student::withTrashed()
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();

        $serial = str_pad($countThisMonth + 1, 2, '0', STR_PAD_LEFT);
        return 'FA/' . $year . '/' . $month . '/' . $serial;
    }
}
