<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Homework;
use App\Models\HomeworkFile;
use App\Models\ClassRoom;
use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;

class HomeworkController extends Controller
{
    public function index(Request $request)
    {
        $query = Homework::select('id', 'title', 'class_room_id', 'teacher_id', 'created_at')
            ->with([
                'classRoom' => fn($q) => $q->select('id', 'name'),
                'teacher' => fn($q) => $q->select('id', 'name')
            ])
            ->withCount(['files', 'submissions']);

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->class_room_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $homeworks = $query->latest()->paginate(utility('pagination', 50))->withQueryString();

        $classRooms = \Illuminate\Support\Facades\Cache::remember('homework_classrooms', 3600, function () {
            return ClassRoom::active()->orderBy('name')->select('id', 'name')->get();
        });

        $teachers = \Illuminate\Support\Facades\Cache::remember('homework_teachers', 3600, function () {
            return Teacher::active()->orderBy('name')->select('id', 'name')->get();
        });

        return view('admin.homeworks.index', compact('homeworks', 'classRooms', 'teachers'));
    }

    public function show($id)
    {
        $homework = Homework::select('id', 'title', 'content', 'class_room_id', 'teacher_id', 'created_at')
            ->with([
                'classRoom' => fn($q) => $q->select('id', 'name'),
                'teacher' => fn($q) => $q->select('id', 'name'),
                'files' => fn($q) => $q->select('id', 'homework_id', 'file_name', 'file_path', 'file_size'),
                'submissions' => fn($q) => $q->select('id', 'homework_id', 'student_id', 'created_at', 'graded_at', 'mark_obtained', 'total_mark', 'graded_by'),
                'submissions.student' => fn($q) => $q->select('id', 'name'),
                'submissions.grader' => fn($q) => $q->select('id', 'name')
            ])
            ->findOrFail(decrypt($id));

        return view('admin.homeworks.show', compact('homework'));
    }

    public function destroy($id)
    {
        $homework = Homework::findOrFail(decrypt($id));

        // Delete assignment files
        foreach ($homework->files as $file) {
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }

        // Delete submissions and their files
        foreach ($homework->submissions as $submission) {
            foreach ($submission->files as $subFile) {
                if (Storage::disk('public')->exists($subFile->file_path)) {
                    Storage::disk('public')->delete($subFile->file_path);
                }
                $subFile->delete();
            }
            $submission->delete();
        }

        $homework->delete();

        return redirect()->route('admin.homeworks.index')
            ->with('success', 'Homework deleted successfully.');
    }

    public function downloadFile($id)
    {
        $file = HomeworkFile::findOrFail(decrypt($id));
        $path = storage_path('app/public/' . $file->file_path);

        if (!file_exists($path)) {
            abort(404, 'File not found on server.');
        }

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $file->file_name . '"'
        ]);
    }
}
