<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class StaffExport implements FromCollection
{
    protected $staffs;

    public function __construct(Collection $staffs)
    {
        $this->staffs = $staffs;
    }

    public function collection()
    {
        return $this->staffs->map(function ($staff) {
            return [
                'Name' => $staff->name,
                'Phone' => $staff->phone,
                'Email' => $staff->email,
                'Salary Amount' => $staff->salary_amount,
                'Joined Date' => optional($staff->created_at)->format('Y-m-d'),
            ];
        });
    }
}
