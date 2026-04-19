<?php

namespace App\Imports;

use App\Models\Teacher;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class TeacherOldDataImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Sanitize phone to handle Excel formatting
            $phone = trim((string)$row['phone']);
            if (is_numeric($phone) && str_contains($phone, '.')) {
                 $phone = (string)intval($phone);
            }
            $phone = preg_replace('/[^0-9]/', '', $phone);

            if (empty($phone) || !isset($row['salary_cycle_day']) || !isset($row['date'])) {
                continue;
            }

            $teacher = Teacher::where('phone', $phone)->first();

            if ($teacher) {
                $date = $this->transformDate($row['date']);
                
                if ($date) {
                    $teacher->timestamps = false;
                    $teacher->salary_cycle_day = $row['salary_cycle_day'];
                    $teacher->created_at = $date;
                    $teacher->updated_at = $date;
                    $teacher->save();
                } else {
                    $teacher->update([
                        'salary_cycle_day' => $row['salary_cycle_day']
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
