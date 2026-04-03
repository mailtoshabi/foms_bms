<?php

// use Illuminate\Support\Facades\Request;


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
            $settings = Utility::pluck('value','key')->toArray();
        }

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('studentWhatsappMessage')) {
    function studentWhatsappMessage($student, $password)
    {
        $message = "Hello {$student->name},\n\n".
                "Your admission to FOMS Academy is successful.\n\n".
                "Admission No: {$student->admission_no}\n".
                "User Name: {$student->phone}\n".
                "Password: {$password}\n\n".
                "Login: ".route('student.login');

        $phone = '91'.$student->phone;

        return "https://wa.me/".$phone."?text=".urlencode($message);
    }
}

if (!function_exists('teacherWhatsappMessage')) {
    function teacherWhatsappMessage($teacher, $password)
    {
        $message = "Hello {$teacher->name},\n\n".
                "Your admission to FOMS Academy is successful.\n\n".
                "Unique No: {$teacher->admission_no}\n".
                "User Name: {$teacher->phone}\n".
                "Password: {$password}\n\n".
                "Login: ".route('teacher.login');

        $phone = '91'.$teacher  ->phone;

        return "https://wa.me/".$phone."?text=".urlencode($message);
    }
}


use App\Models\Teacher;
use App\Services\SalaryService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

if (!function_exists('runDailySalaryProcess')) {

    function runDailySalaryProcess()
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
                app(\App\Services\SalaryService::class)
                    ->processTeacherSalary($teacher->id);
            }

            // ============================
            // 🔹 2. Group Class Fees
            // ============================
            app(\App\Services\FeeService::class)
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

if (!function_exists('topTeachers')) {
    function topTeachers()
    {
        $teachers = Teacher::all();

        $data = [];

        foreach ($teachers as $teacher) {

            // Total completed classes
            $totalClasses = ClassHour::where('teacher_id',$teacher->id)
                ->where('status','completed')
                ->count();

            // Total hours
            $totalMinutes = ClassHour::where('teacher_id',$teacher->id)
                ->where('status','completed')
                ->sum('duration');

            $totalHours = $totalMinutes / 60;

            // =========================
            // Student Attendance %
            // =========================

            $attendanceStats = StudentAttendance::whereHas('classHour', function ($q) use ($teacher) {
                $q->where('teacher_id',$teacher->id)
                ->where('status','completed');
            })
            ->selectRaw("
                COUNT(*) as total,
                SUM(is_present = 1) as present
            ")
            ->first();

            $attendancePercent = $attendanceStats->total
                ? ($attendanceStats->present / $attendanceStats->total) * 100
                : 0;

            // =========================
            // Earnings
            // =========================

            $classHours = ClassHour::with([
                'classRoom.teachers' => function ($q) use ($teacher) {
                    $q->where('teacher_id',$teacher->id);
                }
            ])
            ->where('teacher_id',$teacher->id)
            ->where('status','completed')
            ->get();

            $earnings = 0;

            foreach ($classHours as $hour) {

                if (!$hour->duration) continue;

                $pivot = optional($hour->classRoom->teachers->first())->pivot;
                $wage = $pivot->hourly_wage ?? 0;

                $earnings += ($hour->duration / 60) * $wage;
            }

            // 🎯 Score
            $score =
                ($totalClasses * 0.4) +
                ($totalHours * 0.3) +
                ($attendancePercent * 0.2) +
                (($earnings / 100) * 0.1);

            $data[] = [
                'teacher' => $teacher,
                'classes' => $totalClasses,
                'hours' => round($totalHours,2),
                'attendance' => round($attendancePercent,2),
                'earnings' => round($earnings,2),
                'score' => round($score,2),
            ];
        }

        usort($data, fn($a,$b) => $b['score'] <=> $a['score']);

        return array_slice($data, 0, 5);
    }
}






