<?php

namespace App\Services;

use App\Models\Expense;

class ExpenseService
{
    public function createExpense(array $data)
    {
        return Expense::create($data);
    }
}
