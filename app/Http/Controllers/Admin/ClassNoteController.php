<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassNote;
use App\Models\ClassNoteFile;
use App\Models\ClassRoom;
use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;

class ClassNoteController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassNote::select('id', 'class_room_id', 'teacher_id', 'title', 'created_at')
            ->with([
                'classRoom' => fn($q) => $q->select('id', 'name'),
                'teacher' => fn($q) => $q->select('id', 'name'),
                'files' => fn($q) => $q->select('id', 'class_note_id', 'file_name', 'file_path')
            ]);

        // Filters
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->class_room_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $notes = $query->latest()->paginate(utility('pagination', 50))->withQueryString();

        $classRooms = \Illuminate\Support\Facades\Cache::remember('active_classrooms', 3600, function () {
            return ClassRoom::active()->orderBy('name')->select('id', 'name')->get();
        });
        $teachers = \Illuminate\Support\Facades\Cache::remember('active_teachers', 3600, function () {
            return Teacher::active()->orderBy('name')->select('id', 'name')->get();
        });

        return view('admin.class_notes.index', compact('notes', 'classRooms', 'teachers'));
    }

    public function show($id)
    {
        $note = ClassNote::select('id', 'class_room_id', 'teacher_id', 'title', 'content', 'created_at')
            ->with([
                'classRoom' => fn($q) => $q->select('id', 'name'),
                'teacher' => fn($q) => $q->select('id', 'name'),
                'files' => fn($q) => $q->select('id', 'class_note_id', 'file_name', 'file_path')
            ])
            ->findOrFail(decrypt($id));

        return view('admin.class_notes.show', compact('note'));
    }

    public function destroy($id)
    {
        $note = ClassNote::findOrFail(decrypt($id));

        // Delete files
        foreach ($note->files as $file) {
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }

        $note->delete();

        return redirect()->route('admin.class-notes.index')
            ->with('success', 'Class note deleted successfully.');
    }

    public function downloadFile($id)
    {
        $file = ClassNoteFile::findOrFail(decrypt($id));
        $path = storage_path('app/public/' . $file->file_path);

        if (!file_exists($path)) {
            abort(404, 'File not found on server.');
        }

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $file->file_name . '"'
        ]);
    }
}
