<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherDeposit;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherDeposit::with('teacher');

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deposits = $query->latest()->paginate(utility('pagination', 50))->withQueryString();
        $teachers = Teacher::pluck('name', 'id');

        return view('admin.deposits.index', compact('deposits', 'teachers'));
    }

    public function pay(Request $request)
    {
        $validated = $request->validate([
            'deposit_id' => 'required|exists:teacher_deposits,id',
            'amount_to_pay' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $deposit = TeacherDeposit::findOrFail($validated['deposit_id']);
        $remaining = $deposit->amount - $deposit->paid_amount;

        // Use a small epsilon to prevent floating point comparison issue
        if ($validated['amount_to_pay'] > ($remaining + 0.01)) {
            return back()->with('error', 'Payment amount cannot be greater than the remaining deposit amount.');
        }

        DB::transaction(function () use ($deposit, $validated) {
            $deposit->paid_amount += $validated['amount_to_pay'];
            
            // Append payment details to notes
            $newNotes = ($deposit->notes ? $deposit->notes . "\n" : "") . 
                "Paid: ₹" . number_format($validated['amount_to_pay'], 2) . 
                " on " . $validated['payment_date'] . 
                " via " . $validated['payment_method'] . 
                ($validated['reference_number'] ? " (Ref: " . $validated['reference_number'] . ")" : "") .
                ($validated['notes'] ? " - " . $validated['notes'] : "");
                
            $deposit->notes = $newNotes;

            if ($deposit->paid_amount >= $deposit->amount) {
                $deposit->status = 'paid';
                
                // Sync to salary if exists
                if ($deposit->salary) {
                    $deposit->salary->update([
                        'status' => 'paid',
                        'payment_date' => $validated['payment_date'],
                        'payment_method' => $validated['payment_method'],
                        'reference_number' => $validated['reference_number'],
                        'notes' => $newNotes,
                    ]);
                }
            } else {
                $deposit->status = 'not paid';
            }

            $deposit->payment_date = $validated['payment_date'];
            $deposit->payment_method = $validated['payment_method'];
            $deposit->reference_number = $validated['reference_number'];
            $deposit->save();
        });

        return back()->with('success', 'Deposit payment recorded successfully.');
    }
}
