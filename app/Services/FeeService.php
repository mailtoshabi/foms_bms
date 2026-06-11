<?php

namespace App\Services;

use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\ClassRoom;
use App\Models\ClassHour;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FeeService
{
    public function createFee(array $data)
    {
        $fee = Fee::create($data);
        $this->applyWalletBalance($fee);
        return $fee;
    }

    public function applyWalletBalance(Fee $fee)
    {
        $student = $fee->student;
        if (!$student || !$student->is_wallet_autopay_enabled || $student->wallet_balance <= 0) {
            return;
        }

        DB::transaction(function () use ($fee, $student) {
            $student->refresh();
            if ($student->wallet_balance <= 0) {
                return;
            }

            $totalPaid = FeePayment::where('fee_id', $fee->id)->sum('paid_amount');
            $remaining = $fee->amount - $totalPaid;
            if ($remaining <= 0) {
                return;
            }

            $applyAmount = min($remaining, $student->wallet_balance);
            if ($applyAmount <= 0) {
                return;
            }

            // Deduct from student wallet
            $student->decrement('wallet_balance', $applyAmount);

            // Record wallet transaction
            $student->walletTransactions()->create([
                'fee_id' => $fee->id,
                'amount' => -$applyAmount,
                'type' => 'fee_payment',
                'notes' => 'Applied wallet balance to ' . ucfirst($fee->type) . ' fee (REC: ' . $fee->receipt_no . ')',
            ]);

            // Record fee payment
            FeePayment::create([
                'fee_id' => $fee->id,
                'paid_amount' => $applyAmount,
                'payment_method' => 'wallet',
                'paid_date' => now()->toDateString(),
                'notes' => 'Paid using wallet balance (Autopay)'
            ]);

            // Update fee status
            $newPaid = $totalPaid + $applyAmount;
            $status = ($newPaid >= $fee->amount) ? 'paid' : 'partial';
            $fee->update(['status' => $status]);
        });
    }

    public function recordPayment($feeId, $amount)
    {
        FeePayment::create([
            'fee_id' => $feeId,
            'paid_amount' => $amount,
            'paid_date' => now(),
        ]);
    }

    public function generateGroupFeesForToday($date = null)
    {
        $today = $date ? Carbon::parse($date) : now();
        $dayOfMonth = $today->day;
        $isLastDayOfMonth = $today->copy()->isLastOfMonth();

        // 1. Only active classrooms (is_completed = false)
        // 2. Only if at least one month is complete from the classroom's starting date (prior to current month)
        $query = ClassRoom::with(['students', 'classType'])
            ->where('is_completed', false)
            ->whereDate('starting_date', '<', $today->copy()->startOfMonth()->toDateString())
            ->whereHas('classType', function ($q) {
                $q->where('name', 'group');
            });

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if ($isLastDayOfMonth) {
            if ($isSqlite) {
                $query->whereRaw('CAST(strftime("%d", starting_date) AS INTEGER) >= ?', [$dayOfMonth]);
            } else {
                $query->whereRaw('DAY(starting_date) >= ?', [$dayOfMonth]);
            }
        } else {
            if ($isSqlite) {
                $query->whereRaw('CAST(strftime("%d", starting_date) AS INTEGER) = ?', [$dayOfMonth]);
            } else {
                $query->whereRaw('DAY(starting_date) = ?', [$dayOfMonth]);
            }
        }

        $classRooms = $query->get();

        foreach ($classRooms as $classRoom) {

            foreach ($classRoom->students as $student) {

                // ✅ Skip inactive or monthly fee exempted students
                if ($student->status !== 'active' || $student->is_monthly_fee_exempted) {
                    continue;
                }

                // 3. Prevent duplicate fee for same billing cycle (within last 25 days)
                $exists = Fee::where('student_id', $student->id)
                    ->where('class_room_id', $classRoom->id)
                    ->where('type', 'monthly')
                    ->whereDate('created_at', '>=', $today->copy()->subDays(25)->toDateString())
                    ->exists();

                if ($exists) {
                    continue;
                }

                $amount = max(0, ($classRoom->monthly_fee ?? 0) - ($student->monthly_fee_discount ?? 0));

                if ($amount > 0) {
                    $fee = Fee::create([
                        'student_id' => $student->id,
                        'class_room_id' => $classRoom->id,
                        'amount' => $amount,
                        'due_date' => $today->copy()->addDays(7),
                        'status' => 'unpaid',
                        'type' => 'monthly',
                    ]);
                    $this->applyWalletBalance($fee);
                }
            }

            // Mark related completed class hours as calculated
            ClassHour::where('class_room_id', $classRoom->id)
                ->where('status', 'completed')
                ->where('has_fee_calculated', false)
                ->update(['has_fee_calculated' => true]);
        }
    }
}
