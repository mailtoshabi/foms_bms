<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TeacherSalaryExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = DB::table('teacher_salaries')
            ->join('teachers','teachers.id','=','teacher_salaries.teacher_id')

            ->select(
                'teachers.name',
                'teachers.phone',
                'teacher_salaries.total_hours',
                'teacher_salaries.total_amount',

                DB::raw("CONCAT(
                    DATE_FORMAT(teacher_salaries.cycle_start, '%d %b'),
                    ' - ',
                    DATE_FORMAT(teacher_salaries.cycle_end, '%d %b %Y')
                ) as cycle"),

                'teacher_salaries.payment_date',
                'teacher_salaries.payment_method',
                'teacher_salaries.status'
            );

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['search'])) {
            // $query->where('teachers.name','like','%'.$this->filters['search'].'%');

            $query->where(function($q){
                $q->where('teachers.name','like','%'.$this->filters['search'].'%')
                ->orWhere('teachers.phone','like','%'.$this->filters['search'].'%');
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('teacher_salaries.status',$this->filters['status']);
        }

        if (!empty($this->filters['from_date']) && !empty($this->filters['to_date'])) {
            // $query->whereBetween('teacher_salaries.cycle_start', [
            //     $this->filters['from_date'],
            //     $this->filters['to_date']
            // ]);

            $query->where(function($q){
                $q->whereBetween('cycle_start', [$this->filters['from_date'], $this->filters['to_date']])
                ->orWhereBetween('cycle_end', [$this->filters['from_date'], $this->filters['to_date']]);
            });
        }

        return $query->orderByDesc('teacher_salaries.cycle_start')->get();
    }

    public function headings(): array
    {
        return [
            'Teacher Name',
            'Phone',
            'Total Hours',
            'Total Salary',
            'Cycle',
            'Payment Date',
            'Payment Method',
            'Status'
        ];
    }
}
