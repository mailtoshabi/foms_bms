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

        // ✅ Safe cycle day
        $cycleDay = min($teacher->salary_cycle_day, $today->daysInMonth);

        // ❗ ONLY RUN ON EXACT CYCLE DAY
        if ($today->day != $cycleDay) {
            return;
        }

        // ✅ Current cycle start (this month)
        $cycleStart = $today->copy()->day($cycleDay)->startOfDay();

        // ✅ Previous cycle start
        $previousCycleStart = $cycleStart->copy()->subMonth();

        // ✅ Cycle end = one day before current cycle start
        $cycleEnd = $cycleStart->copy()->subDay()->endOfDay();

        // ✅ Actual cycle start = previous cycle start
        $cycleStart = $previousCycleStart->startOfDay();

        // ❗ Prevent duplicate salary
        $exists = TeacherSalary::where('teacher_id', $teacher->id)
            ->whereDate('cycle_start', $cycleStart)
            ->whereDate('cycle_end', $cycleEnd)
            ->exists();

        if ($exists) {
            return;
        }

        // ✅ Fetch class hours within cycle
        $classHours = ClassHour::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->where('has_salary_calculated', false)
            ->whereBetween('link_updated_at', [
                $cycleStart,
                $cycleEnd
            ])
            ->get();

        if ($classHours->isEmpty()) {
            return;
        }

        $totalAmount = 0;
        $totalHours = 0;

        foreach ($classHours as $hour) {

            if (!$hour->duration)
                continue;

            $wage = $hour->hourly_wage ?? 0;

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
                'total_amount' => round($totalAmount, 2),
                'credit_date' => $cycleEnd->copy()->addDays(11),
            ]);

            ClassHour::whereIn('id', $classHours->pluck('id'))
                ->update(['has_salary_calculated' => true]);

        });
    }
}
