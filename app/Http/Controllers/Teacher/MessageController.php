<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $teacher = Auth::guard('teacher')->user();

        // Get only root messages (not replies) involving this teacher
        $messages = Message::whereNull('reply_to_id')
            ->where(function ($q) use ($teacher) {
                $q->where(function ($q2) use ($teacher) {
                    $q2->where('sender_type', Teacher::class)->where('sender_id', $teacher->id);
                })->orWhere(function ($q2) use ($teacher) {
                    $q2->where('receiver_type', Teacher::class)->where('receiver_id', $teacher->id);
                });
            })
            ->with(['sender', 'receiver', 'replies'])
            ->latest()
            ->get();

        return view('teacher.messages.index', compact('messages', 'teacher'));
    }

    public function create()
    {
        $teacher = Auth::guard('teacher')->user();

        // Get students from teacher's assigned class rooms
        $students = Student::whereHas('class_rooms', function ($q) use ($teacher) {
            $q->whereIn('class_rooms.id', $teacher->classRooms()->pluck('class_rooms.id'));
        })->get();

        return view('teacher.messages.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'message' => 'required|string|max:2000',
        ]);

        $teacher = Auth::guard('teacher')->user();

        Message::create([
            'sender_type' => Teacher::class,
            'sender_id' => $teacher->id,
            'receiver_type' => Student::class,
            'receiver_id' => $request->student_id,
            'message' => $request->message,
        ]);

        return redirect()->route('teacher.messages.index')->with('success', 'Message sent successfully!');
    }

    public function show($id)
    {
        $teacher = Auth::guard('teacher')->user();
        $message = Message::with(['sender', 'receiver', 'replies.sender'])->findOrFail(decrypt($id));

        // Ensure this teacher is involved in the message
        $isTeacherInvolved = ($message->sender_type == Teacher::class && $message->sender_id == $teacher->id)
            || ($message->receiver_type == Teacher::class && $message->receiver_id == $teacher->id);

        if (!$isTeacherInvolved) {
            abort(403);
        }

        return view('teacher.messages.show', compact('message', 'teacher'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $teacher = Auth::guard('teacher')->user();
        $parent = Message::findOrFail(decrypt($id));

        // Determine the receiver (the other party)
        if ($parent->sender_type == Teacher::class && $parent->sender_id == $teacher->id) {
            $receiverType = $parent->receiver_type;
            $receiverId = $parent->receiver_id;
        } else {
            $receiverType = $parent->sender_type;
            $receiverId = $parent->sender_id;
        }

        Message::create([
            'reply_to_id' => $parent->id,
            'sender_type' => Teacher::class,
            'sender_id' => $teacher->id,
            'receiver_type' => $receiverType,
            'receiver_id' => $receiverId,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Reply sent successfully!');
    }
}
