<?php

namespace App\Services;

use App\Models\StaffSalary;
use Carbon\Carbon;
use App\Models\ClassHour;
use App\Models\Teacher;
use App\Models\TeacherSalary;
use Illuminate\Support\Facades\DB;

class SalaryService
{
    public function createTeacherSalary($data)
    {
        return TeacherSalary::create($data);
    }

    public function createStaffSalary($data)
    {
        return StaffSalary::create($data);
    }

    public function processTeacherSalary($teacherId)
    {
        $teacher = Teacher::findOrFail($teacherId);

        if (!$teacher->salary_cycle_day) {
            return;
        }

        $today = now();

        // ✅ Safe cycle day (handles Feb / short months)
        $cycleDay = min($teacher->salary_cycle_day, $today->daysInMonth);

        if ($today->day >= $cycleDay) {
            $cycleEnd = $today->copy()->day($cycleDay);
        } else {
            $cycleEnd = $today->copy()->subMonth()->day($cycleDay);
        }

        $cycleStart = $cycleEnd->copy()->subMonth()->addDay();

        // 🔴 IMPORTANT FIX: Ensure full cycle day completed
        if (now()->lt($cycleEnd->copy()->endOfDay())) {
            return;
        }

        // ❗ Prevent duplicate salary for same cycle
        $exists = TeacherSalary::where('teacher_id', $teacher->id)
            ->whereDate('cycle_start', $cycleStart)
            ->whereDate('cycle_end', $cycleEnd)
            ->exists();

        if ($exists) {
            return;
        }

        // ✅ Load class hours with pivot (NO N+1)
        $classHours = ClassHour::with([
            'classRoom.teachers' => function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            }
        ])
        ->where('teacher_id', $teacher->id)
        ->where('status','completed')
        ->where('has_salary_calculated', false)
        ->whereBetween('class_started_at', [
            $cycleStart->startOfDay(),
            $cycleEnd->endOfDay()
        ])
        ->get();

        if ($classHours->isEmpty()) {
            return;
        }

        $totalAmount = 0;
        $totalHours = 0;

        foreach ($classHours as $hour) {

            if (!$hour->duration) continue;

            $pivot = optional($hour->classRoom->teachers->first())->pivot;

            $wage = $pivot->hourly_wage ?? 0;

            $hours = $hour->duration / 60;

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

            // ✅ Mark processed class hours
            ClassHour::whereIn('id', $classHours->pluck('id'))
                ->update(['has_salary_calculated' => true]);

        });
    }
}
