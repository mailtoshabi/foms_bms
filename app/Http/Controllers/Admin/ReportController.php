<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Exports\FeeExport;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\FeeCollectionExport;
use App\Exports\AttendanceExport;
use App\Exports\TeacherSalaryExport;
use App\Exports\StudentLeadExport;
use App\Exports\StudentExport;
use App\Exports\StaffExport;
use App\Exports\StaffSalaryExport;
use App\Exports\TeacherLeadExport;
use App\Exports\TeacherExport;
use App\Models\ClassNote;
use App\Models\ClassRoom;
use App\Models\Fee;
use App\Models\LeadNote;
use App\Models\Staff;
use App\Models\StudentLead;
use App\Models\Student;
use App\Models\StaffSalary;
use App\Models\TeacherLead;
use App\Models\TeacherLeadNote;
use App\Models\Teacher;
use App\Models\ClassHour;
use App\Services\ExpenseService;

class ReportController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function fees(Request $request)
    {

        $tab = $request->get('tab', 'unpaid'); // default

        $query = Fee::with(['student', 'classRoom', 'refunds']);
        // ->withSum('payments as paid_amount', 'paid_amount');

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
                $matchingFees = (clone $query)->with(['payments', 'refunds'])->get();
                $totalAmount = $matchingFees->sum(function ($fee) {
                    $totalPaid = $fee->payments->sum('paid_amount');
                    $totalRefunded = $fee->refunds->sum('amount');
                    return max($fee->amount - ($totalPaid - $totalRefunded), 0);
                });
            } else {
                $totalAmount = (clone $query)->sum('amount');
            }
        }

        // Sorting
        $sort = $request->get('sort', 'latest');

        if ($sort === 'due_date') {
            $query->orderBy('due_date', 'desc');
        } elseif ($sort === 'amount') {
            $query->orderBy('amount', 'desc');
        } else {
            $query->latest();
        }

        $fees = $query->paginate(utility('pagination', 50))->withQueryString();

        $classRoomSearchUrl = route('admin.class_rooms.search');
        $selectedClassName = $request->filled('class_room_id')
            ? optional(\App\Models\ClassRoom::find($request->class_room_id))->name
            : null;

        return view('admin.reports.fees', compact('fees', 'classRoomSearchUrl', 'selectedClassName', 'tab', 'totalAmount', 'isFiltered'));
    }

    public function exportFee(Request $request)
    {
        $dateTime = Carbon::now()->format('d-m-Y_h-i-A');

        $fileName = 'fee-report_' . $dateTime . '.xlsx';

        return Excel::download(
            new FeeExport($request->all()),
            $fileName
        );
    }

    public function destroyFee($id)
    {
        $fee = Fee::with(['student', 'classRoom'])->findOrFail($id);

        if ($fee->status !== 'unpaid') {
            return back()->with('error', 'Only unpaid fees can be deleted.');
        }

        DB::transaction(function () use ($fee) {
            // Detach student from the class room if applicable
            // if ($fee->type === 'admission' && $fee->student && $fee->class_room_id) {
            //     $fee->student->class_rooms()->detach($fee->class_room_id);
            // }

            $fee->delete();
        });

        return back()->with('success', 'Fee deleted successfully.');
    }

    public function feeCollection(Request $request)
    {
        $query = DB::table('fee_payments')
            ->join('fees', 'fees.id', '=', 'fee_payments.fee_id')
            ->join('students', 'students.id', '=', 'fees.student_id')
            ->leftJoin('countries', 'countries.id', '=', 'students.country_id')
            ->join('class_rooms', 'class_rooms.id', '=', 'fees.class_room_id')
            ->join('courses', 'courses.id', '=', 'class_rooms.course_id')
            ->join('course_categories', 'course_categories.id', '=', 'courses.category_id')

            ->select(
                'students.id as student_id',
                'students.name',
                DB::raw("IF(countries.id IS NOT NULL, CONCAT(countries.code, ' (', countries.name, ') ', students.contact_number), students.contact_number) as contact_number"),
                'students.whatsapp_number',
                'students.is_whatsapp_different',
                'class_rooms.id as class_room_id',
                'class_rooms.name as class_name',
                'course_categories.name as category_name',
                'fee_payments.paid_amount',
                'fee_payments.payment_method',
                'fee_payments.paid_date'
            );

        $refundsQuery = DB::table('fee_refunds')
            ->join('fees', 'fees.id', '=', 'fee_refunds.fee_id')
            ->join('students', 'students.id', '=', 'fees.student_id')
            ->leftJoin('countries', 'countries.id', '=', 'students.country_id')
            ->join('class_rooms', 'class_rooms.id', '=', 'fees.class_room_id')
            ->join('courses', 'courses.id', '=', 'class_rooms.course_id')
            ->join('course_categories', 'course_categories.id', '=', 'courses.category_id');

        // Filters
        if ($request->filled('search')) {
            $query->where('students.name', 'like', '%' . $request->search . '%');
            $refundsQuery->where('students.name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('payment_method')) {
            $query->where('fee_payments.payment_method', $request->payment_method);
            $refundsQuery->where('fee_refunds.payment_method', $request->payment_method);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('fee_payments.paid_date', '>=', $request->from_date);
            $refundsQuery->whereDate('fee_refunds.refund_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('fee_payments.paid_date', '<=', $request->to_date);
            $refundsQuery->whereDate('fee_refunds.refund_date', '<=', $request->to_date);
        }

        if ($request->filled('category_id')) {
            $query->where('course_categories.id', $request->category_id);
            $refundsQuery->where('course_categories.id', $request->category_id);
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_rooms.id', $request->class_room_id);
            $refundsQuery->where('class_rooms.id', $request->class_room_id);
        }

        // Check if any filter is applied
        $isFiltered = $request->anyFilled(['search', 'category_id', 'class_room_id', 'payment_method', 'from_date', 'to_date']);

        $totalGross = 0;
        $totalRefunded = 0;
        $totalNet = 0;
        if ($isFiltered) {
            $totalGross = (clone $query)->sum('fee_payments.paid_amount');
            $totalRefunded = $refundsQuery->sum('fee_refunds.amount');
            $totalNet = $totalGross - $totalRefunded;
        }

        $data = $query->latest('fee_payments.paid_date')->paginate(utility('pagination', 50))->withQueryString();

        // Get course categories for dropdown
        $categories = \App\Models\CourseCategory::orderBy('name', 'asc')->pluck('name', 'id');

        $selectedClassName = $request->filled('class_room_id')
            ? optional(\App\Models\ClassRoom::find($request->class_room_id))->name
            : null;

        return view('admin.reports.fee_collection', compact('data', 'categories', 'selectedClassName', 'totalGross', 'totalRefunded', 'totalNet', 'isFiltered'));
    }

    public function exportFeeCollection(Request $request)
    {
        // 📅 File name with date & time
        $dateTime = Carbon::now()->format('Y-m-d_H-i');
        $fileName = 'fee-collection-report_' . $dateTime . '.xlsx';

        return Excel::download(
            new FeeCollectionExport($request->all()),
            $fileName
        );
    }

    public function financeExpense(Request $request)
    {
        $staffSalary = DB::table('staff_salary_payments as ssp')
            ->join('staff_salaries as ss', 'ss.id', '=', 'ssp.staff_salary_id')
            ->join('staffs as s', 's.id', '=', 'ss.staff_id')
            ->selectRaw("'staff_salary' as source")
            ->selectRaw('s.name as person_name')
            ->selectRaw("'Staff Salary Payment' as particular")
            ->selectRaw('ssp.paid_amount as amount')
            ->selectRaw('ssp.paid_date as transaction_date')
            ->selectRaw('ssp.payment_method as payment_method')
            ->selectRaw('COALESCE(ssp.notes, "") as remarks');

        $teacherSalary = DB::table('teacher_salaries as ts')
            ->join('teachers as t', 't.id', '=', 'ts.teacher_id')
            ->whereIn('ts.status', ['paid', 'partial'])
            ->whereNotNull('ts.payment_date')
            ->selectRaw("'teacher_salary' as source")
            ->selectRaw('t.name as person_name')
            ->selectRaw("'Teacher Salary Payment' as particular")
            ->selectRaw('ts.total_amount as amount')
            ->selectRaw('ts.payment_date as transaction_date')
            ->selectRaw('ts.payment_method as payment_method')
            ->selectRaw('COALESCE(ts.notes, "") as remarks');

        $expenses = DB::table('expenses as e')
            ->leftJoin('expense_categories as ec', 'ec.id', '=', 'e.category_id')
            ->selectRaw("'expense' as source")
            ->selectRaw("'' as person_name")
            ->selectRaw('COALESCE(ec.name, "Expense") as particular')
            ->selectRaw('e.amount as amount')
            ->selectRaw('e.expense_date as transaction_date')
            ->selectRaw('NULL as payment_method')
            ->selectRaw('COALESCE(e.remarks, "") as remarks');

        $baseQuery = DB::query()->fromSub(
            $staffSalary->unionAll($teacherSalary)->unionAll($expenses),
            'finance_expenses'
        );

        if ($request->filled('type')) {
            $baseQuery->where('source', $request->type);
        }

        if ($request->filled('from_date')) {
            $baseQuery->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $baseQuery->whereDate('transaction_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('person_name', 'like', '%' . $search . '%')
                    ->orWhere('particular', 'like', '%' . $search . '%')
                    ->orWhere('remarks', 'like', '%' . $search . '%');
            });
        }

        $totalsQuery = clone $baseQuery;

        if (!$request->filled('from_date') && !$request->filled('to_date')) {
            $totalsQuery->whereMonth('transaction_date', date('m'))
                ->whereYear('transaction_date', date('Y'));
        }

        $totalAmount = (clone $totalsQuery)->sum('amount');

        $sourceTotals = (clone $totalsQuery)
            ->select('source', DB::raw('SUM(amount) as total'))
            ->groupBy('source')
            ->pluck('total', 'source');

        $data = (clone $baseQuery)
            ->orderByDesc('transaction_date')
            ->paginate(utility('pagination', 50))
            ->withQueryString();

        $categories = $this->expenseService->getCategories();

        return view('admin.reports.finance_expense', compact('data', 'totalAmount', 'sourceTotals', 'categories'));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'remarks' => 'nullable|string'
        ]);

        $this->expenseService->createExpense($validated);

        return redirect()->back()->with('success', 'Expense added successfully');
    }

    public function attendance(Request $request)
    {
        $query = DB::table('student_attendance')
            ->join('students', 'students.id', '=', 'student_attendance.student_id')
            ->leftJoin('countries', 'countries.id', '=', 'students.country_id')
            ->join('class_hours', 'class_hours.id', '=', 'student_attendance.class_hour_id')
            ->join('class_rooms', 'class_rooms.id', '=', 'class_hours.class_room_id')

            ->select(
                'students.id as student_id',
                'students.name',
                DB::raw("IF(countries.id IS NOT NULL, CONCAT(countries.code, ' (', countries.name, ') ', students.contact_number), students.contact_number) as contact_number"),
                'students.whatsapp_number',
                'students.is_whatsapp_different',
                'class_rooms.id as class_id',
                'class_rooms.name as class_name',
                'class_hours.link_updated_at',
                'class_hours.google_meet_link',
                'student_attendance.is_present',
                'student_attendance.created_at'
            );

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('students.name', 'like', "%$search%")
                    ->orWhere('students.contact_number', 'like', "%$search%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('class_hours.link_updated_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('class_hours.link_updated_at', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $query->where('student_attendance.is_present', $request->status);
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_rooms.id', $request->class_room_id);
        }

        $hasFilters = $request->anyFilled(['search', 'from_date', 'to_date', 'status', 'class_room_id']);
        $summary = null;

        if ($hasFilters) {
            $summary = [
                'total' => (clone $query)->count(),
                'present' => (clone $query)->where('student_attendance.is_present', 1)->count(),
                'absent' => (clone $query)->where('student_attendance.is_present', 0)->count(),
            ];
        }

        $data = $query->latest('class_hours.link_updated_at')->paginate(utility('pagination', 50))->withQueryString();

        $selectedClassName = $request->filled('class_room_id')
            ? optional(ClassRoom::find($request->class_room_id))->name
            : null;

        $classRoomSearchUrl = route('admin.class_rooms.search');

        return view('admin.reports.attendance', compact('data', 'hasFilters', 'summary', 'selectedClassName', 'classRoomSearchUrl'));
    }

    public function exportAttendance(Request $request)
    {
        $fileName = 'attendance-report_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(
            new AttendanceExport($request->all()),
            $fileName
        );
    }

    public function teacherSalary(Request $request)
    {
        $tab = $request->get('tab', 'unpaid');

        $query = DB::table('teacher_salaries')
            ->join('teachers', 'teachers.id', '=', 'teacher_salaries.teacher_id')
            ->select(
                'teachers.id as teacher_id',
                'teachers.name',
                'teachers.phone',
                'teacher_salaries.id',
                'teacher_salaries.total_hours',
                'teacher_salaries.total_amount',
                'teacher_salaries.cycle_start',
                'teacher_salaries.cycle_end',
                'teacher_salaries.payment_date',
                'teacher_salaries.credit_date',
                'teacher_salaries.reference_number',
                'teacher_salaries.notes',
                'teacher_salaries.payment_method',
                'teacher_salaries.status'
            );

        /*
        |--------------------------------------------------------------------------
        | Tab Logic
        |--------------------------------------------------------------------------
        */

        if ($tab === 'paid') {
            $query->where('teacher_salaries.status', 'paid');
        } else {
            // Unpaid or Partial
            $query->where('teacher_salaries.status', '<>', 'paid');
        }

        /*
        |--------------------------------------------------------------------------
        | 🔍 Filters
        |--------------------------------------------------------------------------
        */

        // Search teacher

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('teachers.name', 'like', '%' . $request->search . '%')
                    ->orWhere('teachers.phone', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter (Optional override)
        if ($request->filled('status')) {
            $query->where('teacher_salaries.status', $request->status);
        }

        $dateType = $request->get('date_type', 'cycle_date');

        if ($request->filled('from_date') && $request->filled('to_date')) {
            if ($dateType === 'credit_date') {
                $query->whereBetween('teacher_salaries.credit_date', [$request->from_date, $request->to_date]);
            } else {
                $query->where(function ($q) use ($request) {
                    $q->whereBetween('cycle_start', [$request->from_date, $request->to_date])
                        ->orWhereBetween('cycle_end', [$request->from_date, $request->to_date]);
                });
            }
        }

        // Payment date filter (optional)
        if ($request->filled('payment_date')) {
            $query->whereDate('teacher_salaries.payment_date', $request->payment_date);
        }

        // Check if any filter is applied
        $isFiltered = $request->anyFilled(['search', 'status', 'from_date', 'to_date', 'payment_date']);

        $totalAmount = 0;
        if ($isFiltered) {
            $totalAmount = (clone $query)->sum('teacher_salaries.total_amount');
        }

        $data = $query->latest('teacher_salaries.cycle_start')
            ->paginate(utility('pagination', 50))
            ->withQueryString();

        return view('admin.reports.teacher_salary', compact('data', 'tab', 'totalAmount', 'isFiltered'));
    }

    public function exportTeacherSalary(Request $request)
    {
        $fileName = 'teacher-salary-report_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(
            new TeacherSalaryExport($request->all()),
            $fileName
        );
    }

    public function studentLeadReport(Request $request)
    {
        $query = StudentLead::query()->with('notes.staff');

        // Date range filter (same as salary)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Name / Phone filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->name . '%')
                    ->orWhere('whatsapp_number', 'like', '%' . $request->name . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leads = $query->latest()->paginate(utility('pagination', 20))->withQueryString();

        // Summary (like salary totals)
        $totalLeads = $query->count();
        $convertedLeads = $query->clone()->where('status', 'converted')->count();
        $pendingLeads = $query->clone()->where('status', 'pending')->count();

        return view('admin.reports.student_leads', compact(
            'leads',
            'totalLeads',
            'convertedLeads',
            'pendingLeads'
        ));
    }

    public function exportStudentLeads(Request $request)
    {
        $query = StudentLead::query();

        // Date range filter (same as report)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Name / Phone filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->name . '%')
                    ->orWhere('whatsapp_number', 'like', '%' . $request->name . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leads = $query->latest()->get();

        $fileName = 'student_leads_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(
            new StudentLeadExport($leads),
            $fileName
        );
    }

    public function studentReport(Request $request)
    {
        $query = Student::query();

        // Date range filter (same as salary)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Name / Phone filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->name . '%')
                    ->orWhere('whatsapp_number', 'like', '%' . $request->name . '%')
                    ->orWhere('phone', 'like', '%' . $request->name . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Blocked filter
        if ($request->filled('is_blocked')) {
            $query->where('is_blocked', $request->is_blocked);
        }

        $students = $query->latest('id')->paginate(utility('pagination', 20))->withQueryString();

        // Summary
        $totalStudents = $query->count();
        $activeStudents = $query->clone()->where('status', 'active')->count();
        $inactiveStudents = $query->clone()->where(function ($q) {
            $q->where('status', 'passout')->orWhere('status', 'dropout');
        })->count();

        return view('admin.reports.students', compact(
            'students',
            'totalStudents',
            'activeStudents',
            'inactiveStudents'
        ));
    }

    public function exportStudents(Request $request)
    {
        $query = Student::query();

        // Date range filter (same as report)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Name / Phone filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->name . '%')
                    ->orWhere('whatsapp_number', 'like', '%' . $request->name . '%')
                    ->orWhere('phone', 'like', '%' . $request->name . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Blocked filter
        if ($request->filled('is_blocked')) {
            $query->where('is_blocked', $request->is_blocked);
        }

        $students = $query->latest('id')->get();

        $fileName = 'students_report_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(
            new StudentExport($students),
            $fileName
        );
    }

    public function studentAdvances(Request $request)
    {
        $query = Student::query();

        // Name / Phone / Admission No filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->name . '%')
                    ->orWhere('whatsapp_number', 'like', '%' . $request->name . '%')
                    ->orWhere('phone', 'like', '%' . $request->name . '%')
                    ->orWhere('admission_no', 'like', '%' . $request->name . '%');
            });
        }

        // Date range filter
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Wallet Balance filter
        $balanceOption = $request->get('balance_option', 'has_balance');
        if ($balanceOption === 'has_balance') {
            $query->where('wallet_balance', '>', 0);
        } elseif ($balanceOption === 'no_balance') {
            $query->where('wallet_balance', '<=', 0);
        }

        // Calculate totals based on filtered query
        $filteredAdvanceAmount = (clone $query)->sum('wallet_balance');
        $filteredStudentsCount = (clone $query)->count();

        // Overall stats
        $totalSystemAdvance = Student::sum('wallet_balance');
        $studentsWithAdvanceCount = Student::where('wallet_balance', '>', 0)->count();

        // Paginate results
        $students = $query->orderByDesc('wallet_balance')
            ->paginate(utility('pagination', 20))
            ->withQueryString();

        return view('admin.reports.student_advances', compact(
            'students',
            'filteredAdvanceAmount',
            'filteredStudentsCount',
            'totalSystemAdvance',
            'studentsWithAdvanceCount',
            'balanceOption'
        ));
    }

    public function staffReport(Request $request)
    {
        $query = Staff::query();

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $staffs = $query->latest()->paginate(utility('pagination', 20))->withQueryString();

        $totalStaffs = (clone $query)->count();
        $withSalary = (clone $query)->whereNotNull('salary_amount')->count();
        $withoutSalary = (clone $query)->where(function ($q) {
            $q->whereNull('salary_amount')->orWhere('salary_amount', 0);
        })->count();

        return view('admin.reports.staffs', compact(
            'staffs',
            'totalStaffs',
            'withSalary',
            'withoutSalary'
        ));
    }

    public function exportStaffs(Request $request)
    {
        $query = Staff::query();

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $staffs = $query->latest()->get();

        $fileName = 'staffs_report_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(
            new StaffExport($staffs),
            $fileName
        );
    }

    public function staffSalaryReport(Request $request)
    {
        $query = StaffSalary::query()
            ->join('staffs', 'staffs.id', '=', 'staff_salaries.staff_id')
            ->leftJoin('staff_salary_payments', 'staff_salary_payments.staff_salary_id', '=', 'staff_salaries.id')
            ->select(
                'staff_salaries.id',
                'staffs.id as staff_id',
                'staffs.name',
                'staffs.phone',
                'staff_salaries.salary_month',
                'staff_salaries.salary_amount',
                'staff_salaries.status',
                'staff_salaries.paid_date',
                DB::raw('COALESCE(SUM(staff_salary_payments.paid_amount), 0) as paid_amount'),
                DB::raw('staff_salaries.salary_amount - COALESCE(SUM(staff_salary_payments.paid_amount), 0) as balance_due')
            )
            ->groupBy(
                'staff_salaries.id',
                'staffs.id',
                'staffs.name',
                'staffs.phone',
                'staff_salaries.salary_month',
                'staff_salaries.salary_amount',
                'staff_salaries.status',
                'staff_salaries.paid_date'
            );

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('staffs.name', 'like', '%' . $request->search . '%')
                    ->orWhere('staffs.phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('staff_salaries.status', $request->status);
        }

        if ($request->filled('from_month')) {
            $query->where('staff_salaries.salary_month', '>=', $request->from_month);
        }

        if ($request->filled('to_month')) {
            $query->where('staff_salaries.salary_month', '<=', $request->to_month);
        }

        $data = $query->orderByDesc('staff_salaries.salary_month')
            ->paginate(utility('pagination', 50))
            ->withQueryString();

        return view('admin.reports.staff_salary', compact('data'));
    }

    public function exportStaffSalary(Request $request)
    {
        $fileName = 'staff-salary-report_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(
            new StaffSalaryExport($request->all()),
            $fileName
        );
    }

    public function createStudent(Request $request)
    {
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();

        $relativeOfStudent = null;
        if ($request->filled('relative_of')) {
            try {
                $relativeOfStudent = Student::findOrFail(decrypt($request->relative_of));
            } catch (\Exception $e) {
                // Ignore decrypt failure
            }
        }

        return view('staff.students.create', compact('countries', 'relativeOfStudent'));
    }

    public function storeStudent(Request $request)
    {
        $request->merge([
            'contact_number' => preg_replace('/[^0-9]/', '', $request->contact_number),
            'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
        ]);

        $request->merge(['phone' => $request->contact_number]);

        $relativeOfStudent = null;
        if ($request->filled('relative_of')) {
            try {
                $relativeOfStudent = Student::with('relatedStudents')->findOrFail(decrypt($request->relative_of));
            } catch (\Exception $e) {
                return back()->with('error', 'Invalid sibling reference.')->withInput();
            }
        }

        if ($relativeOfStudent) {
            // Force same contact details as main student
            $request->merge([
                'country_id' => $relativeOfStudent->country_id,
                'contact_number' => $relativeOfStudent->contact_number,
                'phone' => $relativeOfStudent->phone,
            ]);

            $request->validate([
                'name' => 'required',
                'password' => 'required|min:6',
                'selected_days' => 'required|array|min:1'
            ]);

            // Enforce password is not the same as any family member
            $familyIds = \DB::table('student_relations')
                ->where('student_id', $relativeOfStudent->id)
                ->orWhere('related_student_id', $relativeOfStudent->id)
                ->get()
                ->flatMap(function ($row) {
                    return [$row->student_id, $row->related_student_id];
                })
                ->unique()
                ->toArray();
            $allFamilyIds = array_unique(array_merge($familyIds, [$relativeOfStudent->id]));

            $familyHashedPasswords = Student::whereIn('id', $allFamilyIds)->pluck('password')->filter();

            foreach ($familyHashedPasswords as $hashedPassword) {
                if (\Illuminate\Support\Facades\Hash::check($request->password, $hashedPassword)) {
                    return back()->withErrors([
                        'password' => 'The password cannot be the same as another related family member. Please choose a different password.'
                    ])->withInput();
                }
            }
        } else {
            // Standard validation (must be unique phone)
            $request->validate([
                'name' => 'required',
                'country_id' => 'required|exists:countries,id',
                'contact_number' => 'required|string|digits_between:7,15',
                'phone' => 'required|unique:students,phone,NULL,id,country_id,' . $request->country_id,
                'email' => 'nullable|email',
                'password' => 'required|min:6',
                'selected_days' => 'required|array|min:1'
            ]);
        }

        try {
            // Enforce consistency
            $classesPerWeek = count($request->selected_days ?? []);

            $photo = null;
            $idProof = null;

            if ($request->hasFile('photo')) {
                $photo = $request->file('photo')
                    ->store('students/photos', 'public');
            }

            if ($request->hasFile('id_proof')) {
                $idProof = $request->file('id_proof')
                    ->store('students/id_proofs', 'public');
            }

            $country = \App\Models\Country::find($request->country_id);
            $isWhatsappDifferent = $request->has('is_whatsapp_different');
            if ($isWhatsappDifferent) {
                $whatsapp_number = $request->whatsapp_number;
            } else {
                $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
                $whatsapp_number = $countryCode . $request->contact_number;
            }

            $admissionNo = generateAdmissionNo();

            $newStudent = Student::create([
                'admission_no' => $admissionNo,
                'student_lead_id' => null,
                'country_id' => $request->country_id,
                'is_whatsapp_different' => $isWhatsappDifferent,
                'name' => $request->name,
                'dob' => $request->dob,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'whatsapp_number' => $whatsapp_number,
                'parent_name' => $request->parent_name,
                'address' => $request->address,
                'phone' => $request->phone,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'photo' => $photo,
                'id_proof' => $idProof,
                'classes_per_week' => $classesPerWeek,
                'selected_days' => $request->selected_days ?? [],
                'time_slot' => $request->time_slot,
                'starting_date' => $request->starting_date,
                'status' => $request->status ?? 'active'
            ]);

            if ($relativeOfStudent) {
                // Form clique of all family members
                $familyIds = $relativeOfStudent->relatedStudents()->pluck('students.id')->toArray();
                $allFamilyIds = array_unique(array_merge($familyIds, [$relativeOfStudent->id, $newStudent->id]));

                foreach ($allFamilyIds as $id) {
                    $member = Student::find($id);
                    if ($member) {
                        $otherIds = array_diff($allFamilyIds, [$id]);
                        $member->relatedStudents()->sync($otherIds);
                    }
                }

                return redirect()
                    ->route('admin.reports.students.show', encrypt($relativeOfStudent->id))
                    ->with('success', 'Sibling account registered and linked successfully.');
            }

            return redirect()
                ->route('admin.reports.students')
                ->with('success', 'Student created successfully.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Student Creation Failed: " . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Error creating student: ' . $e->getMessage());
        }
    }

    public function showStudent($id)
    {
        $student = Student::with([
            'class_rooms.course',
            'class_rooms.classType',
            'fees',
            'attendances'
        ])->findOrFail(decrypt($id));

        $teachers = Teacher::whereHas('classRooms', function ($q) use ($student) {
            $q->whereIn('class_rooms.id', $student->class_rooms->pluck('id'));
        })->get();

        $attendance = [
            'total' => $student->attendances()->count(),
            'present' => $student->attendances()->where('is_present', 1)->count(),
            'absent' => $student->attendances()->where('is_present', 0)->count(),
        ];

        $notes = ClassNote::whereIn(
            'class_room_id',
            $student->class_rooms->pluck('id')
        )->latest()->get();

        return view('admin.reports.show_student', compact('student', 'teachers', 'attendance', 'notes'));
    }

    public function toggleBlockStudent($id)
    {
        $student = Student::findOrFail(decrypt($id));
        $student->update([
            'is_blocked' => !$student->is_blocked
        ]);

        $statusStr = $student->is_blocked ? 'blocked' : 'unblocked';
        return back()->with('success', "Student \"{$student->name}\" {$statusStr} successfully.");
    }

    public function teacherLeadReport(Request $request)
    {
        $query = TeacherLead::with(['source', 'notes.staff']);
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Name / Phone filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->name . '%')
                    ->orWhere('whatsapp_number', 'like', '%' . $request->name . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leads = $query->latest()->paginate(utility('pagination', 20))->withQueryString();

        // Summary
        $totalLeads = $query->count();
        $convertedLeads = $query->clone()->where('status', 'converted')->count();
        $pendingLeads = $query->clone()->where('status', 'pending')->count();

        return view('admin.reports.teacher_leads', compact(
            'leads',
            'totalLeads',
            'convertedLeads',
            'pendingLeads'
        ));
    }

    public function exportTeacherLeads(Request $request)
    {
        $query = TeacherLead::with('source');

        // Date range filter (same as report)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Name / Phone filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->name . '%')
                    ->orWhere('whatsapp_number', 'like', '%' . $request->name . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leads = $query->latest()->get();

        $fileName = 'teacher_leads_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(
            new TeacherLeadExport($leads),
            $fileName
        );
    }

    public function teacherReport(Request $request)
    {
        $query = Teacher::query();

        // Date range filter (same as salary)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Name / Phone filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->name . '%')
                    ->orWhere('whatsapp_number', 'like', '%' . $request->name . '%')
                    ->orWhere('phone', 'like', '%' . $request->name . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $teachers = $query->latest('id')->paginate(utility('pagination', 20))->withQueryString();

        // Summary
        $totalTeachers = $query->count();
        $activeTeachers = $query->clone()->where('status', 'active')->count();
        $inactiveTeachers = $query->clone()->where('status', '!=', 'active')->count();

        return view('admin.reports.teachers', compact(
            'teachers',
            'totalTeachers',
            'activeTeachers',
            'inactiveTeachers'
        ));
    }

    public function exportTeachers(Request $request)
    {
        $query = Teacher::query();

        // Date range filter (same as report)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Name / Phone filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->name . '%')
                    ->orWhere('whatsapp_number', 'like', '%' . $request->name . '%')
                    ->orWhere('phone', 'like', '%' . $request->name . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $teachers = $query->latest('id')->get();

        $fileName = 'teachers_report_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(
            new TeacherExport($teachers),
            $fileName
        );
    }

    public function showTeacher($id)
    {
        $teacher = Teacher::with('classRooms.course')->findOrFail(decrypt($id));

        $notes = ClassNote::whereIn(
            'class_room_id',
            $teacher->classRooms->pluck('id')
        )->latest()->get();

        return view('admin.reports.show_teacher', compact('teacher', 'notes'));

    }

    public function studentLeadNotes(Request $request)
    {
        $query = LeadNote::with(['lead', 'staff']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('lead', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('contact_number', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $data = $query->latest()->paginate(utility('pagination', 50))->withQueryString();

        return view('admin.reports.student_lead_notes', compact('data'));
    }

    public function teacherLeadNotes(Request $request)
    {
        $query = TeacherLeadNote::with(['lead', 'staff']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('lead', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('contact_number', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $data = $query->latest()->paginate(utility('pagination', 50))->withQueryString();

        return view('admin.reports.teacher_lead_notes', compact('data'));
    }

    public function searchTeachers(Request $request)
    {
        $term = $request->input('q', '');
        $results = Teacher::where('name', 'like', "%{$term}%")
            ->orWhere('contact_number', 'like', "%{$term}%")
            ->limit(30)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'text' => $t->name,
            ]);

        return response()->json(['results' => $results]);
    }

    public function classHours(Request $request)
    {
        $query = ClassHour::with(['classRoom.course', 'teacher']);

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->class_room_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('link_updated_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('link_updated_at', '<=', $request->to_date);
        }

        $totalClassHours = $query->count();
        $totalDurationMins = (int) $query->sum('duration');

        $totalDurationHours = floor($totalDurationMins / 60);
        $remainingMins = $totalDurationMins % 60;
        $totalDurationFormatted = "{$totalDurationHours}h {$remainingMins}m";

        $data = $query->latest('link_updated_at')->paginate(utility('pagination', 20))->withQueryString();

        $selectedClassName = $request->filled('class_room_id')
            ? optional(\App\Models\ClassRoom::find($request->class_room_id))->name
            : null;

        $selectedTeacherName = $request->filled('teacher_id')
            ? optional(\App\Models\Teacher::find($request->teacher_id))->name
            : null;

        return view('admin.reports.class_hours', compact(
            'data',
            'selectedClassName',
            'selectedTeacherName',
            'totalClassHours',
            'totalDurationFormatted'
        ));
    }

    public function toggleWalletAutopay(Request $request, $id)
    {
        $student = Student::findOrFail(decrypt($id));
        $student->update([
            'is_wallet_autopay_enabled' => !$student->is_wallet_autopay_enabled
        ]);
        return back()->with('success', 'Wallet autopay setting updated successfully.');
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

                    // Fetch unpaid or partial fees sorted by due date ascending
                    $fees = Fee::where('student_id', $student->id)
                        ->whereIn('status', ['unpaid', 'partial'])
                        ->orderBy('due_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                    foreach ($fees as $fee) {
                        if ($student->wallet_balance <= 0) {
                            break;
                        }

                        $totalPaid = \App\Models\FeePayment::where('fee_id', $fee->id)->sum('paid_amount');
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
                        \App\Models\FeePayment::create([
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

    public function refundFee(Request $request)
    {
        $validated = $request->validate([
            'fee_id' => 'required|exists:fees,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,upi,bank_transfer',
            'notes' => 'nullable|string',
            'refund_date' => 'required|date'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $fee = Fee::findOrFail($validated['fee_id']);

                // Calculate total paid so far
                $totalPaid = $fee->payments()->sum('paid_amount');
                // Calculate total refunded so far
                $totalRefunded = \App\Models\FeeRefund::where('fee_id', $fee->id)->sum('amount');

                $maxRefundable = $totalPaid - $totalRefunded;

                if ($validated['amount'] > $maxRefundable) {
                    throw new \Exception('Refund amount of ₹' . number_format($validated['amount'], 2) . ' exceeds the max refundable amount of ₹' . number_format($maxRefundable, 2));
                }

                $lastPaymentDate = $fee->payments()->max('paid_date');
                if (!$lastPaymentDate) {
                    throw new \Exception('Cannot refund a fee with no recorded payments.');
                }
                if (\Carbon\Carbon::parse($lastPaymentDate)->addMonths(2)->isPast()) {
                    throw new \Exception('Refund is only allowed within 2 months of the last payment date (Last payment: ' . \Carbon\Carbon::parse($lastPaymentDate)->format('d M Y') . ').');
                }

                // Create refund record
                \App\Models\FeeRefund::create([
                    'fee_id' => $fee->id,
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'refund_date' => $validated['refund_date'],
                    'notes' => $validated['notes']
                ]);

                // Calculate net paid amount after this refund
                $netPaid = $totalPaid - ($totalRefunded + $validated['amount']);

                // Update fee status based on net paid
                if ($netPaid <= 0) {
                    $fee->update(['status' => 'unpaid']);
                } elseif ($netPaid < $fee->amount) {
                    $fee->update(['status' => 'partial']);
                } else {
                    $fee->update(['status' => 'paid']);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Refund recorded successfully.');
    }

    public function getRefunds($id)
    {
        $fee = Fee::with('refunds')->findOrFail($id);
        return response()->json([
            'refunds' => $fee->refunds
        ]);
    }

    public function getPayments($id)
    {
        $fee = Fee::with('payments')->findOrFail($id);
        return response()->json([
            'payments' => $fee->payments
        ]);
    }

    public function assignClass(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_room_id' => 'required|exists:class_rooms,id'
        ]);

        $student = Student::findOrFail($request->student_id);
        $class = ClassRoom::with('classType', 'students')->findOrFail($request->class_room_id);

        if ($class->classType?->name === 'individual') {
            if ($class->students()->count() > 0) {
                return back()->with('error', 'Only one student allowed for individual class.');
            }
        }

        if ($student->class_rooms()->where('class_room_id', $class->id)->exists()) {
            return back()->with('error', 'Student already assigned to this class.');
        }

        $student->class_rooms()->attach($class->id, [
            'assigned_date' => now()
        ]);

        $isAdmissionExempted = $student->is_admission_fee_exempted;

        if ($isAdmissionExempted) {
            return back()->with('success', 'Class assigned (Admission fee exempted)');
        }

        $type = 'admission';
        $amount = max(0, $class->admission_fee - $student->admission_fee_discount);

        if (
            Fee::where('student_id', $student->id)
                ->where('class_room_id', $class->id)
                ->where('type', $type)
                ->exists()
        ) {
            return back()->with('warning', 'Class assigned, fee already exists.');
        }

        if ($amount > 0) {
            $fee = Fee::create([
                'student_id' => $student->id,
                'class_room_id' => $class->id,
                'type' => $type,
                'amount' => $amount,
                'due_date' => now()->addDays(7),
                'status' => 'unpaid'
            ]);

            app(\App\Services\FeeService::class)->applyWalletBalance($fee);
        }

        return back()->with('success', 'Class assigned and fee generated successfully.');
    }

    public function changeClass(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'from_class_id' => 'required|exists:class_rooms,id',
            'to_class_id' => 'required|exists:class_rooms,id|different:from_class_id'
        ]);

        $student = Student::findOrFail($request->student_id);

        if (!$student->class_rooms()->where('class_room_id', $request->from_class_id)->exists()) {
            return back()->with('error', 'Student is not assigned to the selected "From Class".');
        }

        if ($student->class_rooms()->where('class_room_id', $request->to_class_id)->exists()) {
            return back()->with('error', 'Student is already assigned to the selected "To Class".');
        }

        DB::transaction(function () use ($student, $request) {
            DB::table('class_change_logs')->insert([
                'student_id' => $student->id,
                'class_room_id_from' => $request->from_class_id,
                'class_room_id_to' => $request->to_class_id,
                'type' => 'change',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Fee::where('student_id', $student->id)
                ->where('class_room_id', $request->from_class_id)
                ->where('status', 'unpaid')
                ->update(['class_room_id' => $request->to_class_id]);

            $student->class_rooms()->detach($request->from_class_id);
            $student->class_rooms()->attach($request->to_class_id, [
                'assigned_date' => now()
            ]);
        });

        return back()->with('success', 'Class changed successfully. Unpaid fees have been transferred.');
    }

    public function promoteClass(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'from_class_id' => 'required|exists:class_rooms,id',
            'to_class_id' => 'required|exists:class_rooms,id|different:from_class_id'
        ]);

        $student = Student::findOrFail($request->student_id);

        if (!$student->class_rooms()->where('class_room_id', $request->from_class_id)->exists()) {
            return back()->with('error', 'Student is not assigned to the selected "From Class".');
        }

        if ($student->class_rooms()->where('class_room_id', $request->to_class_id)->exists()) {
            return back()->with('error', 'Student is already assigned to the selected "To Class".');
        }

        $hasUnpaidFees = Fee::where('student_id', $student->id)
            ->where('class_room_id', $request->from_class_id)
            ->where('status', '!=', 'paid')
            ->exists();

        if ($hasUnpaidFees) {
            return back()->with('error', 'Cannot promote student. There are unpaid fees in the current class.');
        }

        DB::transaction(function () use ($student, $request) {
            DB::table('class_change_logs')->insert([
                'student_id' => $student->id,
                'class_room_id_from' => $request->from_class_id,
                'class_room_id_to' => $request->to_class_id,
                'type' => 'promote',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $student->class_rooms()->attach($request->to_class_id, [
                'assigned_date' => now()
            ]);

            ClassRoom::where('id', $request->from_class_id)->update(['is_completed' => true]);
        });

        return back()->with('success', 'Student promoted to new class successfully.');
    }

    public function searchActiveClasses(Request $request)
    {
        $term = $request->input('q', '');
        $query = ClassRoom::with('course')
            ->where('is_completed', false);

        if ($request->filled('type')) {
            $types = explode(',', $request->type);
            $query->whereHas('classType', function ($q) use ($types) {
                $q->whereIn('name', $types);
            });
        }

        if ($request->filled('exclude_student_id')) {
            $query->whereDoesntHave('students', function ($q) use ($request) {
                $q->where('students.id', $request->exclude_student_id);
            });
        }

        $results = $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhereHas('course', fn($c) => $c->where('name', 'like', "%{$term}%"));
        })
            ->limit(30)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'text' => $c->name . ($c->course ? ' (' . $c->course->name . ')' : ''),
            ]);

        return response()->json(['results' => $results]);
    }

    public function removeRelation(Request $request, $id, $related_id)
    {
        $student = Student::findOrFail(decrypt($id));
        $relatedStudent = Student::findOrFail(decrypt($related_id));

        $request->validate([
            'new_contact_number' => 'required|string|digits_between:7,15',
        ]);

        $newNumber = preg_replace('/[^0-9]/', '', $request->new_contact_number);

        // Check unique constraint for the new number
        $exists = Student::where('phone', $newNumber)
            ->where('country_id', $relatedStudent->country_id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'new_contact_number' => 'The contact number is already registered under this country.'
            ])->withInput();
        }

        // Update details and password
        $country = $relatedStudent->country;
        $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
        $whatsapp_number = $relatedStudent->is_whatsapp_different
            ? $relatedStudent->whatsapp_number
            : ($countryCode . $newNumber);

        $relatedStudent->update([
            'contact_number' => $newNumber,
            'phone' => $newNumber,
            'whatsapp_number' => $whatsapp_number,
            'password' => \Illuminate\Support\Facades\Hash::make($newNumber),
        ]);

        $relatedStudent->relatedStudents()->detach();

        \DB::table('student_relations')
            ->where('related_student_id', $relatedStudent->id)
            ->delete();

        return redirect()->route('admin.reports.students.show', encrypt($student->id))
            ->with('success', 'Sibling account unlinked, contact details updated, and password reset successfully.');
    }

    public function searchStudentsForRelations(Request $request, $id)
    {
        $student = Student::findOrFail(decrypt($id));
        $term = $request->input('q', '');

        $relatedIds = \DB::table('student_relations')
            ->where('student_id', $student->id)
            ->orWhere('related_student_id', $student->id)
            ->get()
            ->flatMap(function ($row) {
                return [$row->student_id, $row->related_student_id];
            })
            ->unique()
            ->toArray();

        $excludeIds = array_merge([$student->id], $relatedIds);

        $results = Student::whereNotIn('id', $excludeIds)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('contact_number', 'like', "%{$term}%")
                    ->orWhere('admission_no', 'like', "%{$term}%");
            })
            ->limit(20)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'text' => $s->name . ' (' . $s->admission_no . ') - ' . $s->phone,
            ]);

        return response()->json(['results' => $results]);
    }

    public function addRelation(Request $request, $id)
    {
        $studentA = Student::findOrFail(decrypt($id));
        $relatedStudentId = $request->input('related_student_id');

        if (!$relatedStudentId) {
            return back()->with('error', 'Please select a student to link.');
        }

        $studentB = Student::findOrFail($relatedStudentId);

        if ($studentA->id == $studentB->id) {
            return back()->with('error', 'Cannot link a student to themselves.');
        }

        // Get A's family clique
        $familyIdsA = \DB::table('student_relations')
            ->where('student_id', $studentA->id)
            ->orWhere('related_student_id', $studentA->id)
            ->get()
            ->flatMap(fn($row) => [$row->student_id, $row->related_student_id])
            ->unique()
            ->toArray();
        $allFamilyIdsA = array_unique(array_merge($familyIdsA, [$studentA->id]));

        // Get B's family clique
        $familyIdsB = \DB::table('student_relations')
            ->where('student_id', $studentB->id)
            ->orWhere('related_student_id', $studentB->id)
            ->get()
            ->flatMap(fn($row) => [$row->student_id, $row->related_student_id])
            ->unique()
            ->toArray();
        $allFamilyIdsB = array_unique(array_merge($familyIdsB, [$studentB->id]));

        // Check if there are any intersecting IDs
        if (count(array_intersect($allFamilyIdsA, $allFamilyIdsB)) > 0) {
            return back()->with('error', 'These students are already linked.');
        }

        // Update all members of B's clique to use A's contact details
        foreach ($allFamilyIdsB as $mId) {
            $member = Student::find($mId);
            if ($member) {
                $member->update([
                    'country_id' => $studentA->country_id,
                    'contact_number' => $studentA->contact_number,
                    'phone' => $studentA->phone,
                    'whatsapp_number' => $studentA->whatsapp_number,
                    'is_whatsapp_different' => $studentA->is_whatsapp_different,
                ]);
            }
        }

        // Merge cliques
        $combinedFamilyIds = array_unique(array_merge($allFamilyIdsA, $allFamilyIdsB));

        // Sync relationships bidirectionally for all members of the combined clique
        foreach ($combinedFamilyIds as $mId) {
            $member = Student::find($mId);
            if ($member) {
                $otherIds = array_diff($combinedFamilyIds, [$mId]);
                $member->relatedStudents()->sync($otherIds);
            }
        }

        return back()->with('success', 'Student linked as family member successfully.');
    }
}
