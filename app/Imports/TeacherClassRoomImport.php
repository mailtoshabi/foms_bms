<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Models\ClassRoom;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherClassRoomImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (!isset($row['phone']) || !isset($row['classroom_name']) || !isset($row['date'])) {
                continue;
            }

            $teacher = Teacher::where('phone', $row['phone'])->first();
            $classroom = ClassRoom::where('name', $row['classroom_name'])->first();

            if ($teacher && $classroom) {
                $date = $this->transformDate($row['date']);
                
                if ($date) {
                    DB::table('teacher_class_room')
                        ->where('teacher_id', $teacher->id)
                        ->where('class_room_id', $classroom->id)
                        ->update([
                            'assigned_at' => $date,
                            'created_at'  => $date,
                            'updated_at'  => $date,
                        ]);
                }
            }
        }
    }

    private function transformDate($value)
    {
        if (!$value) return null;

        try {
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
