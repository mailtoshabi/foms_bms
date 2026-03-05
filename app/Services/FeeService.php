<?php

namespace App\Services;

use App\Models\Fee;
use App\Models\FeePayment;

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
}
