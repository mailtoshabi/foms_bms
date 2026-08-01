<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use App\Models\ClassHour;
use App\Models\ClassRoom;
use Illuminate\Http\Request;

class ClassHourController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassHour::query();

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->class_room_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('link_updated_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('link_updated_at', '<=', $request->to_date);
        }

        $totalClassHours = $query->count();
        $totalDurationMins = (int) $query->sum('duration');

        $totalDurationHours = floor($totalDurationMins / 60);
        $remainingMins = $totalDurationMins % 60;
        $totalDurationFormatted = "{$totalDurationHours}h {$remainingMins}m";

        $data = $query->select('id', 'class_room_id', 'teacher_id', 'duration', 'google_meet_link', 'status', 'link_updated_at', 'join_teacher_at', 'join_student_at', 'completed_at', 'created_at')
            ->with([
                'classRoom' => fn($q) => $q->select('id', 'name', 'course_id')
                    ->with(['course' => fn($qc) => $qc->select('id', 'name')]),
                'teacher' => fn($q) => $q->select('id', 'name')
            ])
            ->latest('created_at')
            ->paginate(utility('pagination', 20))
            ->withQueryString();

        $selectedClassName = $request->filled('class_room_id')
            ? optional(ClassRoom::select('id', 'name')->find($request->class_room_id))->name
            : null;

        $selectedTeacherName = $request->filled('teacher_id')
            ? optional(\App\Models\Teacher::select('id', 'name')->find($request->teacher_id))->name
            : null;

        return view('staff.reports.class_hours', compact(
            'data',
            'selectedClassName',
            'selectedTeacherName',
            'totalClassHours',
            'totalDurationFormatted'
        ));
    }
}
