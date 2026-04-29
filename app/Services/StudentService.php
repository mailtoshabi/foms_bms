<?php

namespace App\Services;

use App\Models\StudentLead;
use App\Models\Student;

class StudentService
{
    public function createLead(array $data)
    {
        return StudentLead::create($data);
    }

    public function convertToStudent($leadId, array $studentData)
    {
        $lead = StudentLead::findOrFail($leadId);

        $student = Student::create(array_merge(
            ['student_lead_id' => $lead->id],
            $studentData
        ));

        $lead->update(['status' => 'converted']);

        return $student;
    }
}
