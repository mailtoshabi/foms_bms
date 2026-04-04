<?php

namespace App\Services;

use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\ClassRoom;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FeeService
{
    public function createFee(array $data)
    {
        return Fee::create($data);
    }

    public function recordPayment($feeId, $amount)
    {
        FeePayment::create([
            'fee_id' => $feeId,
            'paid_amount' => $amount,
            'paid_date' => now(),
        ]);
    }

    public function generateGroupFeesForToday()
    {
        $today = now()->toDateString();

        $classRooms = ClassRoom::with(['students', 'classType'])
            ->whereDate('starting_date', $today)

            // ✅ FIXED: filter using class_types.name
            ->whereHas('classType', function ($q) {
                $q->where('name', 'group');
            })

            ->get();

        foreach ($classRooms as $classRoom) {

            foreach ($classRoom->students as $student) {

                // ✅ Prevent duplicate fee for same day
                $exists = Fee::where('student_id', $student->id)
                    ->where('class_room_id', $classRoom->id)
                    ->whereDate('due_date', $today)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $amount = max(0, ($classRoom->monthly_fee ?? 0) - ($student->monthly_fee_discount ?? 0));

                if ($amount > 0) {
                    Fee::create([
                        'student_id'    => $student->id,
                        'class_room_id' => $classRoom->id,
                        'amount'        => $amount, // adjust if needed
                        'due_date'      => Carbon::parse($classRoom->starting_date)->addDays(7),
                        'status'        => 'unpaid',
                        'type'          => 'monthly',
                    ]);
                }
            }
        }
    }
}
