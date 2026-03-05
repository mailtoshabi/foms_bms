<?php

namespace App\Services;

use App\Models\TeacherSalary;
use App\Models\StaffSalary;

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
}
