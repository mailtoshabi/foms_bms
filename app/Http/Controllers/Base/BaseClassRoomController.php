<?php

namespace App\Http\Controllers\Base;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\ClassType;
use App\Http\Controllers\Base\BaseServiceController;
use App\Models\Student;
use App\Models\Teacher;

class BaseClassRoomController extends BaseServiceController
{

    protected $viewPrefix;
    protected $routePrefix;

    public function index(Request $request)
    {
        $query = ClassRoom::with(['course', 'classType'])
            ->orderBy('is_completed', 'asc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->filled('class_type_id')) {
            $query->where('class_type_id', $request->class_type_id);
        }

        if ($request->filled('class_room_id')) {
            $query->where('id', $request->class_room_id);
        }

        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_completed', false);
            }

            if ($request->status == 'completed') {
                $query->where('is_completed', true);
            }
        }

        $class_rooms = $query->paginate(utility('pagination', 50))->withQueryString();

        $courses = Course::all();
        $types = ClassType::all();

        $classRoomSearchUrl = route($this->routePrefix . '.search');
        $selectedClassName = $request->filled('class_room_id')
            ? optional(ClassRoom::find($request->class_room_id))->name
            : null;

        return view(
            $this->viewPrefix . '.index',
            compact('class_rooms', 'courses', 'types', 'classRoomSearchUrl', 'selectedClassName')
        );
    }


    public function create()
    {
        $courses = Course::all();
        $types = ClassType::all();

        return view($this->viewPrefix . '.create', compact('courses', 'types'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'class_type_id' => 'required|exists:class_types,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('class_rooms', 'name')
                    ->where('course_id', $request->course_id),
            ],

            'classes_per_week' => 'nullable|integer|min:0',
            'selected_days' => 'nullable|array',
            'selected_days.*' => 'string',

            'time_slot' => 'nullable|string|max:255',
            'slot_duration' => 'required|integer|min:1',

            'admission_fee' => 'nullable|numeric|min:0',
            'monthly_fee' => 'nullable|numeric|min:0',

            // 'starting_date'    => 'nullable|date',
            'is_completed' => 'nullable|boolean',
        ]);

        $this->classService->create($validated);

        return redirect()->route($this->routePrefix . '.index')
            ->with('success', 'Class created successfully');
    }


    public function edit($id)
    {
        $class = ClassRoom::findOrFail(decrypt($id));

        $courses = Course::all();
        $types = ClassType::all();

        return view(
            $this->viewPrefix . '.create',
            compact('class', 'courses', 'types')
        );
    }


    public function update(Request $request)
    {
        $classId = decrypt($request->class_room_id);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'class_type_id' => 'required|exists:class_types,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('class_rooms', 'name')
                    ->where('course_id', $request->course_id)
                    ->ignore($classId),
            ],

            'classes_per_week' => 'nullable|integer|min:0',
            'selected_days' => 'nullable|array',
            'selected_days.*' => 'string',

            'time_slot' => 'nullable|string|max:255',
            'slot_duration' => 'required|integer|min:1',

            'admission_fee' => 'nullable|numeric|min:0',
            'monthly_fee' => 'nullable|numeric|min:0',

            // 'starting_date'    => 'nullable|date',
            'is_completed' => 'nullable|boolean',
        ]);

        $this->classService->update($classId, $validated);

        return redirect()->route($this->routePrefix . '.index')
            ->with('success', 'Class updated successfully');
    }

    public function search(Request $request)
    {
        $term = $request->input('q', '');
        $query = ClassRoom::active()->with('course');

        // Filter by type name (e.g., 'group,individual') if provided
        if ($request->filled('type')) {
            $types = explode(',', $request->type);
            $query->whereHas('classType', function ($q) use ($types) {
                $q->whereIn('name', $types);
            });
        }

        // Exclude specific student's enrolled classes
        if ($request->filled('exclude_student_id')) {
            $query->whereDoesntHave('students', function ($q) use ($request) {
                $q->where('students.id', $request->exclude_student_id);
            });
        }

        // Filter by a specific student's enrolled classes
        if ($request->filled('student_id')) {
            $query->whereHas('students', function ($q) use ($request) {
                $q->where('students.id', $request->student_id);
            });
        }

        $results = $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhereHas('course', fn($c) => $c->where('name', 'like', "%{$term}%"));
        })
            ->limit(30)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'text' => $c->name . ($c->course ? ' (' . $c->course->name . ')' : ''),
            ]);

        return response()->json(['results' => $results]);
    }

    public function show($id)
    {
        $class = ClassRoom::with([
            'course',
            'classType',
            'teachers',
            'students'
        ])->findOrFail(decrypt($id));

        $teachers = Teacher::all();

        return view($this->viewPrefix . '.show', compact(
            'class',
            'teachers'
        ));
    }

    public function assignTeacher(Request $request)
    {
        $validated = $request->validate([
            'class_room_id' => 'required',
            'teacher_id' => 'required',
            'hourly_wage' => 'required|numeric|min:0'
        ]);

        $classId = decrypt($validated['class_room_id']);
        $teacherId = decrypt($validated['teacher_id']);

        $class = ClassRoom::findOrFail($classId);
        if ($class->is_completed) {
            return back()->with('error', 'Cannot assign teacher. The class is already marked as completed.');
        }

        $result = $this->classService->assignTeacher(
            $classId,
            $teacherId,
            $validated['hourly_wage']
        );

        if (!$result['status']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'Teacher assigned successfully');
    }

    public function removeTeacher(Request $request)
    {
        $validated = $request->validate([
            'class_room_id' => 'required',
            'teacher_id' => 'required'
        ]);

        $classId = decrypt($validated['class_room_id']);
        $teacherId = decrypt($validated['teacher_id']);

        $class = ClassRoom::findOrFail($classId);
        if ($class->is_completed) {
            return back()->with('error', 'Cannot remove teacher. The class is already marked as completed.');
        }

        $this->classService->removeTeacher($classId, $teacherId);

        return back()->with('success', 'Teacher removed from class');
    }

    public function assignStudents(Request $request)
    {
        $validated = $request->validate([
            'class_room_id' => 'required',
            'student_ids' => 'required|array'
        ]);

        $classId = decrypt($validated['class_room_id']);

        $class = ClassRoom::findOrFail($classId);
        if ($class->is_completed) {
            return back()->with('error', 'Cannot assign students. The class is already marked as completed.');
        }

        $result = $this->classService->assignStudents(
            $classId,
            $validated['student_ids']
        );

        if (!$result['status']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    public function destroy($id)
    {
        $staff = auth('staff')->user();
        $isAdmin = auth('admin')->check();
        $isOperation = $staff && $staff->hasRoleId(utility('id_operation_dept'));

        if (!$isAdmin && !$isOperation) {
            abort(403, 'Unauthorized access: Only Administrators or Operations department can delete classes.');
        }

        $classId = decrypt($id);
        $class = ClassRoom::findOrFail($classId);

        if ($class->is_completed) {
            return back()->with('error', 'Cannot delete. The class is already marked as completed.');
        }

        $class = $this->classService->delete($classId);

        return back()->with('success', "Class \"{$class->name}\" deleted successfully.");
    }


    public function changeStatus($id)
    {
        $this->classService->toggleStatus(decrypt($id));

        return back()->with('success', 'Status updated');
    }

    public function removeStudent(Request $request)
    {
        $validated = $request->validate([
            'class_room_id' => 'required',
            'student_id' => 'required'
        ]);

        $classId = decrypt($validated['class_room_id']);
        $studentId = decrypt($validated['student_id']);

        $class = ClassRoom::findOrFail($classId);
        if ($class->is_completed) {
            return back()->with('error', 'Cannot remove student. The class is already marked as completed.');
        }

        $this->classService->removeStudent($classId, $studentId);

        return back()->with('success', 'Student removed from class');
    }

}
