<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = DB::table('student_attendance')
            ->join('students','students.id','=','student_attendance.student_id')
            ->join('class_hours','class_hours.id','=','student_attendance.class_hour_id')
            ->join('class_rooms','class_rooms.id','=','class_hours.class_room_id')

            ->select(
                'students.name',
                'class_rooms.name as class_name',
                'class_hours.class_started_at',
                DB::raw("CASE WHEN student_attendance.is_present = 1 THEN 'Present' ELSE 'Absent' END as status")
            );

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('students.name', 'like', "%$search%")
                ->orWhere('students.contact_number', 'like', "%$search%");
            });
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('student_attendance.is_present',$this->filters['status']);
        }

        if (!empty($this->filters['date'])) {
            $query->whereDate('class_hours.class_started_at',$this->filters['date']);
        }

        return $query->orderByDesc('class_hours.class_started_at')->get();
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Class',
            'Date',
            'Status'
        ];
    }
}
