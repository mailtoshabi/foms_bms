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
        $today = now();
        $dayOfMonth = $today->day;
        $isLastDayOfMonth = $today->copy()->endOfMonth()->isToday();

        $query = ClassRoom::with(['students', 'classType'])
            ->where('is_completed', false)
            ->whereDate('starting_date', '<=', $today->toDateString())
            ->whereHas('classType', function ($q) {
                $q->where('name', 'group');
            });

        if ($isLastDayOfMonth) {
            $query->whereRaw('DAY(starting_date) >= ?', [$dayOfMonth]);
        } else {
            $query->whereRaw('DAY(starting_date) = ?', [$dayOfMonth]);
        }

        $classRooms = $query->get();

        foreach ($classRooms as $classRoom) {

            foreach ($classRoom->students as $student) {

                // ✅ Prevent duplicate fee for same day
                $exists = Fee::where('student_id', $student->id)
                    ->where('class_room_id', $classRoom->id)
                    ->where('type', 'monthly')
                    ->whereDate('created_at', $today->toDateString())
                    ->exists();

                if ($exists) {
                    continue;
                }

                $amount = max(0, ($classRoom->monthly_fee ?? 0) - ($student->monthly_fee_discount ?? 0));

                if ($amount > 0) {
                    Fee::create([
                        'student_id' => $student->id,
                        'class_room_id' => $classRoom->id,
                        'amount' => $amount,
                        'due_date' => now()->addDays(7),
                        'status' => 'unpaid',
                        'type' => 'monthly',
                    ]);
                }
            }
        }
    }
}
