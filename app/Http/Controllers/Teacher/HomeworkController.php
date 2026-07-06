<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Homework;
use App\Models\HomeworkFile;
use App\Models\HomeworkSubmission;
use App\Models\HomeworkSubmissionFile;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HomeworkController extends Controller
{
    public function index()
    {
        $homeworks = auth('teacher')->user()->homeworks()
            ->with(['classRoom', 'teacher'])
            ->latest()
            ->paginate(utility('pagination', 50));

        return view('teacher.homeworks.index', compact('homeworks'));
    }

    public function create(Request $request)
    {
        $classRoom = null;
        if ($request->filled('class_room_id')) {
            try {
                $classRoomId = decrypt($request->class_room_id);
                $classRoom = auth('teacher')->user()->classRooms()->find($classRoomId);
            } catch (\Exception $e) {
                // If decryption fails, ignore and proceed
            }
        }
        return view('teacher.homeworks.create', compact('classRoom'));
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
            'title' => 'required|string|max:255',
            'class_room_id' => [
                'required',
                Rule::exists('teacher_class_room', 'class_room_id')
                    ->where('teacher_id', auth('teacher')->id())
            ],
            'content' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:5120' // 5MB per file max
        ]);

        try {
            $homework = Homework::create([
                'title' => $request->title,
                'content' => $request->content,
                'class_room_id' => $request->class_room_id,
                'teacher_id' => auth('teacher')->id(),
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if ($file) {
                        $fileName = $file->getClientOriginalName();
                        $fileType = $file->getClientOriginalExtension();
                        $fileSize = $file->getSize();
                        $filePath = $file->store('homeworks', 'public');

                        HomeworkFile::create([
                            'homework_id' => $homework->id,
                            'file_name' => $fileName,
                            'file_path' => $filePath,
                            'file_type' => $fileType,
                            'file_size' => $fileSize
                        ]);
                    }
                }
            }

            return redirect()
                ->route('teacher.homeworks.index')
                ->with('success', 'Homework assigned successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error assigning homework: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $homework = auth('teacher')->user()->homeworks()
            ->with(['classRoom.students', 'files'])
            ->findOrFail(decrypt($id));

        // Get submissions indexed by student ID for easier lookup
        $submissions = HomeworkSubmission::with(['files', 'student'])
            ->where('homework_id', $homework->id)
            ->get()
            ->keyBy('student_id');

        return view('teacher.homeworks.show', compact('homework', 'submissions'));
    }

    public function downloadFile($id)
    {
        $file = HomeworkFile::findOrFail(decrypt($id));
        $homework = auth('teacher')->user()->homeworks()->find($file->homework_id);
        
        if (!$homework) {
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

    public function downloadSubmissionFile($id)
    {
        $file = HomeworkSubmissionFile::with('submission.homework')->findOrFail(decrypt($id));
        
        if ($file->submission->homework->teacher_id !== auth('teacher')->id()) {
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
        $homework = auth('teacher')->user()->homeworks()->findOrFail(decrypt($id));

        // Delete associated homework files
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

        return back()->with('success', 'Homework deleted successfully.');
    }

    public function gradeSubmission(Request $request, $id)
    {
        $submission = HomeworkSubmission::with('homework')->findOrFail(decrypt($id));

        if ($submission->homework->teacher_id !== auth('teacher')->id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'total_mark' => 'required|numeric|min:0',
            'mark_obtained' => 'required|numeric|min:0|lte:total_mark',
            'teacher_comments' => 'nullable|string'
        ], [
            'mark_obtained.lte' => 'Obtained marks cannot exceed the total marks possible.'
        ]);

        $submission->update([
            'total_mark' => $request->total_mark,
            'mark_obtained' => $request->mark_obtained,
            'teacher_comments' => $request->teacher_comments,
            'graded_by' => auth('teacher')->id(),
            'graded_at' => now()
        ]);

        return back()->with('success', 'Submission evaluated successfully.');
    }
}
