<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherSalary;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SalaryController extends Controller
{

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'unpaid');

        $query = TeacherSalary::with('teacher');

        if ($tab === 'paid') {
            $query->where('status', 'paid');
        } else {
            $query->where('status', 'unpaid');
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
            SUM(status = 'unpaid') as unpaid,
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
}
