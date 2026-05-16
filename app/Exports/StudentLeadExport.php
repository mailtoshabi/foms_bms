<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentLeadExport implements FromCollection, WithHeadings
{
    protected $leads;

    public function __construct(Collection $leads)
    {
        $this->leads = $leads;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Phone',
            'Status',
            'Created Date',
        ];
    }

    public function collection()
    {
        return $this->leads->map(function ($lead) {
            return [
                'Name' => $lead->name,
                'Phone' => $lead->phone,
                'Status' => ucfirst($lead->status), // converted / pending
                'Created Date' => $lead->created_at->format('Y-m-d'),
            ];
        });
    }
}
