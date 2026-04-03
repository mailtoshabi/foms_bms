<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;

class ExpenseService
{
    public function getExpenses($request = null)
    {
        $query = Expense::with('category');

        if ($request) {
            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by date range
            if ($request->filled('from_date')) {
                $query->whereDate('expense_date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('expense_date', '<=', $request->to_date);
            }

            // Search by remarks
            if ($request->filled('search')) {
                $query->where('remarks', 'like', '%' . $request->search . '%');
            }

            // Sorting
            $sort = $request->get('sort', 'latest');
            if ($sort === 'amount') {
                $query->orderBy('amount', 'desc');
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        return $query;
    }

    public function createExpense(array $data)
    {
        return Expense::create($data);
    }

    public function updateExpense($expenseId, array $data)
    {
        $expense = Expense::findOrFail($expenseId);
        $expense->update($data);
        return $expense;
    }

    public function deleteExpense($expenseId)
    {
        $expense = Expense::findOrFail($expenseId);
        $expense->delete();
        return true;
    }

    public function getCategories()
    {
        return ExpenseCategory::pluck('name', 'id');
    }

    public function getTotalExpenses($query = null)
    {
        if ($query) {
            return $query->sum('amount');
        }
        return Expense::sum('amount');
    }
}

