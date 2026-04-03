<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseCategory;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with('category');

        // 🔎 Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 🔎 Filter by course name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $courses = $query->latest()->paginate(10)->withQueryString();

        // needed for filter dropdown
        $categories = CourseCategory::all();

        return view('admin.courses.index', compact('courses','categories'));
    }

    public function create()
    {
        $categories = CourseCategory::all();
        return view('admin.courses.create',compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:course_categories,id',
            'name'        => 'required|string|max:255',
            // 'course_fee'  => 'nullable|numeric'
        ]);

        Course::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            // 'course_fee'  => $request->course_fee ?? 0,
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success','Course created successfully');
    }

    public function edit($id)
    {
        $course = Course::findOrFail(decrypt($id));
        $categories = CourseCategory::all();

        return view('admin.courses.create',compact('course','categories'));
    }

    public function update(Request $request)
    {
        // decrypt id from hidden input
        $courseId = decrypt($request->course_id);

        $course = Course::findOrFail($courseId);

        // Validation based on new migration
        $request->validate([
            'category_id' => 'required|exists:course_categories,id',
            'name'        => 'required|string|max:255',
            // 'course_fee'  => 'nullable|numeric'
        ]);

        // Update fields safely
        $course->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            // 'course_fee'  => $request->course_fee ?? 0,
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success','Course updated successfully');
    }

    public function destroy($id)
    {
        Course::findOrFail(decrypt($id))->delete();

        return back()->with('success','Course deleted');
    }
}
