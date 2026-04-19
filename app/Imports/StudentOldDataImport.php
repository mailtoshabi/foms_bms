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
            // Identifier is 'phone' - Sanitize to handle Excel formatting
            $phone = trim((string)$row['phone']);
            // Remove non-numeric characters (handles potential spaces, dashes, or scientific notation dots)
            if (is_numeric($phone) && str_contains($phone, '.')) {
                 $phone = (string)intval($phone);
            }
            $phone = preg_replace('/[^0-9]/', '', $phone);

            if (empty($phone) || !isset($row['admission_no']) || !isset($row['date'])) {
                continue;
            }

            $student = Student::where('phone', $phone)->first();

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
