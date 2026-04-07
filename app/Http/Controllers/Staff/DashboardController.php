<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\Expense;
use App\Models\StudentLead;
use App\Models\TeacherLead;
use App\Models\StaffSalary;
use App\Models\TeacherSalary;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $now = Carbon::now();

    /*
    |--------------------------------------------------------------------------
    | 💰 Current Month Finance
    |--------------------------------------------------------------------------
    */

    $totalFee = FeePayment::whereMonth('paid_date', $now->month)
        ->whereYear('paid_date', $now->year)
        ->sum('paid_amount');

    $totalExpense =
        Expense::whereMonth('expense_date', $now->month)
            ->whereYear('expense_date', $now->year)
            ->sum('amount')

        +

        TeacherSalary::where('status','paid')
            ->whereMonth('payment_date', $now->month)
            ->whereYear('payment_date', $now->year)
            ->sum('total_amount')

        +

        StaffSalary::where('status','paid')
            ->whereMonth('paid_date', $now->month)
            ->whereYear('paid_date', $now->year)
            ->sum('salary_amount');


    /*
    |--------------------------------------------------------------------------
    | 📊 Stats
    |--------------------------------------------------------------------------
    */

        $stats = [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'fee'      => $totalFee,
            'expense'  => $totalExpense,
        ];

        $pendingStudentLeads         = StudentLead::where('status', 'pending')->count();
        $pendingTeacherLeads         = TeacherLead::where('status', 'pending')->count();
        $unpaidFeesCount             = Fee::whereIn('status', ['unpaid', 'partial'])->count();
        $unpaidFeesAmount            = Fee::whereIn('status', ['unpaid', 'partial'])->sum('amount');
        $unpaidTeacherSalariesCount  = TeacherSalary::whereIn('status', ['unpaid', 'partial'])->count();
        $unpaidTeacherSalariesAmount = TeacherSalary::whereIn('status', ['unpaid', 'partial'])->sum('total_amount');

        $topTeachers = topTeachers();

        return view('staff.dashboard.index', compact(
            'stats',
            'topTeachers',
            'pendingStudentLeads',
            'pendingTeacherLeads',
            'unpaidFeesCount',
            'unpaidFeesAmount',
            'unpaidTeacherSalariesCount',
            'unpaidTeacherSalariesAmount'
        ));
    }

    public function profile()
    {
        $staff = auth()->guard('staff')->user();
        $staff->load('roles');

        return view('staff.profile', compact('staff'));
    }
}
