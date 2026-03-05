<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\ClassType;
use App\Http\Controllers\BaseServiceController;

class ClassRoomController extends BaseServiceController
{
    public function index(Request $request)
    {
        $query = ClassRoom::with(['course','classType'])->latest();

        // ✅ Filter: Course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // ✅ Filter: Class Type
        if ($request->filled('class_type_id')) {
            $query->where('class_type_id', $request->class_type_id);
        }

        // ✅ Filter: Status
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_completed', false);
            } elseif ($request->status == 'completed') {
                $query->where('is_completed', true);
            }
        }

        $classes = $query->paginate(10)->withQueryString();

        $courses = Course::all();
        $types   = ClassType::all();

        return view('admin.classes.index', compact('classes','courses','types'));
    }

    public function create()
    {
        $courses = Course::all();
        $types   = ClassType::all();

        return view('admin.classes.create',compact('courses','types'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'course_id'=>'required|exists:courses,id',
            'class_type_id'=>'required|exists:class_types,id',
            'name'=>'required|string|max:255'
        ]);

        $this->classService->create($request->all());

        return redirect()->route('admin.classes.index')
            ->with('success','Class created successfully');
    }

    public function edit($id)
    {
        $class = ClassRoom::findOrFail(decrypt($id));
        $courses = Course::all();
        $types   = ClassType::all();

        return view('admin.classes.create',compact('class','courses','types'));
    }

    public function update(Request $request)
    {
        $classId = decrypt($request->class_id);

        $this->classService->update($classId,$request->all());

        return redirect()->route('admin.classes.index')
            ->with('success','Class updated successfully');
    }

    public function destroy($id)
    {
        $this->classService->delete(decrypt($id));

        return back()->with('success','Class deleted');
    }

    public function changeStatus($id)
    {
        $this->classService->toggleStatus(decrypt($id));

        return back()->with('success','Status updated');
    }
}
