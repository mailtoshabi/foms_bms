<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\FeePayment;
use App\Models\Expense;

class DashboardController extends Controller
{
    public function dashboard()
    {

        // dd(auth()->guard('staff')->user());

        // 💰 Finance totals (query ONCE)
        $totalFee     = FeePayment::sum('paid_amount');
        $totalExpense = Expense::sum('amount');

        // 📊 Stats array
        $stats = [
            'students'     => Student::count(),
            'staffs'        => Staff::count(),
            'teachers'     => Teacher::count(),
            'fee'          => $totalFee,
            'expense'      => $totalExpense,
            'balanceSheet' => $totalFee - $totalExpense,
        ];

        return view('staff.dashboard.index', compact('stats'));
    }
}
