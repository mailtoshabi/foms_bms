<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassHour;
use App\Models\ClassNote;
use App\Models\ClassRoom;
use App\Models\Message;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $id = auth()->guard('student')->id();

        if (!$id) {
            return redirect()->route('student.login');
        }

        $student = Student::with([
            'class_rooms.course',
            'class_rooms.classType',
            'class_rooms.teachers',
            'fees',
            'attendances'
        ])->findOrFail($id);

        $currentClass = $student->class_rooms()
            ->orderBy('student_class_room.created_at', 'desc')
            ->first();

        $teacher = null;

        if ($currentClass) {
            $teacher = Teacher::whereHas('classRooms', function($q) use ($currentClass){
                $q->where('class_rooms.id',$currentClass->id);
            })->first();
        }

        $totalAttendance = $student->attendances()->count();
        $presentAttendance = $student->attendances()->where('is_present',1)->count();

        $attendancePercent = $totalAttendance > 0
            ? round(($presentAttendance / $totalAttendance) * 100)
            : 0;

        // Get total due fees and paid fees from fee_payments
        $totalDueFees = $student->fees()->where('status', 'unpaid')->sum('amount');
        $paidFees = $student->fees()
            ->join('fee_payments', 'fees.id', '=', 'fee_payments.fee_id')
            ->sum('fee_payments.paid_amount');

        $feeDue = $totalDueFees;

        // Fee details for modal
        $feeDetails = $student->fees()
            ->with(['classRoom', 'payments'])
            ->latest()
            ->limit(5)
            ->get();

        // Get all teachers from student's class rooms
        $allTeachers = Teacher::whereHas('classRooms', function($q) use ($student) {
            $q->whereIn('class_rooms.id', $student->class_rooms->pluck('id'));
        })->get();

        // Get pending class hours for student's classes
        $classRoomIds = $student->class_rooms->pluck('id');
        $pendingClassHours = ClassHour::whereIn('class_room_id', $classRoomIds)
            ->where('status', 'pending')
            ->with('classRoom')
            ->latest()
            ->get();

        // Get messages sent/received by student + class broadcasts for enrolled classes
        $studentType = Student::class;
        $allMessages = Message::whereNull('reply_to_id')
            ->where(function ($q) use ($id, $studentType, $classRoomIds) {
                $q->where(function ($q2) use ($id, $studentType) {
                    $q2->where('sender_type', $studentType)->where('sender_id', $id);
                })->orWhere(function ($q2) use ($id, $studentType) {
                    $q2->where('receiver_type', $studentType)->where('receiver_id', $id);
                })->orWhere(function ($q2) use ($classRoomIds) {
                    $q2->where('receiver_type', ClassRoom::class)
                       ->whereIn('receiver_id', $classRoomIds);
                });
            })
            ->with(['sender', 'receiver'])
            ->latest()
            ->get();

        // Get class notes for student's classes
        $allNotes = ClassNote::whereIn('class_room_id', $classRoomIds)
            ->with(['classRoom', 'files', 'teacher'])
            ->latest()
            ->get();
        $latestNotes = $allNotes->take(5);

        // Fetch active/upcoming holidays for the logged-in student
        $holidays = \App\Models\Holiday::where(function ($q) use ($student, $classRoomIds) {
            $q->where('target_type', 'all_students')
              ->orWhere(function ($q2) use ($student) {
                  $q2->where('target_type', 'selected_students')
                     ->whereHas('students', function ($q3) use ($student) {
                         $q3->where('students.id', $student->id);
                     });
              })
              ->orWhere(function ($q2) use ($classRoomIds) {
                  $q2->where('target_type', 'classes')
                     ->whereIn('class_target_type', ['students', 'both'])
                     ->whereHas('classRooms', function ($q3) use ($classRoomIds) {
                         $q3->whereIn('class_rooms.id', $classRoomIds);
                     });
              });
        })->where('date', '>=', now()->toDateString())
          ->orderBy('date', 'asc')
          ->get();

        return view('student.dashboard', compact(
            'student',
            'currentClass',
            'teacher',
            'allTeachers',
            'attendancePercent',
            'feeDue',
            'feeDetails',
            'latestNotes',
            'allNotes',
            'pendingClassHours',
            'allMessages',
            'totalAttendance',
            'presentAttendance',
            'holidays',
        ));
    }

    public function profile()
    {
        $id = auth()->guard('student')->id();

        if (!$id) {
            return redirect()->route('student.login');
        }

        $student = Student::with([
            'class_rooms.course',
            'class_rooms.classType',
        ])->findOrFail($id);

        return view('student.profile', compact('student'));
    }
}
