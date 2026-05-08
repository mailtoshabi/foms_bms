<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Models\Country;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class TeacherOldDataImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // Cache the default country to avoid repeated queries
        $defaultCountry = Country::where('code', '+91')->first();

        foreach ($rows as $row) {
            // Sanitize phone to handle Excel formatting
            $phone = trim((string) $row['phone']);
            if (is_numeric($phone) && str_contains($phone, '.')) {
                $phone = (string) intval($phone);
            }
            $phone = preg_replace('/[^0-9]/', '', $phone);

            if (empty($phone) || !isset($row['salary_cycle_day']) || !isset($row['date'])) {
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
            } else {
                $date = $this->transformDate($row['date']);

                $newTeacher = new Teacher();
                $newTeacher->country_id = $country->id;
                $newTeacher->phone = $phone;
                $newTeacher->contact_number = $phone;
                $newTeacher->password = \Illuminate\Support\Facades\Hash::make($phone);
                $newTeacher->name = $row['name'] ?? 'Unknown';
                $newTeacher->salary_cycle_day = $row['salary_cycle_day'];

                if ($date) {
                    $newTeacher->created_at = $date;
                    $newTeacher->updated_at = $date;
                    $newTeacher->timestamps = false;
                }

                $newTeacher->save();
            }
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
