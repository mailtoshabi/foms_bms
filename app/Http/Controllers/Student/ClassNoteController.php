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
        $classRoomIds = $student->class_rooms->pluck('id');

        $notes = ClassNote::with(['classRoom','teacher'])
            ->whereIn('class_room_id', $classRoomIds)
            ->latest()
            ->paginate(10);

        return view('student.class_notes.index', compact('notes'));
    }


    public function show($id)
    {
        $student = Auth::guard('student')->user();
        $classRoomIds = $student->class_rooms->pluck('id');

        $note = ClassNote::with(['classRoom','teacher'])
            ->whereIn('class_room_id', $classRoomIds)
            ->findOrFail(decrypt($id));

        return view('student.class_notes.show', compact('note'));
    }

}
