<?php

namespace App\Http\Controllers\Student;

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
        $student = Auth::guard('student')->user();

        $messages = Message::whereNull('reply_to_id')
            ->where(function ($q) use ($student) {
                $q->where(function ($q2) use ($student) {
                    $q2->where('sender_type', Student::class)->where('sender_id', $student->id);
                })->orWhere(function ($q2) use ($student) {
                    $q2->where('receiver_type', Student::class)->where('receiver_id', $student->id);
                });
            })
            ->with(['sender', 'receiver', 'replies'])
            ->latest()
            ->get();

        return view('student.messages.index', compact('messages', 'student'));
    }

    public function create()
    {
        $student = Auth::guard('student')->user();

        // Get teachers from student's assigned class rooms
        $teachers = Teacher::whereHas('classRooms', function ($q) use ($student) {
            $q->whereIn('class_rooms.id', $student->class_rooms()->pluck('class_rooms.id'));
        })->get();

        return view('student.messages.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'message' => 'required|string|max:2000',
        ]);

        $student = Auth::guard('student')->user();

        Message::create([
            'sender_type' => Student::class,
            'sender_id' => $student->id,
            'receiver_type' => Teacher::class,
            'receiver_id' => $request->teacher_id,
            'message' => $request->message,
        ]);

        return redirect()->route('student.messages.index')->with('success', 'Message sent successfully!');
    }

    public function show($id)
    {
        $student = Auth::guard('student')->user();
        $message = Message::with(['sender', 'receiver', 'replies.sender'])->findOrFail(decrypt($id));

        $isStudentInvolved = ($message->sender_type == Student::class && $message->sender_id == $student->id)
            || ($message->receiver_type == Student::class && $message->receiver_id == $student->id);

        if (!$isStudentInvolved) {
            abort(403);
        }

        return view('student.messages.show', compact('message', 'student'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $student = Auth::guard('student')->user();
        $parent = Message::findOrFail(decrypt($id));

        if ($parent->sender_type == Student::class && $parent->sender_id == $student->id) {
            $receiverType = $parent->receiver_type;
            $receiverId = $parent->receiver_id;
        } else {
            $receiverType = $parent->sender_type;
            $receiverId = $parent->sender_id;
        }

        Message::create([
            'reply_to_id' => $parent->id,
            'sender_type' => Student::class,
            'sender_id' => $student->id,
            'receiver_type' => $receiverType,
            'receiver_id' => $receiverId,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Reply sent successfully!');
    }
}
