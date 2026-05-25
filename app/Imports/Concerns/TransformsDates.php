<?php

namespace App\Imports\Concerns;

use Carbon\Carbon;

trait TransformsDates
{
    /**
     * Transform a date value from Excel into a Carbon instance.
     * Handles both Excel serial numbers and various standard string formats.
     *
     * @param mixed $value
     * @return \Carbon\Carbon|null
     */
    protected function transformDate($value)
    {
        if (!$value) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }

            $value = trim($value);

            // 1. dd-mm-yyyy (e.g., 25-05-2026)
            if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $value)) {
                return Carbon::createFromFormat('d-m-Y', $value)->startOfDay();
            }

            // 2. dd-mm-yy (e.g., 01-08-25)
            if (preg_match('/^\d{1,2}-\d{1,2}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('d-m-y', $value)->startOfDay();
            }

            // 3. dd/mm/yyyy (e.g., 25/05/2026)
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->startOfDay();
            }

            // 4. dd/mm/yy (e.g., 01/08/25)
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2}$/', $value)) {
                return Carbon::createFromFormat('d/m/y', $value)->startOfDay();
            }

            // 5. yyyy-mm-dd (e.g., 2026-05-25)
            if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            }

            // 6. yyyy/mm/dd (e.g., 2026/05/25)
            if (preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $value)) {
                return Carbon::createFromFormat('Y/m/d', $value)->startOfDay();
            }

            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
