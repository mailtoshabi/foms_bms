<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TeacherExport implements FromCollection, WithHeadings
{
    protected $teachers;

    public function __construct(Collection $teachers)
    {
        $this->teachers = $teachers;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Phone',
            'Email',
            'Qualification',
            'Status',
            'Joined Date',
        ];
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
