<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Fee;
use App\Services\FeeService;
use Carbon\Carbon;

class FeeController extends Controller
{
    protected $feeService;

    public function __construct(FeeService $feeService)
    {
        $this->feeService = $feeService;
    }

    public function create()
    {
        return view('admin.fees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_room_id' => 'required|exists:class_rooms,id',
            'type' => 'required|in:admission,monthly',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'status' => 'required|in:paid,unpaid',
        ]);

        $dueDate = Carbon::parse($validated['date'])->addDays(7)->toDateString();

        // Check uniqueness unique(['student_id', 'class_room_id', 'due_date', 'type'])
        $exists = Fee::where('student_id', $validated['student_id'])
            ->where('class_room_id', $validated['class_room_id'])
            ->where('due_date', $dueDate)
            ->where('type', $validated['type'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'A fee with the same student, class, type, and due date already exists.');
        }

        try {
            $fee = $this->feeService->createFee([
                'student_id' => $validated['student_id'],
                'class_room_id' => $validated['class_room_id'],
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'due_date' => $dueDate,
                'status' => $validated['status'],
            ]);

            if ($validated['status'] === 'paid') {
                \App\Models\FeePayment::create([
                    'fee_id' => $fee->id,
                    'paid_amount' => $validated['amount'],
                    'payment_method' => 'cash',
                    'paid_date' => $validated['date'],
                    'notes' => 'Manually marked as Paid at creation'
                ]);
            }

            return redirect()->route('admin.reports.fee')->with('success', 'Manual fee created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create fee: ' . $e->getMessage());
        }
    }

    public function searchStudents(Request $request)
    {
        $term = $request->input('q', '');
        $results = Student::where('name', 'like', "%{$term}%")
            ->orWhere('contact_number', 'like', "%{$term}%")
            ->orWhere('admission_no', 'like', "%{$term}%")
            ->limit(30)
            ->get()
            ->map(fn($student) => [
                'id' => $student->id,
                'text' => $student->name . ($student->admission_no ? ' (' . $student->admission_no . ')' : ''),
            ]);

        return response()->json(['results' => $results]);
    }
}
