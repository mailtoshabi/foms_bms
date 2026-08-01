<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassNote;
use App\Models\ClassNoteFile;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClassNoteController extends Controller
{

    public function index()
    {
        $student = Auth::guard('student')->user();
        $classRoomIds = $student->class_rooms()->pluck('class_rooms.id');

        $notes = ClassNote::select('id', 'class_room_id', 'teacher_id', 'title', 'created_at')
            ->with([
                'classRoom' => fn($q) => $q->select('id', 'name'),
                'teacher' => fn($q) => $q->select('id', 'name')
            ])
            ->whereIn('class_room_id', $classRoomIds)
            ->latest()
            ->paginate(utility('pagination', 50));

        return view('student.class_notes.index', compact('notes'));
    }


    public function show($id)
    {
        $student = Auth::guard('student')->user();
        $classRoomIds = $student->class_rooms()->pluck('class_rooms.id');

        $note = ClassNote::select('id', 'class_room_id', 'teacher_id', 'title', 'content', 'created_at')
            ->with([
                'classRoom' => fn($q) => $q->select('id', 'name'),
                'teacher' => fn($q) => $q->select('id', 'name'),
                'files' => fn($q) => $q->select('id', 'class_note_id', 'file_name', 'file_path')
            ])
            ->whereIn('class_room_id', $classRoomIds)
            ->findOrFail(decrypt($id));

        return view('student.class_notes.show', compact('note'));
    }

}
