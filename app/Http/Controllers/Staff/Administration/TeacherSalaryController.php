<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherSalary;
use Illuminate\Http\Request;

class TeacherSalaryController extends Controller
{

    public function create(Teacher $teacher)
    {
        return view('staff.teacher_salaries.create',compact('teacher'));
    }

    public function store(Request $request, Teacher $teacher)
    {

    $request->validate([
    'amount'=>'required|numeric',
    'payment_date'=>'required|date'
    ]);

    TeacherSalary::create([
    'teacher_id'=>$teacher->id,
    'amount'=>$request->amount,
    'payment_method'=>$request->payment_method,
    'payment_date'=>$request->payment_date,
    'notes'=>$request->notes
    ]);

    return back()->with('success','Salary added');

    }


    public function update(Request $request, TeacherSalary $salary)
    {

    $salary->update($request->only(
    'amount',
    'payment_method',
    'payment_date',
    'notes'
    ));

    return back()->with('success','Salary updated');

    }

    public function destroy(TeacherSalary $salary)
    {

    $salary->delete();

    return back()->with('success','Salary deleted');

    }
}
