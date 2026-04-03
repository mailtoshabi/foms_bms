<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassHour;
use App\Models\ClassNote;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Models\TeacherSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{


public function dashboard()
{
    $teacher = Auth::guard('teacher')->user();

    // Assigned Classes
    $classes = $teacher->classRooms()
        ->with(['course','classType'])
        ->get();

    $stats = \App\Models\ClassHour::where('teacher_id',$teacher->id)
        ->selectRaw("
            COUNT(*) as total,
            SUM(status = 'completed') as completed
        ")
        ->first();

    $completedClasses = $stats->completed;

    // Total Hours
    $totalMinutes = ClassHour::where('teacher_id',$teacher->id)
        ->where('status','completed')
        ->sum('duration');

    $totalHours = round($totalMinutes / 60, 2);

    // This Month Classes
    $thisMonthClasses = ClassHour::where('teacher_id',$teacher->id)
        ->where('status','completed')
        ->whereMonth('class_started_at', now()->month)
        ->whereYear('class_started_at', now()->year)
        ->count();

    // =========================
    // EARNINGS (THIS MONTH)
    // =========================

    $thisMonthHours = ClassHour::with([
        'classRoom.teachers' => function ($q) use ($teacher) {
            $q->where('teacher_id',$teacher->id);
        }
    ])
    ->where('teacher_id',$teacher->id)
    ->where('status','completed')
    ->whereMonth('class_started_at', now()->month)
    ->whereYear('class_started_at', now()->year)
    ->get();

    $earningsThisMonth = 0;

    foreach ($thisMonthHours as $hour) {

        if (!$hour->duration) continue;

        $pivot = optional($hour->classRoom->teachers->first())->pivot;

        $wage = $pivot->hourly_wage ?? 0;

        $hours = $hour->duration / 60;

        $earningsThisMonth += $hours * $wage;
    }

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

    //Pending Salary
    $pendingHours = ClassHour::with([
        'classRoom.teachers' => function ($q) use ($teacher) {
            $q->where('teacher_id',$teacher->id);
        }
    ])
    ->where('teacher_id',$teacher->id)
    ->where('status','completed')
    ->where('has_salary_calculated', false)
    ->get();

    $pendingSalary = 0;

    foreach ($pendingHours as $hour) {

        if (!$hour->duration) continue;

        $pivot = optional($hour->classRoom->teachers->first())->pivot;

        $wage = $pivot->hourly_wage ?? 0;

        $pendingSalary += ($hour->duration / 60) * $wage;
    }

    // Earnings Graph
    $monthlyData = ClassHour::with([
        'classRoom.teachers' => function ($q) use ($teacher) {
            $q->where('teacher_id',$teacher->id);
        }
    ])
    ->where('teacher_id',$teacher->id)
    ->where('status','completed')
    ->whereYear('class_started_at', now()->year)
    ->get()
    ->groupBy(function ($item) {
        return Carbon::parse($item->class_started_at)->format('M');
    });

    $chartLabels = [];
    $classCounts = [];
    $earnings = [];

    foreach ($monthlyData as $month => $hours) {

        $chartLabels[] = $month;

        $classCounts[] = $hours->count();

        $total = 0;

        foreach ($hours as $hour) {

            if (!$hour->duration) continue;

            $pivot = optional($hour->classRoom->teachers->first())->pivot;
            $wage = $pivot->hourly_wage ?? 0;

            $total += ($hour->duration / 60) * $wage;
        }

        $earnings[] = round($total,2);
    }

    return view('teacher.dashboard',compact(
        'teacher',
        'classes',
        'completedClasses',
        'totalHours',
        'thisMonthClasses',
        'earningsThisMonth',
        'salaries',
        'notes',
        'pendingSalary',
        'chartLabels','classCounts','earnings'
    ));
}

    public function profile()
    {
        $teacher = Auth::guard('teacher')->user();
        $classes = $teacher->classRooms()
            ->with(['course', 'classType'])
            ->get();

        return view('teacher.profile', compact('teacher', 'classes'));
    }
}
