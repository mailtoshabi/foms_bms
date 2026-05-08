<?php

namespace App\Http\Controllers\Staff\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function index(Request $request)
    {
        $query = $this->expenseService->getExpenses($request);
        $expenses = $query->paginate(utility('pagination', 50))->withQueryString();

        $categories = $this->expenseService->getCategories();
        $totalExpense = $this->expenseService->getTotalExpenses($query);

        return view('staff.finance.expenses.index', compact('expenses', 'categories', 'totalExpense'));
    }

    public function create()
    {
        $categories = $this->expenseService->getCategories();
        return view('staff.finance.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'remarks' => 'nullable|string'
        ]);

        $this->expenseService->createExpense($validated);

        return redirect()->route('staff.expenses.index')
            ->with('success', 'Expense created successfully');
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $categories = $this->expenseService->getCategories();

        return view('staff.finance.expenses.create', compact('expense', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'remarks' => 'nullable|string'
        ]);

        $this->expenseService->updateExpense($id, $validated);

        return redirect()->route('staff.expenses.index')
            ->with('success', 'Expense updated successfully');
    }

    public function destroy($id)
    {
        $this->expenseService->deleteExpense($id);

        return redirect()->route('staff.expenses.index')
            ->with('success', 'Expense deleted successfully');
    }
}
