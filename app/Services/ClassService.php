<?php
namespace App\Services;

use App\Models\ClassRoom;
use App\Models\Fee;
use App\Models\ClassHour;
use App\Models\StudentAttendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClassService
{

    public function create(array $data)
    {
        return DB::transaction(fn() => ClassRoom::create($data));
    }

    public function update(int $classId, array $data)
    {
        $class = ClassRoom::findOrFail($classId);
        $class->update($data);

        return $class;
    }

    public function delete(int $classId)
    {
        $class = ClassRoom::findOrFail($classId);
        $class->delete(); // soft delete — sets deleted_at

        return $class;
    }

    public function toggleStatus(int $classId)
    {
        $class = ClassRoom::findOrFail($classId);

        $class->update([
            'is_completed' => !$class->is_completed
        ]);

        return $class;
    }

    /*
    |------------------------------------------------------------------
    | Assign Teacher
    |------------------------------------------------------------------
    */
    public function assignTeacher(int $classId, int $teacherId, float $wage)
    {
        return DB::transaction(function () use ($classId, $teacherId, $wage) {

            $class = ClassRoom::with('teachers')->findOrFail($classId);

            // Only one teacher allowed
            if ($class->teachers()->exists()) {
                return [
                    'status' => false,
                    'message' => 'Teacher already assigned. Remove existing teacher first.'
                ];
            }

            $class->teachers()->attach($teacherId, [
                'hourly_wage' => $wage,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'status' => true
            ];
        });
    }

    public function removeTeacher(int $classId, int $teacherId)
    {
        return DB::transaction(function () use ($classId, $teacherId) {

            // 1. Delete pending class hours for this teacher in this classroom
            ClassHour::where('teacher_id', $teacherId)
                ->where('class_room_id', $classId)
                ->where('status', 'pending')
                ->delete();

            // 2. Detach teacher from classroom
            $class = ClassRoom::findOrFail($classId);
            $class->teachers()->detach($teacherId);

        });
    }

    /*
    |------------------------------------------------------------------
    | Assign Students
    |------------------------------------------------------------------
    */
    public function assignStudents(int $classId, array $studentIds)
    {
        return DB::transaction(function () use ($classId, $studentIds) {

            $class = ClassRoom::with('classType', 'students')->findOrFail($classId);

            $type = $class->classType->name; // assuming 'individual' or 'group'

            // 🔴 INDIVIDUAL CLASS LOGIC
            if ($type === 'individual') {

                // already has a student
                if ($class->students()->count() > 0) {
                    return [
                        'status' => false,
                        'message' => 'Only one student allowed for individual class.'
                    ];
                }

                // more than one selected
                if (count($studentIds) > 1) {
                    return [
                        'status' => false,
                        'message' => 'You can select only one student for individual class.'
                    ];
                }

            }

            // Prepare attach data with pivot
            $attachData = [];

            foreach ($studentIds as $studentId) {

                if ($class->students()->where('student_id', $studentId)->exists()) {
                    continue;
                }

                $attachData[$studentId] = [
                    'assigned_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($attachData)) {
                $class->students()->attach($attachData);
            }

            // =========================
            // Fee Generation per student
            // =========================
            foreach (array_keys($attachData) as $studentId) {

                $student = Student::findOrFail($studentId);

                $isAdmissionExempted = $student->is_admission_fee_exempted;
                $isMonthlyExempted = $student->is_monthly_fee_exempted;

                // Skip if admission exempted
                if ($isAdmissionExempted) {
                    continue;
                }

                $feeType = 'admission';
                $feeAmount = max(0, $class->admission_fee - $student->admission_fee_discount);

                // Skip if fee already exists for this student/class/type
                if (
                    Fee::where('student_id', $student->id)
                        ->where('class_room_id', $class->id)
                        ->where('type', $feeType)
                        ->exists()
                ) {
                    continue;
                }

                if ($feeAmount > 0) {
                    Fee::create([
                        'student_id' => $student->id,
                        'class_room_id' => $class->id,
                        'type' => $feeType,
                        'amount' => $feeAmount,
                        'due_date' => Carbon::parse($class->starting_date)->addDays(7),
                        'status' => 'unpaid',
                    ]);
                }
            }

            return [
                'status' => true,
                'message' => 'Students added successfully'
            ];

        });
    }

    public function removeStudent(int $classId, int $studentId)
    {
        return DB::transaction(function () use ($classId, $studentId) {

            $class = ClassRoom::with('classType', 'students')->findOrFail($classId);

            // 1. Delete related unpaid fees for this student in this classroom
            Fee::where('student_id', $studentId)
                ->where('class_room_id', $classId)
                ->where('status', 'unpaid')
                ->delete();

            // 2. Detach student from classroom
            $class->students()->detach($studentId);

            // 3. Handle pending class hours based on classroom type and remaining students
            $type = strtolower($class->classType->name ?? '');
            $remainingStudentsCount = $class->students()->count();

            $pendingClassHours = ClassHour::where('class_room_id', $classId)
                ->where('status', 'pending');

            if ($type === 'individual' || ($type === 'group' && $remainingStudentsCount === 0)) {

                // Case: Individual class OR group class with no students left -> Delete pending class hours
                $pendingClassHours->delete();

            } else {

                // Case: Group class with other students -> Keep class hours, but remove this student's attendance records (if any)
                $pendingIds = $pendingClassHours->pluck('id');
                if ($pendingIds->isNotEmpty()) {
                    StudentAttendance::whereIn('class_hour_id', $pendingIds)
                        ->where('student_id', $studentId)
                        ->delete();
                }
            }

            return true;
        });
    }

}
