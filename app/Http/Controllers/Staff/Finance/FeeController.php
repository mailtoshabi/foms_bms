<?php

namespace App\Http\Controllers\Staff\Finance;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\FeeNotification;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'unpaid'); // default

        $query = Fee::with(['student', 'classRoom.classType'])
            ->whereHas('student')
            ->withSum('payments as paid_amount', 'paid_amount')
            ->withSum('refunds as total_refunded', 'amount')
            ->withMax('payments as last_payment_date', 'paid_date');

        // Enrolment dept sees admission fees only
        $staff = auth('staff')->user();
        if ($staff->hasRoleId(utility('id_enrolment_dept'))) {
            $query->where('type', 'admission');
        }

        // Tab & Status logic
        $status = $request->get('status');
        if ($tab === 'paid') {
            if ($status === 'partial') {
                $query->where('status', 'partial');
            } else {
                $query->where('status', 'paid');
            }
        } else {
            $query->where('status', 'unpaid');
            $fourDaysAgo = now()->subDays(4)->endOfDay();
            if ($tab === 'overdue') {
                $query->whereDate('due_date', '<', $fourDaysAgo);
            } else {
                $query->whereDate('due_date', '>=', $fourDaysAgo);
            }
        }

        // Filters
        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->class_room_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('from_date') || $request->filled('to_date')) {
            $dateType = $request->get('date_type');
            if (empty($dateType)) {
                $dateType = ($tab === 'paid') ? 'payment_date' : 'created_at';
            }

            if ($dateType === 'payment_date') {
                $query->whereHas('payments', function ($q) use ($request) {
                    if ($request->filled('from_date')) {
                        $q->whereDate('paid_date', '>=', $request->from_date);
                    }
                    if ($request->filled('to_date')) {
                        $q->whereDate('paid_date', '<=', $request->to_date);
                    }
                });
            } elseif ($dateType === 'due_date') {
                if ($request->filled('from_date')) {
                    $query->whereDate('due_date', '>=', $request->from_date);
                }
                if ($request->filled('to_date')) {
                    $query->whereDate('due_date', '<=', $request->to_date);
                }
            } else {
                if ($request->filled('from_date')) {
                    $query->whereDate('created_at', '>=', $request->from_date);
                }
                if ($request->filled('to_date')) {
                    $query->whereDate('created_at', '<=', $request->to_date);
                }
            }
        }

        // Check if any filter is applied
        $isFiltered = $request->anyFilled(['search', 'class_room_id', 'type', 'status', 'from_date', 'to_date', 'date_type']);

        $totalAmount = 0;
        if ($isFiltered) {
            if ($request->status === 'partial') {
                $totalAmount = (clone $query)
                    ->selectRaw('SUM(GREATEST(amount - (
                        (SELECT COALESCE(SUM(paid_amount), 0) FROM fee_payments WHERE fee_payments.fee_id = fees.id) - 
                        (SELECT COALESCE(SUM(amount), 0) FROM fee_refunds WHERE fee_refunds.fee_id = fees.id)
                    ), 0)) as total_remaining')
                    ->value('total_remaining') ?? 0;
            } else {
                $totalAmount = (clone $query)->sum('amount');
            }
        }

        // Sorting
        $sort = $request->get('sort', 'latest');

        if ($sort === 'due_date') {
            $query->orderBy('due_date');
        } elseif ($sort === 'amount') {
            $query->orderBy('amount', 'desc');
        } else {
            $query->latest();
        }

        $fees = $query->paginate(utility('pagination', 50))->withQueryString();

        // Pre-calculate attendance for monthly fees in the paginated set
        $studentIds = $fees->pluck('student_id')->unique()->filter();
        $classRoomIds = $fees->pluck('class_room_id')->unique()->filter();

        if ($studentIds->isNotEmpty() && $classRoomIds->isNotEmpty()) {
            $allMonthlyFees = DB::table('fees')
                ->whereIn('student_id', $studentIds)
                ->whereIn('class_room_id', $classRoomIds)
                ->where('type', 'monthly')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->groupBy(function ($item) {
                    return $item->student_id . '-' . $item->class_room_id;
                });

            $allAttendances = DB::table('student_attendance')
                ->join('class_hours', 'student_attendance.class_hour_id', '=', 'class_hours.id')
                ->select(
                    'student_attendance.student_id',
                    'class_hours.class_room_id',
                    'class_hours.completed_at',
                    'student_attendance.is_present'
                )
                ->whereIn('student_attendance.student_id', $studentIds)
                ->whereIn('class_hours.class_room_id', $classRoomIds)
                ->where('class_hours.status', 'completed')
                ->orderBy('class_hours.completed_at', 'asc')
                ->get()
                ->map(function ($item) {
                    $item->completed_at_parsed = \Carbon\Carbon::parse($item->completed_at);
                    return $item;
                })
                ->groupBy(function ($item) {
                    return $item->student_id . '-' . $item->class_room_id;
                });

            foreach ($fees as $fee) {
                if ($fee->type !== 'monthly') {
                    continue;
                }

                $key = $fee->student_id . '-' . $fee->class_room_id;

                $previousFee = null;
                $studentClassFees = isset($allMonthlyFees[$key]) ? $allMonthlyFees[$key] : null;
                if ($studentClassFees) {
                    $index = $studentClassFees->search(function ($item) use ($fee) {
                        return (int) $item->id === (int) $fee->id;
                    });
                    if ($index !== false && $index > 0) {
                        $previousFee = $studentClassFees->get($index - 1);
                    }
                }

                $total = 0;
                $present = 0;

                $studentClassAttendances = isset($allAttendances[$key]) ? $allAttendances[$key] : null;
                if ($studentClassAttendances) {
                    $feeCreatedAt = \Carbon\Carbon::parse($fee->created_at);
                    $prevFeeCreatedAt = $previousFee ? \Carbon\Carbon::parse($previousFee->created_at) : null;

                    foreach ($studentClassAttendances as $att) {
                        $completedAt = $att->completed_at_parsed;

                        if ($prevFeeCreatedAt) {
                            $inInterval = $completedAt->gt($prevFeeCreatedAt) && $completedAt->lte($feeCreatedAt);
                        } else {
                            $inInterval = $completedAt->lte($feeCreatedAt);
                        }

                        if ($inInterval) {
                            $total++;
                            if ($att->is_present) {
                                $present++;
                            }
                        }
                    }
                }

                $fee->attendance_total = $total;
                $fee->attendance_present = $present;
                $fee->attendance_percent = $total > 0 ? round(($present / $total) * 100) : 0;
                $fee->has_attendance = $total > 0;
            }
        }

        $classRoomSearchUrl = route('staff.class_rooms.search');
        $selectedClassName = $request->filled('class_room_id')
            ? optional(\App\Models\ClassRoom::select('name')->find($request->class_room_id))->name
            : null;

        return view('staff.finance.fees.index', compact('fees', 'classRoomSearchUrl', 'selectedClassName', 'tab', 'totalAmount', 'isFiltered'));
    }

    public function pay(Request $request)
    {
        $validated = $request->validate([
            'fee_id' => 'required|exists:fees,id',
            'paid_amount' => 'required|numeric|min:1',
            'payment_method' => 'required',
            'paid_date' => 'required|date'
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {

                $fee = Fee::with('student')->findOrFail($validated['fee_id']);

                if (!$fee->student || $fee->student->trashed()) {
                    throw new \Exception('Payment cannot be recorded for a deleted student.');
                }

                $student = $fee->student;
                $amountToPay = $validated['paid_amount'];

                // Total paid so far
                $totalPaid = FeePayment::where('fee_id', $fee->id)
                    ->sum('paid_amount');

                $remaining = $fee->amount - $totalPaid;

                if ($validated['payment_method'] === 'wallet') {
                    if ($student->wallet_balance < $amountToPay) {
                        throw new \Exception('Insufficient wallet balance. Available: ₹' . number_format($student->wallet_balance, 2));
                    }
                    if ($amountToPay > $remaining) {
                        throw new \Exception('Cannot pay more than the remaining fee amount of ₹' . number_format($remaining, 2) . ' using wallet.');
                    }

                    // Deduct from student's wallet
                    $student->decrement('wallet_balance', $amountToPay);

                    // Record wallet transaction
                    $student->walletTransactions()->create([
                        'fee_id' => $fee->id,
                        'amount' => -$amountToPay,
                        'type' => 'fee_payment',
                        'notes' => 'Manually paid fee using wallet balance (REC: ' . $fee->receipt_no . ')',
                    ]);
                } else {
                    // For non-wallet payments, validate that paid_amount doesn't exceed remaining
                    if ($amountToPay > $remaining) {
                        throw new \Exception('Payment amount of ₹' . number_format($amountToPay, 2) . ' exceeds the remaining fee amount of ₹' . number_format($remaining, 2) . '.');
                    }
                }

                $newTotal = $totalPaid + $amountToPay;

                // Save payment
                FeePayment::create([
                    'fee_id' => $fee->id,
                    'paid_amount' => $amountToPay,
                    'payment_method' => $validated['payment_method'],
                    'paid_date' => $validated['paid_date'],
                    'notes' => $request->notes
                ]);

                // Update fee status
                if ($newTotal >= $fee->amount) {

                    $fee->update([
                        'status' => 'paid'
                    ]);

                } else {

                    $fee->update([
                        'status' => 'partial'
                    ]);

                }

            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment recorded successfully');
    }

    public function destroy($id)
    {
        $fee = Fee::with(['student', 'classRoom'])->findOrFail($id);

        if ($fee->status !== 'unpaid' || $fee->type !== 'admission') {
            return back()->with('error', 'Only unpaid admission fees can be deleted.');
        }

        DB::transaction(function () use ($fee) {
            // Detach student from the class room
            if ($fee->student && $fee->class_room_id) {
                $fee->student->class_rooms()->detach($fee->class_room_id);
            }

            $fee->delete();
        });

        return back()->with('success', 'Fee deleted and student unassigned from class successfully.');
    }

    public function invoice($id)
    {
        $fee = Fee::with(['student', 'classRoom', 'payments'])
            ->findOrFail(decrypt($id));

        return view('staff.finance.fees.invoice', compact('fee'));
    }

    public function downloadInvoice($id)
    {
        $fee = Fee::with(['student', 'classRoom', 'payments'])
            ->where('status', 'paid')
            ->findOrFail($id);

        $pdf = Pdf::loadView('staff.finance.fees.invoice_pdf', compact('fee'))
            ->setPaper('a4', 'portrait');

        $filename = 'invoice_INV-' . str_pad($fee->id, 5, '0', STR_PAD_LEFT) . '_' . $fee->student->name . '.pdf';
        $filename = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $filename);

        return $pdf->download($filename);
    }

    public function getPayments($id)
    {
        $fee = Fee::with('payments')->findOrFail($id);

        return response()->json([
            'payments' => $fee->payments
        ]);
    }

    public function sendNotification(Request $request)
    {
        $validated = $request->validate([
            'fee_id' => 'required|exists:fees,id'
        ]);

        $fee = Fee::with('student')->findOrFail($validated['fee_id']);
        $notificationType = $request->get('type', 'due'); // 'due' or 'overdue'

        try {
            // Calculate days overdue if applicable
            $dueDate = \Carbon\Carbon::parse($fee->due_date);
            $daysOverdue = $dueDate->isPast() && $fee->status != 'paid' ? now()->diffInDays($dueDate) : 0;

            // Determine notification type if not explicitly set
            if (!$request->get('type')) {
                $notificationType = $daysOverdue > 4 ? 'overdue' : 'due';
            }

            // Prepare message
            $studentName = $fee->student->name;
            $amount = number_format($fee->amount, 2);
            $dueDate = $fee->due_date;
            $contactNumber = $fee->student->contact_number;

            if ($notificationType === 'overdue') {
                $message = "Dear {$studentName}, Your fee of ₹{$amount} is overdue since {$dueDate}. Please pay immediately. Contact school for assistance.";
            } else {
                $message = "Dear {$studentName}, Your fee of ₹{$amount} is due on {$dueDate}. Please make payment at the earliest.";
            }

            // Save notification record
            $notification = FeeNotification::create([
                'fee_id' => $fee->id,
                'type' => $notificationType,
                'recipient_phone' => $contactNumber,
                'message' => $message,
                'status' => 'sent'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully to ' . $contactNumber
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendBulkNotifications(Request $request)
    {
        $validated = $request->validate([
            'fee_ids' => 'required|array',
            'fee_ids.*' => 'exists:fees,id'
        ]);

        try {
            $successCount = 0;
            $failureCount = 0;

            // Load all fees and their students at once
            $fees = Fee::with('student')->whereIn('id', $validated['fee_ids'])->get();

            // Fetch already sent notifications for today at once
            $existingNotifications = FeeNotification::whereIn('fee_id', $validated['fee_ids'])
                ->whereDate('created_at', now()->toDateString())
                ->pluck('fee_id')
                ->toArray();

            foreach ($fees as $fee) {
                try {
                    // Calculate days overdue
                    $dueDate = \Carbon\Carbon::parse($fee->due_date);
                    $daysOverdue = $dueDate->isPast() && $fee->status != 'paid' ? now()->diffInDays($dueDate) : 0;
                    $notificationType = $daysOverdue > 4 ? 'overdue' : 'due';

                    // Prepare message
                    $studentName = $fee->student->name;
                    $amount = number_format($fee->amount, 2);
                    $dueDateStr = $fee->due_date;
                    $contactNumber = $fee->student->contact_number;

                    if ($notificationType === 'overdue') {
                        $message = "Dear {$studentName}, Your fee of ₹{$amount} is overdue since {$dueDateStr}. Please pay immediately.";
                    } else {
                        $message = "Dear {$studentName}, Your fee of ₹{$amount} is due on {$dueDateStr}. Please make payment.";
                    }

                    if (!in_array($fee->id, $existingNotifications)) {
                        FeeNotification::create([
                            'fee_id' => $fee->id,
                            'type' => $notificationType,
                            'recipient_phone' => $contactNumber,
                            'message' => $message,
                            'status' => 'sent'
                        ]);
                        $successCount++;
                    }

                } catch (\Exception $e) {
                    $failureCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Notifications sent: {$successCount} | Failed: {$failureCount}",
                'details' => "Successfully sent {$successCount} notifications"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing bulk notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    public function depositWallet(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $student = Student::findOrFail($validated['student_id']);
                $amount = $validated['amount'];

                // 1. Record deposit in wallet transactions
                $student->walletTransactions()->create([
                    'amount' => $amount,
                    'type' => 'deposit',
                    'payment_method' => $validated['payment_method'],
                    'notes' => $validated['notes'] ?? 'Wallet deposit'
                ]);

                // 2. Add to student wallet balance
                $student->increment('wallet_balance', $amount);

                // 3. Auto-allocate if autopay is enabled
                if ($student->is_wallet_autopay_enabled) {
                    $student->refresh();

                    // Fetch unpaid or partial fees sorted by due date ascending with eager loaded payments sum
                    $fees = Fee::where('student_id', $student->id)
                        ->whereIn('status', ['unpaid', 'partial'])
                        ->withSum('payments as paid_amount', 'paid_amount')
                        ->orderBy('due_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                    foreach ($fees as $fee) {
                        if ($student->wallet_balance <= 0) {
                            break;
                        }

                        $totalPaid = $fee->paid_amount ?? 0;
                        $remaining = $fee->amount - $totalPaid;

                        if ($remaining <= 0) {
                            continue;
                        }

                        $applyAmount = min($remaining, $student->wallet_balance);
                        if ($applyAmount <= 0) {
                            continue;
                        }

                        // Deduct from wallet
                        $student->decrement('wallet_balance', $applyAmount);

                        // Record wallet transaction
                        $student->walletTransactions()->create([
                            'fee_id' => $fee->id,
                            'amount' => -$applyAmount,
                            'type' => 'fee_payment',
                            'notes' => 'Auto-allocated wallet balance to ' . ucfirst($fee->type) . ' fee (REC: ' . $fee->receipt_no . ')',
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
                    }
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Wallet deposited successfully.');
    }

    public function refundWallet(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $student = Student::findOrFail($validated['student_id']);
                $amount = $validated['amount'];

                if ($student->wallet_balance < $amount) {
                    throw new \Exception('Insufficient wallet balance for refund. Available: ₹' . number_format($student->wallet_balance, 2));
                }

                // 1. Record refund in wallet transactions (negative amount)
                $student->walletTransactions()->create([
                    'amount' => -$amount,
                    'type' => 'refund',
                    'payment_method' => $validated['payment_method'],
                    'notes' => $validated['notes'] ?? 'Wallet refund'
                ]);

                // 2. Deduct from student wallet balance
                $student->decrement('wallet_balance', $amount);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Wallet balance refunded successfully.');
    }

    public function getRefunds($id)
    {
        $fee = Fee::with('refunds')->findOrFail($id);
        return response()->json([
            'refunds' => $fee->refunds
        ]);
    }
}

