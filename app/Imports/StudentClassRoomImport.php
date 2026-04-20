<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Country;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentClassRoomImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // Cache the default country to avoid repeated queries
        $defaultCountry = Country::where('code', '+91')->first();

        foreach ($rows as $row) {
            // Sanitize phone to handle Excel formatting
            $phone = trim((string)$row['phone']);
            if (is_numeric($phone) && str_contains($phone, '.')) {
                 $phone = (string)intval($phone);
            }
            $phone = preg_replace('/[^0-9]/', '', $phone);

            if (empty($phone) || !isset($row['classroom_name']) || !isset($row['date'])) {
                continue;
            }

            // Determine country_id
            $country = null;
            if (isset($row['country_code'])) {
                $code = trim((string)$row['country_code']);
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

            $countryId = $country?->id;

            $student = Student::where('phone', $phone)
                ->when($countryId, function ($query, $countryId) {
                    return $query->where('country_id', $countryId);
                })
                ->first();

            $classroom = ClassRoom::where('name', $row['classroom_name'])->first();

            if ($student && $classroom) {
                $date = $this->transformDate($row['date']);
                
                if ($date) {
                    DB::table('student_class_room')
                        ->where('student_id', $student->id)
                        ->where('class_room_id', $classroom->id)
                        ->update([
                            'assigned_date' => $date,
                            'created_at'    => $date,
                            'updated_at'    => $date,
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
