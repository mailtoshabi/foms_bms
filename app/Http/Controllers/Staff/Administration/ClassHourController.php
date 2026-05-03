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
        $query = ClassHour::with(['classRoom.course', 'teacher']);

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
            $query->whereDate('join_teacher_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('join_teacher_at', '<=', $request->to_date);
        }

        $totalClassHours = $query->count();
        $totalDurationMins = (int) $query->sum('duration');

        $totalDurationHours = floor($totalDurationMins / 60);
        $remainingMins = $totalDurationMins % 60;
        $totalDurationFormatted = "{$totalDurationHours}h {$remainingMins}m";

        $data = $query->latest('join_teacher_at')->paginate(20)->withQueryString();

        $selectedClassName = $request->filled('class_room_id')
            ? optional(ClassRoom::find($request->class_room_id))->name
            : null;

        $selectedTeacherName = $request->filled('teacher_id')
            ? optional(\App\Models\Teacher::find($request->teacher_id))->name
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
