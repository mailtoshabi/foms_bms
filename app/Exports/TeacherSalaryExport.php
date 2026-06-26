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

                'teacher_salaries.credit_date',
                'teacher_salaries.payment_date',
                'teacher_salaries.payment_method',
                'teacher_salaries.status'
            );

        $tab = $this->filters['tab'] ?? 'unpaid';

        /*
        |--------------------------------------------------------------------------
        | Tab Logic
        |--------------------------------------------------------------------------
        |*/

        if ($tab === 'paid') {
            $query->where('teacher_salaries.status', 'paid');
        } else {
            // Unpaid or Partial
            $query->where('teacher_salaries.status', '<>', 'paid');
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        |*/

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

        $dateType = $this->filters['date_type'] ?? 'cycle_date';

        if (!empty($this->filters['from_date']) && !empty($this->filters['to_date'])) {
            $from = $this->filters['from_date'];
            $to = $this->filters['to_date'];
            if ($dateType === 'credit_date') {
                $query->whereBetween('teacher_salaries.credit_date', [$from, $to]);
            } else {
                $query->where(function($q) use ($from, $to) {
                    $q->whereBetween('cycle_start', [$from, $to])
                    ->orWhereBetween('cycle_end', [$from, $to]);
                });
            }
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
            'Credit Date',
            'Payment Date',
            'Payment Method',
            'Status'
        ];
    }
}
