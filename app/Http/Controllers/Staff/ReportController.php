<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function attendance(Request $request)
    {
        $query = DB::table('student_attendance')
            ->join('students', 'students.id', '=', 'student_attendance.student_id')
            ->join('class_hours', 'class_hours.id', '=', 'student_attendance.class_hour_id')
            ->join('class_rooms', 'class_rooms.id', '=', 'class_hours.class_room_id')

            ->select(
                'students.name',
                'students.contact_number',
                'students.whatsapp_number',
                'students.is_whatsapp_different',
                'class_rooms.name as class_name',
                'class_hours.class_started_at',
                'student_attendance.is_present'
            );

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('students.name', 'like', "%$search%")
                    ->orWhere('students.contact_number', 'like', "%$search%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('class_hours.class_started_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('class_hours.class_started_at', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $query->where('student_attendance.is_present', $request->status);
        }

        $data = $query->latest('class_hours.class_started_at')->paginate(10)->withQueryString();

        return view('staff.reports.attendance', compact('data'));
    }
}
