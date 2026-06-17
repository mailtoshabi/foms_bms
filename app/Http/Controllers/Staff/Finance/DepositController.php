<?php

namespace App\Http\Controllers\Staff\Finance;

use App\Http\Controllers\Controller;
use App\Models\TeacherDeposit;
use App\Models\Teacher;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherDeposit::with('teacher');

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deposits = $query->latest()->paginate(utility('pagination', 50))->withQueryString();
        $teachers = Teacher::pluck('name', 'id');

        return view('staff.finance.teachers.deposits.index', compact('deposits', 'teachers'));
    }
}
