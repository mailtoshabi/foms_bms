<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Homework;
use App\Models\HomeworkFile;
use App\Models\HomeworkSubmission;
use App\Models\HomeworkSubmissionFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HomeworkController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $classRoomIds = $student->class_rooms->pluck('id');

        $homeworks = Homework::with(['classRoom', 'teacher'])
            ->whereIn('class_room_id', $classRoomIds)
            ->latest()
            ->paginate(utility('pagination', 50));

        // Get submissions indexed by homework ID
        $submissions = HomeworkSubmission::where('student_id', $student->id)
            ->whereIn('homework_id', $homeworks->pluck('id'))
            ->get()
            ->keyBy('homework_id');

        return view('student.homeworks.index', compact('homeworks', 'submissions'));
    }

    public function show($id)
    {
        $student = Auth::guard('student')->user();
        $classRoomIds = $student->class_rooms->pluck('id');

        $homework = Homework::with(['classRoom', 'teacher', 'files'])
            ->whereIn('class_room_id', $classRoomIds)
            ->findOrFail(decrypt($id));

        $submission = HomeworkSubmission::with('files')
            ->where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        return view('student.homeworks.show', compact('homework', 'submission'));
    }

    public function submit(Request $request, $id)
    {
        $student = Auth::guard('student')->user();
        $classRoomIds = $student->class_rooms->pluck('id');

        $homework = Homework::whereIn('class_room_id', $classRoomIds)
            ->findOrFail(decrypt($id));

        // Check if already submitted
        $existing = HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already submitted this homework.');
        }

        $request->validate([
            'submitted_text' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:5120' // 5MB limit per file
        ]);

        if (empty($request->submitted_text) && !$request->hasFile('files')) {
            return back()->withInput()->with('error', 'You must provide either submission text or attach a file.');
        }

        try {
            $submission = HomeworkSubmission::create([
                'homework_id' => $homework->id,
                'student_id' => $student->id,
                'submitted_text' => $request->submitted_text
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if ($file) {
                        $fileName = $file->getClientOriginalName();
                        $fileType = $file->getClientOriginalExtension();
                        $fileSize = $file->getSize();
                        $filePath = $file->store('homework_submissions', 'public');

                        HomeworkSubmissionFile::create([
                            'homework_submission_id' => $submission->id,
                            'file_name' => $fileName,
                            'file_path' => $filePath,
                            'file_type' => $fileType,
                            'file_size' => $fileSize
                        ]);
                    }
                }
            }

            return redirect()
                ->route('student.homeworks.show', encrypt($homework->id))
                ->with('success', 'Homework submitted successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error submitting homework: ' . $e->getMessage());
        }
    }

    public function destroySubmission($id)
    {
        $student = Auth::guard('student')->user();
        $submission = HomeworkSubmission::where('student_id', $student->id)->findOrFail(decrypt($id));

        if (!is_null($submission->graded_at)) {
            return back()->with('error', 'You cannot delete a submission that has already been graded.');
        }

        // Delete files
        foreach ($submission->files as $file) {
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }

        $homeworkId = $submission->homework_id;
        $submission->delete();

        return redirect()
            ->route('student.homeworks.show', encrypt($homeworkId))
            ->with('success', 'Submission deleted successfully.');
    }

    public function downloadFile($id)
    {
        $file = HomeworkFile::findOrFail(decrypt($id));
        $student = Auth::guard('student')->user();
        $classRoomIds = $student->class_rooms->pluck('id');

        $homework = Homework::whereIn('class_room_id', $classRoomIds)->find($file->homework_id);
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
        $file = HomeworkSubmissionFile::with('submission')->findOrFail(decrypt($id));
        
        if ((int) $file->submission->student_id !== (int) auth('student')->id()) {
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
}
