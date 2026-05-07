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

        $query = Fee::with(['student', 'classRoom']);
        // ->withSum('payments as paid_amount', 'paid_amount');

        // Tab logic
        if ($tab === 'paid') {
            $query->where('status', 'paid');
        } elseif ($tab === 'overdue') {
            // Overdue: More than 4 days past due date AND not paid
            $fourDaysAgo = now()->subDays(4)->endOfDay();
            $query->where('status', '<>', 'paid')
                ->whereDate('due_date', '<', $fourDaysAgo);
        } else {
            // Unpaid: Not paid AND within 4 days of due date
            $fourDaysAgo = now()->subDays(4)->endOfDay();
            $query->where('status', '<>', 'paid')
                ->whereDate('due_date', '>=', $fourDaysAgo);
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('due_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('due_date', '<=', $request->to_date);
        }

        // Check if any filter is applied
        $isFiltered = $request->anyFilled(['search', 'class_room_id', 'type', 'status', 'from_date', 'to_date']);

        $totalAmount = 0;
        if ($isFiltered) {
            $totalAmount = (clone $query)->sum('amount');
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

        $fees = $query->paginate(10)->withQueryString();

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

    public function feeCollection(Request $request)
    {
        $query = DB::table('fee_payments')
            ->join('fees', 'fees.id', '=', 'fee_payments.fee_id')
            ->join('students', 'students.id', '=', 'fees.student_id')
            ->join('class_rooms', 'class_rooms.id', '=', 'fees.class_room_id')
            ->join('courses', 'courses.id', '=', 'class_rooms.course_id')
            ->join('course_categories', 'course_categories.id', '=', 'courses.category_id')

            ->select(
                'students.name',
                'students.contact_number',
                'students.whatsapp_number',
                'students.is_whatsapp_different',
                'class_rooms.name as class_name',
                'course_categories.name as category_name',
                'fee_payments.paid_amount',
                'fee_payments.payment_method',
                'fee_payments.paid_date'
            );

        // Filters
        if ($request->filled('search')) {
            $query->where('students.name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('payment_method')) {
            $query->where('fee_payments.payment_method', $request->payment_method);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('fee_payments.paid_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('fee_payments.paid_date', '<=', $request->to_date);
        }

        if ($request->filled('category_id')) {
            $query->where('course_categories.id', $request->category_id);
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_rooms.id', $request->class_room_id);
        }

        // Check if any filter is applied
        $isFiltered = $request->anyFilled(['search', 'category_id', 'class_room_id', 'payment_method', 'from_date', 'to_date']);

        $totalAmount = 0;
        if ($isFiltered) {
            $totalAmount = (clone $query)->sum('fee_payments.paid_amount');
        }

        $data = $query->latest('fee_payments.paid_date')->paginate(10)->withQueryString();

        // Get course categories for dropdown
        $categories = \App\Models\CourseCategory::orderBy('name', 'asc')->pluck('name', 'id');

        $selectedClassName = $request->filled('class_room_id')
            ? optional(\App\Models\ClassRoom::find($request->class_room_id))->name
            : null;

        return view('admin.reports.fee_collection', compact('data', 'categories', 'selectedClassName', 'totalAmount', 'isFiltered'));
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

        $totalAmount = (clone $baseQuery)->sum('amount');

        $sourceTotals = (clone $baseQuery)
            ->select('source', DB::raw('SUM(amount) as total'))
            ->groupBy('source')
            ->pluck('total', 'source');

        $data = (clone $baseQuery)
            ->orderByDesc('transaction_date')
            ->paginate(10)
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
            ->join('class_hours', 'class_hours.id', '=', 'student_attendance.class_hour_id')
            ->join('class_rooms', 'class_rooms.id', '=', 'class_hours.class_room_id')

            ->select(
                'students.name',
                'students.contact_number',
                'students.whatsapp_number',
                'students.is_whatsapp_different',
                'class_rooms.name as class_name',
                'class_hours.updated_at',
                'student_attendance.is_present'
            );

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('students.name', 'like', "%$search%")
                    ->orWhere('students.contact_number', 'like', "%$search%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('class_hours.updated_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('class_hours.updated_at', '<=', $request->to_date);
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

        $data = $query->latest('class_hours.updated_at')->paginate(10)->withQueryString();

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

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('cycle_start', [$request->from_date, $request->to_date])
                    ->orWhereBetween('cycle_end', [$request->from_date, $request->to_date]);
            });
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
            ->paginate(10)
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
        $query = StudentLead::query();

        // Date range filter (same as salary)
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

        $leads = $query->latest()->paginate(20);

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

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $students = $query->latest()->paginate(20);

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

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $students = $query->latest()->get();

        $fileName = 'students_report_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(
            new StudentExport($students),
            $fileName
        );
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

        $staffs = $query->latest()->paginate(20)->withQueryString();

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
            ->paginate(10)
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

    public function teacherLeadReport(Request $request)
    {
        $query = TeacherLead::with('source');
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

        $leads = $query->latest()->paginate(20);

        // Summary
        $totalLeads = $query->count();
        $convertedLeads = $query->clone()->where('status', 'approved')->count();
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

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $teachers = $query->latest()->paginate(20);

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

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $teachers = $query->latest()->get();

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

        $data = $query->latest()->paginate(15)->withQueryString();

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

        $data = $query->latest()->paginate(15)->withQueryString();

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
            $query->whereDate('updated_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('updated_at', '<=', $request->to_date);
        }

        $totalClassHours = $query->count();
        $totalDurationMins = (int) $query->sum('duration');

        $totalDurationHours = floor($totalDurationMins / 60);
        $remainingMins = $totalDurationMins % 60;
        $totalDurationFormatted = "{$totalDurationHours}h {$remainingMins}m";

        $data = $query->latest('updated_at')->paginate(20)->withQueryString();

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
}
