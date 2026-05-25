<?php

namespace App\Imports;

use App\Models\ClassRoom;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

use App\Imports\Concerns\TransformsDates;

class ClassRoomStartingDateImport implements ToCollection, WithHeadingRow
{
    use TransformsDates;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (!isset($row['name']) || !isset($row['starting_date'])) {
                continue;
            }

            $class = ClassRoom::where('name', $row['name'])->first();

            if ($class) {
                $date = $this->transformDate($row['starting_date']);
                
                if ($date) {
                    $class->update([
                        'starting_date' => $date,
                        'created_at'    => $date,
                        'updated_at'    => $date,
                    ]);
                }
            }
        }
    }
}
