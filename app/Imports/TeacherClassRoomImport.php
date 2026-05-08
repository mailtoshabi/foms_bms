<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Models\Country;
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
        // Cache the default country to avoid repeated queries
        $defaultCountry = Country::where('code', '+91')->first();
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Assuming row 1 is header

            // Sanitize phone to handle Excel formatting
            $phone = trim((string) $row['phone']);
            if (is_numeric($phone) && str_contains($phone, '.')) {
                $phone = (string) intval($phone);
            }
            $phone = preg_replace('/[^0-9]/', '', $phone);

            if (empty($phone) || !isset($row['classroom_name']) || !isset($row['date'])) {
                $errors[] = "Row {$rowNumber}: Missing required fields (phone, classroom_name, or date)";
                continue;
            }

            // Determine country_id
            $country = null;
            if (isset($row['country_code'])) {
                $code = trim((string) $row['country_code']);
                if (!empty($code)) {
                    if (!str_starts_with($code, '+')) {
                        $code = '+' . $code;
                    }
                    $country = Country::where('code', $code)->first();
                }
            }

            // Default to India (+91) if country not found or missing
            if (!$country) {
                $country = $defaultCountry;
            }

            $teacher = Teacher::where('country_id', $country->id)->where('phone', $phone)->first();
            $classroom = ClassRoom::where('name', $row['classroom_name'])->first();

            if (!$teacher) {
                $errors[] = "Row {$rowNumber}: Teacher not found with phone {$phone}";
            }
            if (!$classroom) {
                $errors[] = "Row {$rowNumber}: Classroom not found with name {$row['classroom_name']}";
            }

            if ($teacher && $classroom) {
                $date = $this->transformDate($row['date']);

                if ($date) {
                    DB::table('teacher_class_room')
                        ->updateOrInsert(
                            [
                                'teacher_id' => $teacher->id,
                                'class_room_id' => $classroom->id
                            ],
                            [
                                'assigned_at' => $date,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ]
                        );
                } else {
                    $errors[] = "Row {$rowNumber}: Invalid date format ({$row['date']})";
                }
            }
        }

        if (!empty($errors)) {
            throw new \Exception(implode(' | ', $errors));
        }
    }

    private function transformDate($value)
    {
        if (!$value)
            return null;

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
