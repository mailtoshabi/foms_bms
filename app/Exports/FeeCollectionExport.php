<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FeeCollectionExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = DB::table('fee_payments')
            ->join('fees','fees.id','=','fee_payments.fee_id')
            ->join('students','students.id','=','fees.student_id')
            ->join('class_rooms','class_rooms.id','=','fees.class_room_id')
            ->join('courses','courses.id','=','class_rooms.course_id')
            ->join('course_categories','course_categories.id','=','courses.category_id')

            ->select(
                'students.name',
                'students.contact_number',
                'class_rooms.name as class_name',
                'course_categories.name as category_name',
                'fee_payments.paid_amount',
                'fee_payments.payment_method',
                'fee_payments.paid_date'
            );

        /*
        |--------------------------------------------------------------------------
        | Apply Filters
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['search'])) {
            $query->where('students.name','like','%'.$this->filters['search'].'%');
        }

        if (!empty($this->filters['payment_method'])) {
            $query->where('fee_payments.payment_method',$this->filters['payment_method']);
        }

        if (!empty($this->filters['from_date'])) {
            $query->whereDate('fee_payments.paid_date', '>=', $this->filters['from_date']);
        }

        if (!empty($this->filters['to_date'])) {
            $query->whereDate('fee_payments.paid_date', '<=', $this->filters['to_date']);
        }

        if (!empty($this->filters['category_id'])) {
            $query->where('course_categories.id',$this->filters['category_id']);
        }

        if (!empty($this->filters['class_room_id'])) {
            $query->where('class_rooms.id',$this->filters['class_room_id']);
        }

        return $query->orderByDesc('fee_payments.paid_date')->get();
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Contact',
            'Class',
            'Category',
            'Amount',
            'Payment Method',
            'Paid Date'
        ];
    }
}
