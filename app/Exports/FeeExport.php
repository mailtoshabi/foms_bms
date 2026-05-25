<?php

namespace App\Exports;

use App\Models\Fee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class FeeExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $tab = $this->filters['tab'] ?? 'unpaid';

        $query = DB::table('fees')
            ->join('students','students.id','=','fees.student_id')
            ->join('class_rooms','class_rooms.id','=','fees.class_room_id')
            ->leftJoin('fee_payments','fee_payments.fee_id','=','fees.id')

            ->select(
                'students.name',
                'students.contact_number',
                'class_rooms.name as class_name',
                'fees.type',
                'fees.amount',
                DB::raw('COALESCE(SUM(fee_payments.paid_amount),0) as paid'),
                DB::raw('(fees.amount - COALESCE(SUM(fee_payments.paid_amount),0)) as pending'),
                'fees.due_date',
                'fees.status'
            )

            ->groupBy(
                'fees.id',
                'students.name',
                'students.contact_number',
                'class_rooms.name',
                'fees.type',
                'fees.amount',
                'fees.due_date',
                'fees.status'
            );

        /*
        |--------------------------------------------------------------------------
        | TAB LOGIC (same as fees())
        |--------------------------------------------------------------------------
        */

        $fourDaysAgo = now()->subDays(4)->endOfDay();

        if ($tab === 'paid') {
            $query->where('fees.status', 'paid');
        } elseif ($tab === 'overdue') {
            $query->where('fees.status', '<>', 'paid')
                ->whereDate('fees.due_date', '<', $fourDaysAgo);
        } else {
            // unpaid
            $query->where('fees.status', '<>', 'paid')
                ->whereDate('fees.due_date', '>=', $fourDaysAgo);
        }

        /*
        |--------------------------------------------------------------------------
        | FILTERS (same as fees())
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['class_room_id'])) {
            $query->where('fees.class_room_id', $this->filters['class_room_id']);
        }

        if (!empty($this->filters['type'])) {
            $query->where('fees.type', $this->filters['type']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('students.name', 'like', "%$search%")
                ->orWhere('students.contact_number', 'like', "%$search%");
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('fees.status', $this->filters['status']);
        }

        if (!empty($this->filters['from_date']) || !empty($this->filters['to_date'])) {
            if ($tab === 'paid') {
                if (!empty($this->filters['from_date'])) {
                    $query->whereDate('fee_payments.paid_date', '>=', $this->filters['from_date']);
                }
                if (!empty($this->filters['to_date'])) {
                    $query->whereDate('fee_payments.paid_date', '<=', $this->filters['to_date']);
                }
            } else {
                if (!empty($this->filters['from_date'])) {
                    $query->whereDate('fees.due_date', '>=', $this->filters['from_date']);
                }
                if (!empty($this->filters['to_date'])) {
                    $query->whereDate('fees.due_date', '<=', $this->filters['to_date']);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT: Keep or Remove this depending on requirement
        |--------------------------------------------------------------------------
        */

        // If you ONLY want pending in export, keep this:
        // $query->havingRaw('pending > 0');

        // If you want ALL (like UI), REMOVE it ❗

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Contact',
            'Class',
            'Total Fee',
            'Paid',
            'Pending',
            'Due Date',
            'Status'
        ];
    }
}
