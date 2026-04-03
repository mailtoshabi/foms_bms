<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class StudentLeadExport implements FromCollection
{
    protected $leads;

    public function __construct(Collection $leads)
    {
        $this->leads = $leads;
    }

    public function collection()
    {
        return $this->leads->map(function ($lead) {
            return [
                'Name' => $lead->name,
                'Phone' => $lead->phone,
                'Status' => ucfirst($lead->status), // admitted / pending
                'Created Date' => $lead->created_at->format('Y-m-d'),
            ];
        });
    }
}
