<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::latest('date')->paginate(20);
        $routePrefix = Auth::guard('admin')->check() ? 'admin' : 'staff';
        return view('staff.holidays.index', compact('holidays', 'routePrefix'));
    }

    public function create()
    {
        $teachers = Teacher::where('status', 'active')->orderBy('name', 'asc')->get();
        $students = Student::where('status', 'active')->orderBy('name', 'asc')->get();
        $classes = ClassRoom::active()->orderBy('name', 'asc')->get();
        $routePrefix = Auth::guard('admin')->check() ? 'admin' : 'staff';

        return view('staff.holidays.create', compact('teachers', 'students', 'classes', 'routePrefix'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'target_type' => 'required|string|in:all_teachers,selected_teachers,all_students,selected_students,classes',
            'class_target_type' => 'required_if:target_type,classes|nullable|string|in:teachers,students,both',
            'teacher_ids' => 'required_if:target_type,selected_teachers|array',
            'teacher_ids.*' => 'exists:teachers,id',
            'student_ids' => 'required_if:target_type,selected_students|array',
            'student_ids.*' => 'exists:students,id',
            'class_ids' => 'required_if:target_type,classes|array',
            'class_ids.*' => 'exists:class_rooms,id',
        ]);

        $holiday = Holiday::create([
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'target_type' => $request->target_type,
            'class_target_type' => $request->target_type == 'classes' ? $request->class_target_type : null,
            'created_by' => Auth::guard('staff')->id() ?? Auth::guard('admin')->id(),
        ]);

        if ($request->target_type == 'selected_teachers') {
            $holiday->teachers()->attach($request->teacher_ids);
        } elseif ($request->target_type == 'selected_students') {
            $holiday->students()->attach($request->student_ids);
        } elseif ($request->target_type == 'classes') {
            $holiday->classRooms()->attach($request->class_ids);
        }

        $routePrefix = Auth::guard('admin')->check() ? 'admin' : 'staff';
        return redirect()
            ->route($routePrefix . '.holidays.index')
            ->with('success', 'Holiday notification created successfully.');
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        $routePrefix = Auth::guard('admin')->check() ? 'admin' : 'staff';
        return redirect()
            ->route($routePrefix . '.holidays.index')
            ->with('success', 'Holiday notification deleted successfully.');
    }
}
