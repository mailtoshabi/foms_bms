<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class StudentExport implements FromCollection
{
    protected $students;

    public function __construct(Collection $students)
    {
        $this->students = $students;
    }

    public function collection()
    {
        return $this->students->map(function ($student) {
            return [
                'Admission No' => $student->admission_no,
                'Name' => $student->name,
                'Phone' => $student->contact_number,
                'Email' => $student->email,
                'Status' => ucfirst($student->status),
                'Joined Date' => $student->created_at->format('Y-m-d'),
            ];
        });
    }
}
