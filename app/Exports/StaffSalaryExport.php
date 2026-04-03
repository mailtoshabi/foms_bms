<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StaffSalaryExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = DB::table('staff_salaries')
            ->join('staffs', 'staffs.id', '=', 'staff_salaries.staff_id')
            ->leftJoin('staff_salary_payments', 'staff_salary_payments.staff_salary_id', '=', 'staff_salaries.id')
            ->select(
                'staffs.name',
                'staffs.phone',
                'staff_salaries.salary_month',
                'staff_salaries.salary_amount',
                DB::raw('COALESCE(SUM(staff_salary_payments.paid_amount), 0) as paid_amount'),
                DB::raw('staff_salaries.salary_amount - COALESCE(SUM(staff_salary_payments.paid_amount), 0) as balance_due'),
                'staff_salaries.status',
                'staff_salaries.paid_date'
            )
            ->groupBy(
                'staffs.name',
                'staffs.phone',
                'staff_salaries.id',
                'staff_salaries.salary_month',
                'staff_salaries.salary_amount',
                'staff_salaries.status',
                'staff_salaries.paid_date'
            );

        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('staffs.name', 'like', '%' . $this->filters['search'] . '%')
                    ->orWhere('staffs.phone', 'like', '%' . $this->filters['search'] . '%');
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('staff_salaries.status', $this->filters['status']);
        }

        if (!empty($this->filters['from_month'])) {
            $query->where('staff_salaries.salary_month', '>=', $this->filters['from_month']);
        }

        if (!empty($this->filters['to_month'])) {
            $query->where('staff_salaries.salary_month', '<=', $this->filters['to_month']);
        }

        return $query->orderByDesc('staff_salaries.salary_month')->get()->map(function ($row) {
            return [
                'Staff' => $row->name,
                'Phone' => $row->phone,
                'Salary Month' => $row->salary_month,
                'Salary Amount' => $row->salary_amount,
                'Paid Amount' => $row->paid_amount,
                'Balance Due' => $row->balance_due,
                'Status' => ucfirst(str_replace('_', ' ', $row->status)),
                'Paid Date' => $row->paid_date,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Staff',
            'Phone',
            'Salary Month',
            'Salary Amount',
            'Paid Amount',
            'Balance Due',
            'Status',
            'Paid Date',
        ];
    }
}
