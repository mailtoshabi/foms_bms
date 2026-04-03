<?php

namespace App\Http\Controllers\Teacher;

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
        $notes = ClassNote::with(['classRoom','teacher'])
            ->latest()
            ->paginate(10);

        return view('teacher.class_notes.index', compact('notes'));
    }


    public function create()
    {
        $class_rooms = Auth::guard('teacher')->user()->classRooms;

        return view('teacher.class_notes.create', compact('class_rooms'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required',
            'class_room_id'=>'required',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240' // 10MB per file
        ]);

        try {
            // Create the class note
            $classNote = ClassNote::create([
                'title'=>$request->title,
                'content'=>$request->note,
                'class_room_id'=>$request->class_room_id,
                'teacher_id'=>auth('teacher')->id(),
            ]);

            // Handle multiple file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if ($file) {
                        $fileName = $file->getClientOriginalName();
                        $fileType = $file->getClientOriginalExtension();
                        $fileSize = $file->getSize();
                        $filePath = $file->store('class_notes', 'public');

                        ClassNoteFile::create([
                            'class_note_id' => $classNote->id,
                            'file_name' => $fileName,
                            'file_path' => $filePath,
                            'file_type' => $fileType,
                            'file_size' => $fileSize
                        ]);
                    }
                }
            }

            return redirect()
                ->route('teacher.notes.index')
                ->with('success', 'Class note created successfully with ' . ($request->hasFile('files') ? count($request->file('files')) : 0) . ' file(s)');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating note: ' . $e->getMessage());
        }
    }


    public function show($id)
    {
        $note = ClassNote::with(['classRoom','teacher'])
            ->findOrFail(decrypt($id));

        return view('teacher.class_notes.show', compact('note'));
    }


    public function destroy($id)
    {
        $note = ClassNote::findOrFail(decrypt($id));

        // Delete associated files
        foreach ($note->files as $file) {
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }

        $note->delete();

        return back()->with('success','Note deleted successfully');
    }
}
