<?php

namespace App\Services;

use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;

class AttendanceService
{
    public function markStudent($data)
    {
        return StudentAttendance::create($data);
    }

    public function markTeacher($data)
    {
        return TeacherAttendance::create($data);
    }
}
