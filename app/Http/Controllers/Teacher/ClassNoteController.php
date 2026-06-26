<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassNote;
use App\Models\ClassNoteFile;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClassNoteController extends Controller
{

    public function index()
    {
        $notes = auth('teacher')->user()->notes()
            ->with(['classRoom', 'teacher'])
            ->latest()
            ->paginate(utility('pagination', 50));

        return view('teacher.class_notes.index', compact('notes'));
    }


    public function create()
    {
        return view('teacher.class_notes.create');
    }

    public function searchClassRooms(Request $request)
    {
        $term = $request->input('q', '');
        $teacher = Auth::guard('teacher')->user();

        $results = $teacher->classRooms()
            ->with('course')
            ->where('class_rooms.name', 'like', "%{$term}%")
            ->limit(30)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'text' => $c->name . ($c->course ? ' (' . $c->course->name . ')' : ''),
            ]);

        return response()->json(['results' => $results]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'class_room_id' => [
                'required',
                Rule::exists('teacher_class_room', 'class_room_id')
                    ->where('teacher_id', auth('teacher')->id())
            ],
            'files' => 'nullable|array',
            'files.*' => 'file|max:2048' // 2MB per file
        ]);

        try {
            // Create the class note
            $classNote = ClassNote::create([
                'title' => $request->title,
                'content' => $request->note,
                'class_room_id' => $request->class_room_id,
                'teacher_id' => auth('teacher')->id(),
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
        $note = auth('teacher')->user()->notes()
            ->with(['classRoom', 'teacher'])
            ->findOrFail(decrypt($id));

        return view('teacher.class_notes.show', compact('note'));
    }


    public function downloadFile($id)
    {
        $file = ClassNoteFile::findOrFail(decrypt($id));

        $note = auth('teacher')->user()->notes()->find($file->class_note_id);
        if (!$note) {
            abort(403, 'Unauthorized access to this file.');
        }

        $path = storage_path('app/public/' . $file->file_path);

        if (!file_exists($path)) {
            abort(404, 'File not found on server.');
        }

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $file->file_name . '"'
        ]);
    }


    public function destroy($id)
    {
        $note = auth('teacher')->user()->notes()->findOrFail(decrypt($id));

        // Delete associated files
        foreach ($note->files as $file) {
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }

        $note->delete();

        return back()->with('success', 'Note deleted successfully');
    }
}
