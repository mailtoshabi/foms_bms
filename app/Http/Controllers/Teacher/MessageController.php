<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
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

        $messages = Message::whereNull('reply_to_id')
            ->where(function ($q) use ($teacher) {
                // Direct messages sent/received by this teacher
                $q->where(function ($q2) use ($teacher) {
                    $q2->where('sender_type', Teacher::class)
                        ->where('sender_id', $teacher->id);
                })->orWhere(function ($q2) use ($teacher) {
                    $q2->where('receiver_type', Teacher::class)
                        ->where('receiver_id', $teacher->id);
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
        $classRoomIds = $teacher->classRooms()->pluck('class_rooms.id');

        $students = Student::whereHas('class_rooms', function ($q) use ($classRoomIds) {
            $q->whereIn('class_rooms.id', $classRoomIds);
        })->get();

        $classRooms = $teacher->classRooms()->with('course')->get();

        return view('teacher.messages.create', compact('students', 'classRooms'));
    }

    public function store(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();

        if ($request->to_type === 'class') {
            $request->validate([
                'class_room_id' => 'required|exists:class_rooms,id',
                'message' => 'required|string|max:2000',
            ]);

            // Ensure this teacher belongs to the selected class
            $allowed = $teacher->classRooms()->pluck('class_rooms.id');
            if (!$allowed->contains($request->class_room_id)) {
                abort(403);
            }

            Message::create([
                'sender_type' => Teacher::class,
                'sender_id' => $teacher->id,
                'receiver_type' => ClassRoom::class,
                'receiver_id' => $request->class_room_id,
                'message' => $request->message,
            ]);
        } else {
            $request->validate([
                'student_id' => 'required|exists:students,id',
                'message' => 'required|string|max:2000',
            ]);

            Message::create([
                'sender_type' => Teacher::class,
                'sender_id' => $teacher->id,
                'receiver_type' => Student::class,
                'receiver_id' => $request->student_id,
                'message' => $request->message,
            ]);
        }

        return redirect()->route('teacher.messages.index')->with('success', 'Message sent successfully!');
    }

    public function show($id)
    {
        $teacher = Auth::guard('teacher')->user();
        $message = Message::with(['sender', 'receiver', 'replies.sender'])->findOrFail(decrypt($id));

        $isInvolved = ($message->sender_type == Teacher::class && $message->sender_id == $teacher->id)
            || ($message->receiver_type == Teacher::class && $message->receiver_id == $teacher->id);

        if (!$isInvolved) {
            abort(403);
        }

        // Mark as read if the teacher is the receiver (direct or via classroom)
        if (!$message->is_read) {
            if ($message->receiver_type == Teacher::class && $message->receiver_id == $teacher->id) {
                $message->update(['is_read' => true]);
            } elseif ($message->receiver_type == ClassRoom::class) {
                $classRoomIds = $teacher->classRooms()->pluck('class_rooms.id');
                if ($classRoomIds->contains($message->receiver_id)) {
                    $message->update(['is_read' => true]);
                }
            }
        }

        // Also mark all replies received by this teacher as read
        Message::where('reply_to_id', $message->id)
            ->where('is_read', false)
            ->where(function ($q) use ($teacher) {
                $q->where(function ($q2) use ($teacher) {
                    $q2->where('receiver_type', Teacher::class)
                        ->where('receiver_id', $teacher->id);
                })->orWhere(function ($q2) use ($teacher) {
                    $classRoomIds = $teacher->classRooms()->pluck('class_rooms.id');
                    $q2->where('receiver_type', ClassRoom::class)
                        ->whereIn('receiver_id', $classRoomIds);
                });
            })
            ->update(['is_read' => true]);

        $isClassMessage = $message->receiver_type === ClassRoom::class;

        return view('teacher.messages.show', compact('message', 'teacher', 'isClassMessage'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $teacher = Auth::guard('teacher')->user();
        $parent = Message::findOrFail(decrypt($id));

        // For class message: teacher replies back to the class
        // For direct message: reply to the other party
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

