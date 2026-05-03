<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherSalary;
use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\ClassHour;
use Illuminate\Support\Facades\DB;

class TeacherSalaryController extends Controller
{
    public function processTeacherSalary($teacherId)
    {
        $teacherId = decrypt($teacherId);

        $teacher = Teacher::findOrFail($teacherId);

        if (!$teacher->salary_cycle_day) {
            return;
        }

        $today = now();

        // Determine cycle end
        $cycleDay = $teacher->salary_cycle_day;

        if ($today->day >= $cycleDay) {
            $cycleEnd = Carbon::create($today->year, $today->month, $cycleDay);
        } else {
            $cycleEnd = Carbon::create($today->year, $today->month, $cycleDay)->subMonth();
        }

        $cycleStart = $cycleEnd->copy()->subMonth()->addDay();

        // Get class hours in cycle
        $classHours = ClassHour::with('classRoom')
            ->where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->where('has_salary_calculated', false)
            ->whereBetween('updated_at', [
                $cycleStart->startOfDay(),
                $cycleEnd->endOfDay()
            ])
            ->get();



        // return  $cycleEnd->endOfDay();

        if ($classHours->isEmpty()) {
            return;
        }

        $totalAmount = 0;
        $totalHours = 0;

        foreach ($classHours as $hour) {

            // Get wage from pivot
            $pivot = $hour->classRoom->teachers()
                ->where('teacher_id', $teacher->id)
                ->first()
                ->pivot;

            $wage = $pivot->hourly_wage ?? 0;

            $duration = $hour->duration ?? 0; // minutes

            $hours = $duration / 60;

            $totalHours += $hours;
            $totalAmount += $hours * $wage;
        }

        DB::transaction(function () use ($teacher, $cycleStart, $cycleEnd, $totalHours, $totalAmount, $classHours) {

            TeacherSalary::create([
                'teacher_id' => $teacher->id,
                'cycle_start' => $cycleStart,
                'cycle_end' => $cycleEnd,
                'total_hours' => $totalHours,
                'total_amount' => $totalAmount,
            ]);

            // Mark processed
            ClassHour::whereIn('id', $classHours->pluck('id'))
                ->update(['has_salary_calculated' => true]);

        });

        return back()->with('success', 'Salary Updated successfully');
    }

    public function create(Teacher $teacher)
    {
        return view('staff.teacher_salaries.create', compact('teacher'));
    }

    public function store(Request $request, Teacher $teacher)
    {

        $request->validate([
            'amount' => 'required|numeric',
            'payment_date' => 'required|date'
        ]);

        TeacherSalary::create([
            'teacher_id' => $teacher->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes
        ]);

        return back()->with('success', 'Salary added');

    }


    public function update(Request $request, TeacherSalary $salary)
    {
        $request->validate([
            'total_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'payment_date' => 'nullable|date',
            'status' => 'nullable|in:unpaid,paid',
            'notes' => 'nullable|string|max:1000',
        ]);

        $salary->update($request->only(
            'total_amount',
            'payment_method',
            'payment_date',
            'status',
            'notes'
        ));

        return back()->with('success', 'Salary updated');

    }

    public function destroy(TeacherSalary $salary)
    {

        $salary->delete();

        return back()->with('success', 'Salary deleted');

    }
}
