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
        ->whereMonth('join_teacher_at', now()->month)
        ->whereYear('join_teacher_at', now()->year)
        ->count();

    // =========================
    // EARNINGS (THIS MONTH)
    // =========================

    // Latest Earnings
    $latestEarnings = TeacherSalary::where('teacher_id', $teacher->id)
        ->latest('cycle_start')
        ->value('total_amount') ?? 0;

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

    //Upcoming Salary (unprocessed completed class hours)
    $upcomingSalary = ClassHour::where('teacher_id', $teacher->id)
        ->where('status', 'completed')
        ->where('has_salary_calculated', false)
        ->whereNotNull('duration')
        ->selectRaw('SUM((duration / 60) * hourly_wage) as total')
        ->value('total') ?? 0;

    // $pendingSalary = round($upcomingSalary, 2);
    //Pending Salary
    $pendingSalary = TeacherSalary::where('teacher_id', $teacher->id)
        ->where('status', 'unpaid')
        ->sum('total_amount');

    // Earnings Graph
    $monthlyData = ClassHour::where('teacher_id',$teacher->id)
    ->where('status','completed')
    ->whereYear('join_teacher_at', now()->year)
    ->get()
    ->groupBy(function ($item) {
        return Carbon::parse($item->join_teacher_at)->format('M');
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

            $wage = $hour->hourly_wage ?? 0;

            $total += ($hour->duration / 60) * $wage;
        }

        $earnings[] = round($total,2);
    }

    return view('teacher.dashboard',compact(

        'classes',
        'completedClasses',
        'totalHours',
        'thisMonthClasses',
        'latestEarnings',
        'upcomingSalary',
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
