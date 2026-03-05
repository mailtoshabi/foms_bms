<?php

namespace App\Services;

use App\Models\ClassRoom;
use Illuminate\Support\Facades\DB;

class ClassService
{
    /*
    |--------------------------------------------------------------------------
    | Create Class
    |--------------------------------------------------------------------------
    */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            return ClassRoom::create($data);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update Class
    |--------------------------------------------------------------------------
    */
    public function update(int $classId, array $data)
    {
        $class = ClassRoom::findOrFail($classId);

        $class->update($data);

        return $class;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Class
    |--------------------------------------------------------------------------
    */
    public function delete(int $classId)
    {
        $class = ClassRoom::findOrFail($classId);

        return $class->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Change Completion Status
    |--------------------------------------------------------------------------
    */
    public function toggleStatus(int $classId)
    {
        $class = ClassRoom::findOrFail($classId);

        $class->update([
            'is_completed' => !$class->is_completed
        ]);

        return $class;
    }

    /*
    |--------------------------------------------------------------------------
    | Assign Student
    |--------------------------------------------------------------------------
    */
    public function assignStudent($classId, $studentId)
    {
        $class = ClassRoom::findOrFail($classId);

        $class->students()->syncWithoutDetaching([$studentId]);
    }

    /*
    |--------------------------------------------------------------------------
    | Change Teacher (Optional Feature)
    |--------------------------------------------------------------------------
    */
    public function changeTeacher($classId, $teacherId)
    {
        $class = ClassRoom::findOrFail($classId);

        $class->update([
            'teacher_id' => $teacherId
        ]);
    }
}
