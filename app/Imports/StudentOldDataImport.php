<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class StudentOldDataImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Identifier is 'phone'
            if (!isset($row['phone']) || !isset($row['admission_no']) || !isset($row['date'])) {
                continue;
            }

            $student = Student::where('phone', $row['phone'])->first();

            if ($student) {
                $date = $this->transformDate($row['date']);
                
                if ($date) {
                    $student->timestamps = false; // Prevent automatic updated_at updates
                    $student->admission_no = $row['admission_no'];
                    $student->created_at = $date;
                    $student->updated_at = $date;
                    $student->save();
                } else {
                    // Update only admission number if date is missing or invalid
                    $student->update([
                        'admission_no' => $row['admission_no']
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
