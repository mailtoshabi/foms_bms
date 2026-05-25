<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Country;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

use App\Imports\Concerns\TransformsDates;

class StudentBulkCreateImport implements ToCollection, WithHeadingRow
{
    use TransformsDates;
    public function collection(Collection $rows)
    {
        // Cache the default country to avoid repeated queries
        $defaultCountry = Country::where('code', '+91')->first();

        // Arrays to track duplicates within the excel sheet itself
        $phonesInExcel = [];
        $admissionsInExcel = [];
        $whatsappsInExcel = [];

        // First Pass: Validation
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 because index starts at 0 and row 1 is header

            $phone = trim((string) $row['phone']);
            if (is_numeric($phone) && str_contains($phone, '.')) {
                $phone = (string) intval($phone);
            }
            $phone = preg_replace('/[^0-9]/', '', $phone);

            if (empty($phone) || !isset($row['admission_no']) || !isset($row['name']) || !isset($row['starting_date'])) {
                continue;
            }

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

            if (!$country) {
                $country = $defaultCountry;
            }

            $countryId = $country?->id;

            // 1. Validate Phone
            if (in_array($phone, $phonesInExcel)) {
                throw new \Exception("Row {$rowNum}: Phone number {$phone} is duplicated in the excel sheet.");
            }
            $phonesInExcel[] = $phone;

            $existingPhone = Student::where('phone', $phone)
                ->when($countryId, function ($query, $countryId) {
                    return $query->where('country_id', $countryId);
                })
                ->first();

            if ($existingPhone) {
                throw new \Exception("Row {$rowNum}: Phone number {$phone} already exists in the system.");
            }

            // 2. Validate Admission No
            $admissionNo = trim((string) $row['admission_no']);
            if (in_array($admissionNo, $admissionsInExcel)) {
                throw new \Exception("Row {$rowNum}: Admission No {$admissionNo} is duplicated in the excel sheet.");
            }
            $admissionsInExcel[] = $admissionNo;

            $existingAdmission = Student::where('admission_no', $admissionNo)->first();
            if ($existingAdmission) {
                throw new \Exception("Row {$rowNum}: Admission No {$admissionNo} already exists in the system.");
            }

            // 3. Validate WhatsApp Number
            $whatsappNumber = trim((string) $row['whatsapp_number']);
            if (is_numeric($whatsappNumber) && str_contains($whatsappNumber, '.')) {
                $whatsappNumber = (string) intval($whatsappNumber);
            }
            $whatsappNumber = preg_replace('/[^0-9]/', '', $whatsappNumber);

            if (!empty($whatsappNumber) && $country) {
                $countryCodeWithoutPlus = ltrim($country->code, '+');
                if (!str_starts_with($whatsappNumber, $countryCodeWithoutPlus)) {
                    $whatsappNumber = $countryCodeWithoutPlus . $whatsappNumber;
                }
            }

            if (!empty($whatsappNumber)) {
                if (in_array($whatsappNumber, $whatsappsInExcel)) {
                    throw new \Exception("Row {$rowNum}: WhatsApp number {$whatsappNumber} is duplicated in the excel sheet.");
                }
                $whatsappsInExcel[] = $whatsappNumber;

                $existingWhatsapp = Student::where('whatsapp_number', $whatsappNumber)->first();
                if ($existingWhatsapp) {
                    throw new \Exception("Row {$rowNum}: WhatsApp number {$whatsappNumber} already exists in the system.");
                }
            }
        }

        // Second Pass: Insertion
        foreach ($rows as $row) {
            $phone = trim((string) $row['phone']);
            if (is_numeric($phone) && str_contains($phone, '.')) {
                $phone = (string) intval($phone);
            }
            $phone = preg_replace('/[^0-9]/', '', $phone);

            if (empty($phone) || !isset($row['admission_no']) || !isset($row['name']) || !isset($row['starting_date'])) {
                continue;
            }

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

            if (!$country) {
                $country = $defaultCountry;
            }

            $countryId = $country?->id;

            $date = $this->transformDate($row['starting_date']);

            $whatsappNumber = trim((string) $row['whatsapp_number']);
            if (is_numeric($whatsappNumber) && str_contains($whatsappNumber, '.')) {
                $whatsappNumber = (string) intval($whatsappNumber);
            }
            $whatsappNumber = preg_replace('/[^0-9]/', '', $whatsappNumber);

            if (!empty($whatsappNumber) && $country) {
                $countryCodeWithoutPlus = ltrim($country->code, '+');
                if (!str_starts_with($whatsappNumber, $countryCodeWithoutPlus)) {
                    $whatsappNumber = $countryCodeWithoutPlus . $whatsappNumber;
                }
            }

            // Set contact number same as phone
            $contactNumber = $phone;

            $isWhatsappDifferent = ($whatsappNumber && $whatsappNumber !== $contactNumber) ? 1 : 0;

            $student = new Student();
            $student->timestamps = false; // Prevent automatic created_at/updated_at updates
            $student->admission_no = $row['admission_no'];
            $student->country_id = $countryId;
            $student->name = $row['name'];
            $student->phone = $phone;
            $student->password = Hash::make($phone);
            $student->contact_number = $contactNumber;
            $student->whatsapp_number = $whatsappNumber;
            $student->is_whatsapp_different = $isWhatsappDifferent;
            $student->status = 'active'; // Default status

            if ($date) {
                $student->starting_date = $date->format('Y-m-d');
                $student->created_at = $date;
                $student->updated_at = $date;
            } else {
                $now = now();
                $student->starting_date = $now->format('Y-m-d');
                $student->created_at = $now;
                $student->updated_at = $now;
            }

            $student->save();
        }
    }

}
