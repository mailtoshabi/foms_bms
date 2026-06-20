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

        if (!$settings || app()->runningUnitTests()) {
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
        $todayStr = Carbon::today()->toDateString();

        $utility = Utility::firstOrCreate(
            ['key' => 'daily_process_last_run'],
            ['value' => null]
        );

        // ✅ Already ran today
        if ($utility->value === $todayStr) {
            return;
        }

        DB::transaction(function () use ($utility, $todayStr) {

            $utility->refresh();

            if ($utility->value === $todayStr) {
                return;
            }

            // Determine the starting date for catch up
            if ($utility->value) {
                $startDate = Carbon::parse($utility->value)->addDay();
            } else {
                $startDate = Carbon::today();
            }

            $endDate = Carbon::today();

            // Run process sequentially for every missed date up to today
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->toDateString();

                // ============================
                // 🔹 1. Teacher Salary
                // ============================
                foreach (Teacher::cursor() as $teacher) {
                    app(SalaryService::class)
                        ->processTeacherSalary($teacher->id, $dateStr);
                }

                // ============================
                // 🔹 2. Group Class Fees
                // ============================
                app(FeeService::class)
                    ->generateGroupFeesForToday($dateStr);
            }

            // ✅ Mark completed
            $utility->update([
                'value' => $todayStr
            ]);
        });
    }
}

use App\Models\ClassHour;
use App\Models\StudentAttendance;
use App\Models\ClassNote;
use Illuminate\Support\Facades\Cache;

if (!function_exists('allTeachersRanked')) {
    function allTeachersRanked()
    {
        return Cache::remember('all_teachers_ranked', 300, function () {

            // ── Query 1: classes count + total minutes per teacher ────────────
            $classStats = ClassHour::join('class_rooms', 'class_hours.class_room_id', '=', 'class_rooms.id')
                ->where('class_hours.status', 'completed')
                ->where('class_rooms.is_completed', false)
                ->selectRaw('class_hours.teacher_id, COUNT(class_hours.id) as total_classes, SUM(class_hours.duration) as total_minutes')
                ->groupBy('class_hours.teacher_id')
                ->get()
                ->keyBy('teacher_id');

            // ── Query 2: attendance totals per teacher ────────────────────────
            $attendanceStats = DB::table('student_attendance')
                ->join('class_hours', 'student_attendance.class_hour_id', '=', 'class_hours.id')
                ->join('class_rooms', 'class_hours.class_room_id', '=', 'class_rooms.id')
                ->join('students', 'student_attendance.student_id', '=', 'students.id')
                ->where('class_hours.status', 'completed')
                ->where('class_rooms.is_completed', false)
                ->where('students.status', 'active')
                ->selectRaw('class_hours.teacher_id, COUNT(student_attendance.id) as total, SUM(student_attendance.is_present = 1) as present')
                ->groupBy('class_hours.teacher_id')
                ->get()
                ->keyBy('teacher_id');

            // ── Query 3: earnings per teacher ─────────────────────────────────
            $earningsStats = DB::table('class_hours')
                ->join('teacher_class_room', function ($join) {
                    $join->on('class_hours.class_room_id', '=', 'teacher_class_room.class_room_id')
                        ->on('class_hours.teacher_id', '=', 'teacher_class_room.teacher_id');
                })
                ->join('class_rooms', 'class_hours.class_room_id', '=', 'class_rooms.id')
                ->where('class_hours.status', 'completed')
                ->where('class_rooms.is_completed', false)
                ->whereNotNull('class_hours.duration')
                ->selectRaw('class_hours.teacher_id, SUM((class_hours.duration / 60) * teacher_class_room.hourly_wage) as total_earnings')
                ->groupBy('class_hours.teacher_id')
                ->get()
                ->keyBy('teacher_id');

            // ── Query 4: class notes count per teacher ────────────────────────
            $notesStats = ClassNote::join('class_rooms', 'class_notes.class_room_id', '=', 'class_rooms.id')
                ->where('class_rooms.is_completed', false)
                ->selectRaw('class_notes.teacher_id, COUNT(class_notes.id) as total_notes')
                ->groupBy('class_notes.teacher_id')
                ->get()
                ->keyBy('teacher_id');

            // ── Query 5: active students per teacher ──────────────────────────
            $studentStats = DB::table('teacher_class_room')
                ->join('class_rooms', 'teacher_class_room.class_room_id', '=', 'class_rooms.id')
                ->join('student_class_room', 'class_rooms.id', '=', 'student_class_room.class_room_id')
                ->join('students', 'student_class_room.student_id', '=', 'students.id')
                ->where('class_rooms.is_completed', false)
                ->where('students.status', 'active')
                ->selectRaw('teacher_class_room.teacher_id, COUNT(DISTINCT students.id) as total_students')
                ->groupBy('teacher_class_room.teacher_id')
                ->get()
                ->keyBy('teacher_id');

            // ── Query 6: only teachers who have conducted at least one class or handle students ──
            $teacherIds = collect()
                ->merge($classStats->keys())
                ->merge($studentStats->keys())
                ->unique()
                ->all();

            $teachers = \App\Models\Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');

            $data = [];

            foreach ($teachers as $teacher) {
                $cs = $classStats->get($teacher->id);
                $as = $attendanceStats->get($teacher->id);
                $es = $earningsStats->get($teacher->id);
                $ns = $notesStats->get($teacher->id);
                $ss = $studentStats->get($teacher->id);

                $totalClasses = $cs->total_classes ?? 0;
                $totalHours = ($cs->total_minutes ?? 0) / 60;
                $attendancePercent = ($as && $as->total)
                    ? ($as->present / $as->total) * 100
                    : 0;
                $earnings = $es->total_earnings ?? 0;
                $notesCount = $ns->total_notes ?? 0;
                $studentsCount = $ss->total_students ?? 0;

                // New Scoring Formula (Weights: Classes 15%, Hours 10%, Students 40%, Attendance 10%, Notes 10%, Earnings 15%)
                $score = (
                    ($totalClasses * 0.15) +
                    ($totalHours * 0.10) +
                    ($studentsCount * 0.40) +
                    ($attendancePercent * 0.10) +
                    ($notesCount * 0.10) +
                    (($earnings / 100) * 0.15)
                ) / 4;

                $data[] = [
                    'teacher' => $teacher,
                    'classes' => $totalClasses,
                    'hours' => round($totalHours, 2),
                    'attendance' => round($attendancePercent, 2),
                    'notes' => $notesCount,
                    'students' => $studentsCount,
                    'earnings' => round($earnings, 2),
                    'score' => round($score, 2),
                ];
            }

            usort($data, fn($a, $b) => $b['score'] <=> $a['score']);

            return $data;
        });
    }
}

if (!function_exists('allTimeTeachersRanked')) {
    function allTimeTeachersRanked()
    {
        return Cache::remember('all_time_teachers_ranked', 300, function () {

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

            // ── Query 5: total students per teacher (all time) ───────────────
            $studentStats = DB::table('teacher_class_room')
                ->join('student_class_room', 'teacher_class_room.class_room_id', '=', 'student_class_room.class_room_id')
                ->selectRaw('teacher_class_room.teacher_id, COUNT(DISTINCT student_class_room.student_id) as total_students')
                ->groupBy('teacher_class_room.teacher_id')
                ->get()
                ->keyBy('teacher_id');

            // ── Query 6: only teachers who have conducted at least one class or handle students ──
            $teacherIds = collect()
                ->merge($classStats->keys())
                ->merge($studentStats->keys())
                ->unique()
                ->all();

            $teachers = \App\Models\Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');

            $data = [];

            foreach ($teachers as $teacher) {
                $cs = $classStats->get($teacher->id);
                $as = $attendanceStats->get($teacher->id);
                $es = $earningsStats->get($teacher->id);
                $ns = $notesStats->get($teacher->id);
                $ss = $studentStats->get($teacher->id);

                $totalClasses = $cs->total_classes ?? 0;
                $totalHours = ($cs->total_minutes ?? 0) / 60;
                $attendancePercent = ($as && $as->total)
                    ? ($as->present / $as->total) * 100
                    : 0;
                $earnings = $es->total_earnings ?? 0;
                $notesCount = $ns->total_notes ?? 0;
                $studentsCount = $ss->total_students ?? 0;

                // New Scoring Formula (Weights: Classes 15%, Hours 10%, Students 40%, Attendance 10%, Notes 10%, Earnings 15%)
                $score = (
                    ($totalClasses * 0.15) +
                    ($totalHours * 0.10) +
                    ($studentsCount * 0.40) +
                    ($attendancePercent * 0.10) +
                    ($notesCount * 0.10) +
                    (($earnings / 100) * 0.15)
                ) / 4;

                $data[] = [
                    'teacher' => $teacher,
                    'classes' => $totalClasses,
                    'hours' => round($totalHours, 2),
                    'attendance' => round($attendancePercent, 2),
                    'notes' => $notesCount,
                    'students' => $studentsCount,
                    'earnings' => round($earnings, 2),
                    'score' => round($score, 2),
                ];
            }

            usort($data, fn($a, $b) => $b['score'] <=> $a['score']);

            return $data;
        });
    }
}

if (!function_exists('topTeachers')) {
    function topTeachers()
    {
        return array_slice(allTeachersRanked(), 0, 5);
    }
}

if (!function_exists('topAllTimeTeachers')) {
    function topAllTimeTeachers()
    {
        return array_slice(allTimeTeachersRanked(), 0, 5);
    }
}

if (!function_exists('teacherRankData')) {
    function teacherRankData($teacherId)
    {
        $allRanked = allTeachersRanked();

        $teacherData = null;
        $rank = '-'; // Default if not found

        foreach ($allRanked as $index => $data) {
            if ($data['teacher']->id == $teacherId) {
                $teacherData = $data;
                $rank = $index + 1; // 1-based ranking
                break;
            }
        }

        // If teacher is not in the ranked list (e.g., 0 completed classes)
        if (!$teacherData) {
            $score = 0;
            $totalClasses = 0;
            $totalHours = 0;
            $attendancePercent = 0;
            $totalNotes = 0;
            $studentsCount = 0;
            $earnings = 0;
        } else {
            $score = $teacherData['score'];
            $totalClasses = $teacherData['classes'];
            $totalHours = $teacherData['hours'];
            $attendancePercent = $teacherData['attendance'];
            $totalNotes = $teacherData['notes'];
            $studentsCount = $teacherData['students'];
            $earnings = $teacherData['earnings'];
        }

        $tier = getTeacherRankTier($score);
        $stars = $tier['stars'];
        $label = $tier['label'];
        $color = $tier['color'];

        return compact('score', 'stars', 'label', 'color', 'totalClasses', 'totalHours', 'attendancePercent', 'totalNotes', 'studentsCount', 'rank', 'earnings');
    }
}

if (!function_exists('getTeacherRankTier')) {
    function getTeacherRankTier($score)
    {
        if ($score >= 70) {
            return ['stars' => 5, 'label' => 'Elite', 'color' => 'warning'];
        } elseif ($score >= 50) {
            return ['stars' => 4, 'label' => 'Expert', 'color' => 'primary'];
        } elseif ($score >= 30) {
            return ['stars' => 3, 'label' => 'Advanced', 'color' => 'info'];
        } elseif ($score >= 15) {
            return ['stars' => 2, 'label' => 'Intermediate', 'color' => 'secondary'];
        } else {
            return ['stars' => 1, 'label' => 'Beginner', 'color' => 'light'];
        }
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
