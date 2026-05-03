<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\ClassRoom;

class TeacherAssignmentController extends Controller
{
    public function assign(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_room_id' => 'required|exists:class_rooms,id',
            'hourly_wage' => 'nullable|numeric|min:0'
        ]);

        $teacher = Teacher::findOrFail($request->teacher_id);
        $classRoom = ClassRoom::findOrFail($request->class_room_id);

        if ($classRoom->is_completed) {
            return back()->with('error', 'Cannot assign teacher. The class is already marked as completed.');
        }

        // Prevent duplicate assignment
        if ($teacher->classRooms()->where('class_room_id', $request->class_room_id)->exists()) {

            return back()->with(
                'error',
                'This class is already assigned to the teacher.'
            );
        }

        $teacher->classRooms()->attach($request->class_room_id, [
            'hourly_wage' => $request->hourly_wage,
            'assigned_at' => now()
        ]);

        return back()->with('success', 'Class assigned successfully');
    }

    public function destroy($teacherId, $classRoomId)
    {
        $teacher = Teacher::findOrFail($teacherId);
        $classRoom = ClassRoom::findOrFail($classRoomId);

        if ($classRoom->is_completed) {
            return back()->with('error', 'Cannot remove teacher. The class is already marked as completed.');
        }

        // $attendanceExists = Attendance::where('teacher_id',$teacherId)
        //     ->where('class_room_id',$classRoomId)
        //     ->exists();

        // if($attendanceExists){
        //     return back()->with(
        //         'error',
        //         'Cannot remove teacher. Attendance exists.'
        //     );
        // }

        $teacher->classRooms()->detach($classRoomId);

        return back()->with(
            'success',
            'Teacher removed from class.'
        );
    }

    public function updateWage(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_room_id' => 'required|exists:class_rooms,id',
            'hourly_wage' => 'required|numeric|min:0'
        ]);

        $teacher = Teacher::findOrFail($request->teacher_id);
        $classRoom = ClassRoom::findOrFail($request->class_room_id);

        if ($classRoom->is_completed) {
            return back()->with('error', 'Cannot update wage. The class is already marked as completed.');
        }

        $teacher->classRooms()->updateExistingPivot(
            $request->class_room_id,
            [
                'hourly_wage' => $request->hourly_wage
            ]
        );

        return back()->with('success', 'Hourly wage updated successfully');
    }
}
