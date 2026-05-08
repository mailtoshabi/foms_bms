<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class StudentTeacherMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = Message::whereNull('reply_to_id')
            ->with(['sender', 'receiver', 'replies'])
            ->where(function ($q) {
                $q->where('sender_type', Teacher::class)
                  ->orWhere('sender_type', Student::class);
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHasMorph('sender', [Teacher::class, Student::class], function ($q2) use ($search) {
                    $q2->where('name', 'like', '%' . $search . '%');
                })->orWhereHasMorph('receiver', [Teacher::class, Student::class], function ($q2) use ($search) {
                    $q2->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        if ($request->filled('direction')) {
            if ($request->direction === 'teacher_to_student') {
                $query->where('sender_type', Teacher::class)
                      ->where('receiver_type', Student::class);
            } elseif ($request->direction === 'student_to_teacher') {
                $query->where('sender_type', Student::class)
                      ->where('receiver_type', Teacher::class);
            }
        }

        $messages = $query->latest()->paginate(utility('pagination', 15))->withQueryString();

        return view('admin.st-messages.index', compact('messages'));
    }

    public function show($id)
    {
        $message = Message::with(['sender', 'receiver', 'replies.sender'])
            ->findOrFail(decrypt($id));

        return view('admin.st-messages.show', compact('message'));
    }
}
