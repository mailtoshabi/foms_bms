<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherSalary;
use App\Models\Teacher;
use App\Models\TeacherDeposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'unpaid');

        $query = TeacherSalary::with('teacher');

        if ($tab === 'paid') {
            $query->where('status', 'paid');
        } else {
            $query->whereIn('status', ['unpaid', 'deposit']);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('cycle_start', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('cycle_end', '<=', $request->to_date);
        }

        $salaries = $query->latest()->paginate(utility('pagination', 50))->withQueryString();

        $teachers = Teacher::pluck('name', 'id');

        $counts = TeacherSalary::selectRaw("
            SUM(status = 'unpaid' OR status = 'deposit') as unpaid,
            SUM(status = 'paid') as paid
        ")->first();

        $unpaidCount = $counts->unpaid;
        $paidCount = $counts->paid;

        return view(
            'admin.salaries.index',
            compact('salaries', 'teachers', 'tab', 'unpaidCount', 'paidCount')
        );
    }

    public function pay(Request $request)
    {
        $validated = $request->validate([
            'salary_id' => 'required|exists:teacher_salaries,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required',
        ]);

        $salary = TeacherSalary::findOrFail($validated['salary_id']);

        $salary->update([
            'status' => 'paid',
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Salary marked as paid');
    }

    public function moveToDeposit(TeacherSalary $salary)
    {
        if ($salary->status !== 'unpaid') {
            return back()->with('error', 'Salary is not unpaid or already processed.');
        }

        $isFirst = !TeacherSalary::where('teacher_id', $salary->teacher_id)
            ->where('id', '<', $salary->id)
            ->exists();

        if (!$isFirst) {
            return back()->with('error', 'Only the first month salary can be moved to deposit.');
        }

        DB::transaction(function () use ($salary) {
            $salary->update(['status' => 'deposit']);

            TeacherDeposit::create([
                'teacher_id' => $salary->teacher_id,
                'teacher_salary_id' => $salary->id,
                'amount' => $salary->total_amount,
                'deposited_date' => now()->toDateString(),
                'due_date' => now()->addMonths(6)->toDateString(),
                'status' => 'not paid',
            ]);
        });

        return back()->with('success', 'First month salary successfully moved to deposit.');
    }

    public function releaseDeposit(TeacherSalary $salary)
    {
        if ($salary->status !== 'deposit') {
            return back()->with('error', 'Salary is not in deposit status.');
        }

        $deposit = TeacherDeposit::where('teacher_salary_id', $salary->id)->first();
        if ($deposit && $deposit->paid_amount > 0) {
            return back()->with('error', 'Cannot release deposit as payment has already been made on it.');
        }

        DB::transaction(function () use ($salary) {
            $salary->update(['status' => 'unpaid']);
            TeacherDeposit::where('teacher_salary_id', $salary->id)->delete();
        });

        return back()->with('success', 'Deposit successfully released back to salary.');
    }
}
