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

        if (!$teacher) {
            return redirect()->route('teacher.login');
        }

        // Assigned Classes
        $classes = $teacher->classRooms()
            ->active()
            ->with(['course', 'classType'])
            ->get();

        // Monthly Completed Sessions
        $completedSessions = ClassHour::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();

        // Monthly Pending Sessions
        $pendingSessions = ClassHour::where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->whereMonth('link_updated_at', now()->month)
            ->whereYear('link_updated_at', now()->year)
            ->count();

        // Total Hours
        $totalMinutes = ClassHour::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('duration');

        $totalHours = round($totalMinutes / 60, 2);

        // =========================
        // EARNINGS (THIS MONTH)
        // =========================

        // Latest Earnings
        $latestEarnings = TeacherSalary::where('teacher_id', $teacher->id)
            ->whereMonth('cycle_start', now()->month)
            ->whereYear('cycle_start', now()->year)
            ->sum('total_amount') ?? 0;

        // Salary history
        $salaries = TeacherSalary::where('teacher_id', $teacher->id)
            ->latest()
            ->take(12)
            ->get();

        // Latest class notes
        $notes = ClassNote::where('teacher_id', $teacher->id)
            ->latest()
            ->take(12)
            ->get();

        // This Month Notes Count
        $thisMonthNotesCount = ClassNote::where('teacher_id', $teacher->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

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
        $monthlyData = ClassHour::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->whereYear('completed_at', now()->year)
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->completed_at)->format('M');
            });

        $chartLabels = [];
        $classCounts = [];
        $earnings = [];

        foreach ($monthlyData as $month => $hours) {

            $chartLabels[] = $month;

            $classCounts[] = $hours->count();

            $total = 0;

            foreach ($hours as $hour) {

                if (!$hour->duration)
                    continue;

                $wage = $hour->hourly_wage ?? 0;

                $total += ($hour->duration / 60) * $wage;
            }

            $earnings[] = round($total, 2);
        }

        return view('teacher.dashboard', compact(

            'classes',
            'completedSessions',
            'pendingSessions',
            'totalHours',
            'thisMonthNotesCount',
            'latestEarnings',
            'upcomingSalary',
            'salaries',
            'notes',
            'pendingSalary',
            'chartLabels',
            'classCounts',
            'earnings'
        ));
    }

    public function profile()
    {
        $teacher = Auth::guard('teacher')->user();

        if (!$teacher) {
            return redirect()->route('teacher.login');
        }

        $classes = $teacher->classRooms()
            ->active()
            ->with(['course', 'classType'])
            ->get();

        return view('teacher.profile', compact('teacher', 'classes'));
    }
}
