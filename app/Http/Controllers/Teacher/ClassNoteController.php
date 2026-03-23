<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassNote;
use App\Models\Student;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\Auth;

class ClassNoteController extends Controller
{

    public function index()
    {
        $notes = ClassNote::with(['student','classRoom'])
            ->latest()
            ->paginate(10);

        return view('teacher.class_notes.index', compact('notes'));
    }


    public function create()
    {
        $students = Student::all();
        $class_rooms = ClassRoom::all();

        return view('teacher.class_notes.create', compact('students','class_rooms'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required',
            'student_id'=>'required',
            'class_room_id'=>'required'
        ]);

        $file = null;

        if($request->hasFile('attachment')){
            $file = $request->file('attachment')
                ->store('class_notes','public');
        }

        ClassNote::create([
            'title'=>$request->title,
            'note'=>$request->note,
            'student_id'=>$request->student_id,
            'class_room_id'=>$request->class_room_id,
            'teacher_id'=>auth('teacher')->id(),
            'attachment'=>$file
        ]);

        return redirect()
            ->route('teacher.class-notes.index')
            ->with('success','Class note created');
    }


    public function show($id)
    {
        $note = ClassNote::with(['student','classRoom','teacher'])
            ->findOrFail($id);

        return view('teacher.class_notes.show', compact('note'));
    }


    public function destroy($id)
    {
        $note = ClassNote::findOrFail($id);

        $note->delete();

        return back()->with('success','Deleted successfully');
    }
}
