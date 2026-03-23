<?php
namespace App\Services;

use App\Models\ClassRoom;
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
        return ClassRoom::findOrFail($classId)->delete();
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

            $class = ClassRoom::with('classType','students')->findOrFail($classId);

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

                if ($class->students()->where('student_id',$studentId)->exists()) {
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

            return [
                'status' => true,
                'message' => 'Students added successfully'
            ];

        });
    }

    public function removeStudent(int $classId, int $studentId)
    {
        return DB::transaction(function () use ($classId, $studentId) {

            $class = ClassRoom::findOrFail($classId);

            $class->students()->detach($studentId);

        });
    }

}
