<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ClassRoom;

class ReportController extends Controller
{
    public function attendance(Request $request)
    {
        $query = DB::table('student_attendance')
            ->join('students', 'students.id', '=', 'student_attendance.student_id')
            ->leftJoin('countries', 'countries.id', '=', 'students.country_id')
            ->join('class_hours', 'class_hours.id', '=', 'student_attendance.class_hour_id')
            ->join('class_rooms', 'class_rooms.id', '=', 'class_hours.class_room_id')

            ->select(
                'students.name',
                'students.id',
                DB::raw("IF(countries.id IS NOT NULL, CONCAT(countries.code, ' (', countries.name, ') ', students.contact_number), students.contact_number) as contact_number"),
                'students.whatsapp_number',
                'students.is_whatsapp_different',
                'class_rooms.name as class_name',
                'class_hours.link_updated_at',
                'student_attendance.is_present',
                'student_attendance.created_at'
            );

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('students.name', 'like', "%$search%")
                    ->orWhere('students.contact_number', 'like', "%$search%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('class_hours.link_updated_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('class_hours.link_updated_at', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $query->where('student_attendance.is_present', $request->status);
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_rooms.id', $request->class_room_id);
        }

        $hasFilters = $request->anyFilled(['search', 'from_date', 'to_date', 'status', 'class_room_id']);
        $summary = null;

        if ($hasFilters) {
            $summary = [
                'total' => (clone $query)->count(),
                'present' => (clone $query)->where('student_attendance.is_present', 1)->count(),
                'absent' => (clone $query)->where('student_attendance.is_present', 0)->count(),
            ];
        }

        $data = $query->latest('class_hours.link_updated_at')->paginate(utility('pagination', 50))->withQueryString();

        $selectedClassName = $request->filled('class_room_id')
            ? optional(ClassRoom::find($request->class_room_id))->name
            : null;

        $classRoomSearchUrl = route('staff.class_rooms.search');

        return view('staff.reports.attendance', compact('data', 'hasFilters', 'summary', 'selectedClassName', 'classRoomSearchUrl'));
    }
}
