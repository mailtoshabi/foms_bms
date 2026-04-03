<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class TeacherExport implements FromCollection
{
    protected $teachers;

    public function __construct(Collection $teachers)
    {
        $this->teachers = $teachers;
    }

    public function collection()
    {
        return $this->teachers->map(function ($teacher) {
            return [
                'Name' => $teacher->name,
                'Phone' => $teacher->contact_number,
                'Email' => $teacher->email,
                'Qualification' => $teacher->qualification,
                'Status' => ucfirst($teacher->status),
                'Joined Date' => $teacher->created_at->format('Y-m-d'),
            ];
        });
    }
}
