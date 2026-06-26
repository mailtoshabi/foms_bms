<?php

namespace App\Http\Controllers\Admin;

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
use App\Models\TeacherDeposit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Exports\FeeExport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{

    public function dashboard()
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | 💰 Current Month Finance
        |--------------------------------------------------------------------------
        */
        // session()->forget('salary_checked'); // --- FOR TESTING ONLY ---
        $totalFee = FeePayment::whereMonth('paid_date', $now->month)
            ->whereYear('paid_date', $now->year)
            ->sum('paid_amount');

        $totalRefund = \App\Models\FeeRefund::whereMonth('refund_date', $now->month)
            ->whereYear('refund_date', $now->year)
            ->sum('amount');

        $totalExpense =
            Expense::whereMonth('expense_date', $now->month)
                ->whereYear('expense_date', $now->year)
                ->sum('amount')

            +

            TeacherSalary::where('status', 'paid')
                ->whereMonth('payment_date', $now->month)
                ->whereYear('payment_date', $now->year)
                ->sum('total_amount')

            +

            StaffSalary::where('status', 'paid')
                ->whereMonth('paid_date', $now->month)
                ->whereYear('paid_date', $now->year)
                ->sum('salary_amount');


        /*
        |--------------------------------------------------------------------------
        | 📊 Stats
        |--------------------------------------------------------------------------
        |
        */

        $stats = [
            'students' => Student::count(),
            'staffs' => Staff::count(),
            'teachers' => Teacher::count(),
            'fee' => $totalFee,
            'expense' => $totalExpense,
            'refund' => $totalRefund,
            'balanceSheet' => $totalFee - $totalExpense - $totalRefund,
        ];

        /*
       |--------------------------------------------------------------------------
       | 📊 Monthly Graph Data (Last 12 Months)
       |--------------------------------------------------------------------------
       */

        $months = [];
        $fees = [];
        $expenses = [];

        for ($i = 11; $i >= 0; $i--) {

            $date = Carbon::now()->subMonths($i);

            $monthName = $date->format('M');

            $months[] = $monthName;

            $fees[] = FeePayment::whereMonth('paid_date', $date->month)
                ->whereYear('paid_date', $date->year)
                ->sum('paid_amount');

            $expenses[] =
                Expense::whereMonth('expense_date', $date->month)
                    ->whereYear('expense_date', $date->year)
                    ->sum('amount')

                +

                TeacherSalary::where('status', 'paid')
                    ->whereMonth('payment_date', $date->month)
                    ->whereYear('payment_date', $date->year)
                    ->sum('total_amount')

                +

                StaffSalary::where('status', 'paid')
                    ->whereMonth('paid_date', $date->month)
                    ->whereYear('paid_date', $date->year)
                    ->sum('salary_amount');
        }

        /*
        |--------------------------------------------------------------------------
        | 💰 PAID (Current Month)
        |--------------------------------------------------------------------------
        */

        $paidAmount = DB::table('fee_payments')
            ->whereMonth('paid_date', $now->month)
            ->whereYear('paid_date', $now->year)
            ->sum('paid_amount');


        /*
        |--------------------------------------------------------------------------
        | 💰 TOTAL FEES (Current Month)
        |--------------------------------------------------------------------------
        */

        $totalFees = DB::table('fees')
            ->whereMonth('due_date', $now->month)
            ->whereYear('due_date', $now->year)
            ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | 💰 TOTAL PAID AGAINST THOSE FEES
        |--------------------------------------------------------------------------
        */

        $totalPaidAgainstFees = DB::table('fee_payments')
            ->join('fees', 'fees.id', '=', 'fee_payments.fee_id')
            ->whereMonth('fees.due_date', $now->month)
            ->whereYear('fees.due_date', $now->year)
            ->sum('fee_payments.paid_amount');


        /*
        |--------------------------------------------------------------------------
        | 💰 PENDING
        |--------------------------------------------------------------------------
        */

        $pendingAmount = max($totalFees - $totalPaidAgainstFees, 0);

        $pendingStudentLeads = StudentLead::where('status', 'pending')->count();
        $pendingTeacherLeads = TeacherLead::where('status', 'pending')->count();
        $unpaidFeesCount = Fee::whereIn('status', ['unpaid', 'partial'])->count();
        $unpaidFeesAmount = Fee::whereIn('status', ['unpaid', 'partial'])->sum('amount');

        $fourDaysAgo = now()->subDays(4)->endOfDay();

        $overdueFeesQuery = Fee::where('status', '<>', 'paid')
            ->whereDate('due_date', '<', $fourDaysAgo);

        $overdueFeesCount = (clone $overdueFeesQuery)->count();
        $overdueFees = (clone $overdueFeesQuery)->with('payments')->get();

        $overdueFeesAmount = $overdueFees->sum(function ($fee) {
            return $fee->amount - $fee->payments->sum('paid_amount');
        });

        $unpaidTeacherSalariesCount = TeacherSalary::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('credit_date', '<=', now())
            ->count();
        $unpaidTeacherSalariesAmount = TeacherSalary::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('credit_date', '<=', now())
            ->sum('total_amount');

        $unpaidDepositsCount = TeacherDeposit::where('status', 'not paid')->count();
        $unpaidDepositsAmount = TeacherDeposit::where('status', 'not paid')->get()->sum(function ($d) {
            return $d->amount - $d->paid_amount;
        });

        $studentsWithBalanceCount = Student::where('wallet_balance', '>', 0)->count();
        $totalStudentWalletBalance = Student::sum('wallet_balance');

        $topTeachers = topTeachers();

        return view('admin.dashboard.index', compact(
            'stats',
            'topTeachers',
            'months',
            'fees',
            'expenses',
            'paidAmount',
            'pendingAmount',
            'pendingStudentLeads',
            'pendingTeacherLeads',
            'unpaidFeesCount',
            'unpaidFeesAmount',
            'overdueFeesCount',
            'overdueFeesAmount',
            'unpaidTeacherSalariesCount',
            'unpaidTeacherSalariesAmount',
            'unpaidDepositsCount',
            'unpaidDepositsAmount',
            'studentsWithBalanceCount',
            'totalStudentWalletBalance'
        ));
    }

    public function profile()
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        return view('admin.profile', compact('admin'));
    }

}
