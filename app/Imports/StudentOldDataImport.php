<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Country;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

use App\Imports\Concerns\TransformsDates;

class StudentOldDataImport implements ToCollection, WithHeadingRow
{
    use TransformsDates;
    public function collection(Collection $rows)
    {
        // Cache the default country to avoid repeated queries
        $defaultCountry = Country::where('code', '+91')->first();

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

}
