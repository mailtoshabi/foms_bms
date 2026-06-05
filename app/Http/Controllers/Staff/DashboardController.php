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
        $staff = auth()->guard('staff')->user();

        if (!$staff) {
            return redirect()->route('staff.login');
        }

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

        $fourDaysAgo = now()->subDays(4)->endOfDay();

        $overdueFeesQuery = Fee::where('status', '<>', 'paid')
            ->whereDate('due_date', '<', $fourDaysAgo);

        $overdueFeesCount = (clone $overdueFeesQuery)->count();
        $overdueFees      = (clone $overdueFeesQuery)->with('payments')->get();
            
        $overdueFeesAmount = $overdueFees->sum(function($fee) {
            return $fee->amount - $fee->payments->sum('paid_amount');
        });

        $unpaidTeacherSalariesCount  = TeacherSalary::whereIn('status', ['unpaid', 'partial'])->count();
        $unpaidTeacherSalariesAmount = TeacherSalary::whereIn('status', ['unpaid', 'partial'])->sum('total_amount');

        $topTeachers = topTeachers();

        $isFinanceDept = $staff->hasRoleId(utility('id_finance_dept')) || $staff->hasRoleId(utility('id_operation_dept'));
        $paidAmount = 0;
        $pendingAmount = 0;

        if ($isFinanceDept) {
            $paidAmount = \Illuminate\Support\Facades\DB::table('fee_payments')
                ->whereMonth('paid_date', $now->month)
                ->whereYear('paid_date', $now->year)
                ->sum('paid_amount');

            $totalFees = \Illuminate\Support\Facades\DB::table('fees')
                ->whereMonth('due_date', $now->month)
                ->whereYear('due_date', $now->year)
                ->sum('amount');

            $totalPaidAgainstFees = \Illuminate\Support\Facades\DB::table('fee_payments')
                ->join('fees', 'fees.id', '=', 'fee_payments.fee_id')
                ->whereMonth('fees.due_date', $now->month)
                ->whereYear('fees.due_date', $now->year)
                ->sum('fee_payments.paid_amount');

            $pendingAmount = max($totalFees - $totalPaidAgainstFees, 0);
        }

        return view('staff.dashboard.index', compact(
            'stats',
            'topTeachers',
            'pendingStudentLeads',
            'pendingTeacherLeads',
            'unpaidFeesCount',
            'unpaidFeesAmount',
            'overdueFeesCount',
            'overdueFeesAmount',
            'unpaidTeacherSalariesCount',
            'unpaidTeacherSalariesAmount',
            'isFinanceDept',
            'paidAmount',
            'pendingAmount'
        ));
    }

    public function profile()
    {
        $staff = auth()->guard('staff')->user();

        if (!$staff) {
            return redirect()->route('staff.login');
        }

        $staff->load('roles');

        return view('staff.profile', compact('staff'));
    }
}
