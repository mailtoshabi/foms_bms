@section('title', 'Fees')

@php
    $studentIds = $fees->pluck('student_id')->unique()->filter();
    $classRoomIds = $fees->pluck('class_room_id')->unique()->filter();

    if ($studentIds->isNotEmpty() && $classRoomIds->isNotEmpty()) {
        // Fetch all monthly fees for these students and classrooms to locate previous fees in memory
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

        // Fetch all completed class hours and their attendance for these students & classrooms
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
            ->groupBy(function ($item) {
                return $item->student_id . '-' . $item->class_room_id;
            });

        // Map through each fee in the current pagination and attach attendance calculations
        foreach ($fees as $fee) {
            if ($fee->type !== 'monthly') {
                continue;
            }

            $key = $fee->student_id . '-' . $fee->class_room_id;

            // Find the previous monthly fee for this student and classroom
            $previousFee = null;
            $studentClassFees = isset($allMonthlyFees[$key]) ? $allMonthlyFees[$key] : null;
            if ($studentClassFees) {
                $index = $studentClassFees->search(function ($item) use ($fee) {
                    return (int)$item->id === (int)$fee->id;
                });
                if ($index !== false && $index > 0) {
                    $previousFee = $studentClassFees->get($index - 1);
                }
            }

            // Filter the attendances based on the interval
            $total = 0;
            $present = 0;

            $studentClassAttendances = isset($allAttendances[$key]) ? $allAttendances[$key] : null;
            if ($studentClassAttendances) {
                $feeCreatedAt = \Carbon\Carbon::parse($fee->created_at);
                $prevFeeCreatedAt = $previousFee ? \Carbon\Carbon::parse($previousFee->created_at) : null;

                foreach ($studentClassAttendances as $att) {
                    $completedAt = \Carbon\Carbon::parse($att->completed_at);

                    if ($prevFeeCreatedAt) {
                        // Classes completed after the previous fee up to the current fee
                        $inInterval = $completedAt->gt($prevFeeCreatedAt) && $completedAt->lte($feeCreatedAt);
                    } else {
                        // If no previous fee exists, all classes up to the current fee
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
@endphp

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">

        <div class="col-12">

            <div class="card mb-3">
                <div class="card-body p-2">

                    <ul class="nav nav-pills">

                        <li class="nav-item">
                            <a class="nav-link {{ $tab == 'unpaid' ? 'active' : '' }}" href="{{ $routeTemplateUnPaid }}">
                                Pending
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ $tab == 'overdue' ? 'active' : '' }}" href="{{ $routeTemplateOverdue }}">
                                Over Due
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ $tab == 'paid' ? 'active' : '' }}" href="{{ $routeTemplatePaid }}">
                                Full Paid
                            </a>
                        </li>

                    </ul>

                </div>
            </div>
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h4 class="mb-0">
                        <a href="javascript:window.history.back();"
                            class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        @if($tab == 'paid')
                            Fee Paid
                        @elseif($tab == 'overdue')
                            Over Due Fee
                        @else
                            Fee Pending
                        @endif
                    </h4>

                </div>

                <div class="card-body table-responsive">

                    {{-- FILTER --}}

                    <form method="GET" action="{{ $filterRoute }}" class="mb-4">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Search Student</label>
                                <input type="text" name="search" class="form-control" placeholder="Search student name..."
                                    value="{{ request('search') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Class Room</label>
                                <select name="class_room_id" class="form-control select2-class-ajax"
                                    data-ajax-url="{{ $classRoomSearchUrl }}"
                                    data-selected-id="{{ request('class_room_id') }}"
                                    data-selected-text="{{ $selectedClassName ?? '' }}">
                                    @if(request('class_room_id') && isset($selectedClassName))
                                        <option value="{{ request('class_room_id') }}" selected>{{ $selectedClassName }}
                                        </option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Fee Type</label>
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="admission" {{ request('type') == 'admission' ? 'selected' : '' }}>
                                        Admission
                                    </option>
                                    <option value="monthly" {{ request('type') == 'monthly' ? 'selected' : '' }}>
                                        Monthly
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Payment Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>
                                        Unpaid
                                    </option>
                                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>
                                        Partial
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Sort By</label>
                                <select name="sort" class="form-control">
                                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>
                                        Latest
                                    </option>
                                    <option value="due_date" {{ request('sort') == 'due_date' ? 'selected' : '' }}>
                                        Due Date
                                    </option>
                                    <option value="amount" {{ request('sort') == 'amount' ? 'selected' : '' }}>
                                        Amount
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">{{ $tab === 'paid' ? 'From Payment Date' : 'From Due Date' }}</label>
                                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">{{ $tab === 'paid' ? 'To Payment Date' : 'To Due Date' }}</label>
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                            </div>

                            <div class="col-md-6 d-flex align-items-end justify-content-end gap-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-filter"></i> Filter
                                </button>
                                <a href="{{ $filterRoute }}" class="btn btn-light px-4">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </a>
                                @if($isExport == 'true')
                                    <a href="{{ route('admin.reports.fee.export', request()->query()) }}"
                                        class="btn btn-success px-4">
                                        <i class="mdi mdi-file-excel"></i> Export
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">

                    @if(isset($isFiltered) && $isFiltered)
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card bg-soft-info border-info">
                                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                                        <div>
                                            <h5 class="text-info mb-1"><i class="mdi mdi-information-outline me-1"></i>
                                                Filtering Summary</h5>
                                            <p class="text-muted mb-0 small">Showing total results based on your selected
                                                criteria.</p>
                                        </div>
                                        <div class="text-end">
                                            <p class="text-muted mb-1 small uppercase fw-bold">Grand Total Fees</p>
                                            <h3 class="text-primary mb-0 fw-bold">₹ {{ number_format($totalAmount ?? 0, 2) }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif


                    {{-- TABLE --}}
                    <table class="table table-bordered align-middle">

                        <thead>

                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Attendance</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                @if($isAction == 'true' || auth('admin')->check())
                                    <th>Actions</th>
                                @endif
                            </tr>

                        </thead>

                        <tbody>

                            @forelse($fees as $fee)

                                @php
                                    $paid = $fee->paid_amount ?? 0;
                                    $totalRefunded = $fee->refunds->sum('amount');
                                    $netPaid = $paid - $totalRefunded;
                                    $remaining = $fee->amount - $netPaid;

                                    $dueDate = \Carbon\Carbon::parse($fee->due_date);

                                    // Only calculate overdue if still pending
                                    $daysOverdue = ($remaining > 0 && $dueDate->isPast())
                                        ? $dueDate->startOfDay()->diffInDays(now()->startOfDay())
                                        : 0;

                                    $lastPaymentDate = $fee->payments()->max('paid_date');
                                @endphp

                                <tr class="{{ $fee->rowStyle['class'] }}" style="{{ $fee->rowStyle['style'] }}">

                                    <td>
                                        <a href="{{ auth('admin')->check() ? route('admin.reports.students.show', encrypt($fee->student->id)) : route('staff.students.show', encrypt($fee->student->id)) }}">
                                            {{ $fee->student->name ?? 'N/A' }}
                                        </a>
                                    </td>

                                    <td>
                                        <a href="{{ auth('admin')->check() ? route('admin.class_rooms.show', encrypt($fee->classRoom->id)) : route('staff.class_rooms.show', encrypt($fee->classRoom->id)) }}">
                                            {{ $fee->classRoom->name ?? 'N/A' }}
                                            <br><small>Type: {{ ucfirst($fee->classRoom->classType->name ?? 'N/A') }}</small>
                                        </a>
                                    </td>

                                    <td>
                                    @if($fee->type == 'monthly' && isset($fee->has_attendance) && $fee->has_attendance)
                                        <span class="badge {{ $fee->attendance_percent >= 85 ? 'bg-success' : ($fee->attendance_percent >= 75 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ $fee->attendance_present }}/{{ $fee->attendance_total }} ({{ $fee->attendance_percent }}%)
                                        </span>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                    </td>

                                    <td>
                                        <span class="badge bg-info">
                                            {{ ucfirst($fee->type) }}
                                        </span>
                                    </td>

                                    <td>
                                        <strong>₹ {{ number_format($fee->amount, 2) }}</strong><br>

                                        <small class="text-success">
                                            Paid: ₹ {{ number_format($paid, 2) }}
                                        </small><br>

                                        @if($totalRefunded > 0)
                                            <small class="text-warning">
                                                Refunded: ₹ {{ number_format($totalRefunded, 2) }}
                                            </small><br>
                                        @endif

                                        <small class="text-danger">
                                            Remaining: ₹ {{ number_format($remaining, 2) }}
                                        </small>
                                        @php
                                            $percentage = $fee->amount > 0 ? (max(0, $netPaid) / $fee->amount) * 100 : 0;
                                        @endphp

                                        <div class="progress mt-1" style="height:6px;" title="{{ round($percentage) }}% net paid">
                                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%">
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($fee->due_date)->format('d M Y') }}
                                        @if($lastPaymentDate)
                                            <br><small class="text-muted" title="Last Payment Date">Last paid:
                                                {{ \Carbon\Carbon::parse($lastPaymentDate)->format('d M Y') }}</small>
                                        @endif
                                        @if($daysOverdue > 0)
                                            <br><small class="badge bg-danger">{{ $daysOverdue }} days overdue</small>
                                        @endif
                                    </td>

                                    <td>
                                        @php
                                            $badgeClasses = [
                                                'paid' => 'bg-success',
                                                'unpaid' => 'bg-danger',
                                                'partial' => 'bg-warning text-dark',
                                            ];
                                        @endphp
                                        <span
                                            class="badge {{ $badgeClasses[$fee->status] ?? 'bg-danger' }}">{{ ucfirst($fee->status) }}</span>
                                        @if($totalRefunded > 0)
                                            <br>
                                            <a href="javascript:void(0)" class="badge bg-secondary viewRefundsBtn mt-1" 
                                               data-url="{{ auth('admin')->check() ? route('admin.fees.refunds', $fee->id) : route('staff.fees.refunds', $fee->id) }}"
                                               title="View refund history">
                                                <i class="fas fa-undo-alt me-1"></i> Refunded
                                            </a>
                                        @endif
                                    </td>
                                    @if($isAction == 'true' || auth('admin')->check())
                                        <td>

                                            @if($isAction == 'true')
                                                @if($tab !== 'paid')
                                                    <button class="btn btn-sm btn-success markPaidBtn" data-id="{{ $fee->id }}"
                                                        data-amount="{{ $fee->amount }}" data-remaining="{{ $remaining }}"
                                                        data-wallet="{{ $fee->student->wallet_balance ?? 0 }}"
                                                        {{ $remaining <= 0 ? 'disabled' : '' }}>
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif

                                                <button class="btn btn-sm btn-info viewPaymentsBtn"
                                                    data-url="{{ route('staff.fees.payments', $fee->id) }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                @if($tab === 'paid')
                                                    <a href="{{ route('staff.fees.invoice.download', $fee->id) }}"
                                                        class="btn btn-sm btn-danger" title="Download Invoice PDF" target="_blank">
                                                        <i class="mdi mdi-file-pdf-box"></i>
                                                    </a>
                                                @endif

                                                <!-- @if($tab !== 'paid')
                                                    <button class="btn btn-sm btn-warning sendNotificationBtn" data-id="{{ $fee->id }}"
                                                        title="Send notification">
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                @endif -->
                                            @endif

                                            @if(auth('admin')->check() && $netPaid > 0 && $lastPaymentDate && !\Carbon\Carbon::parse($lastPaymentDate)->addMonths(2)->isPast())
                                                <button class="btn btn-sm btn-warning refundFeeBtn" 
                                                    data-id="{{ $fee->id }}"
                                                    data-student="{{ $fee->student->name ?? 'N/A' }}"
                                                    data-amount="{{ $fee->amount }}"
                                                    data-netpaid="{{ $netPaid }}"
                                                    title="Refund Fee">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            @endif

                                            @if(auth('admin')->check() && $fee->status === 'unpaid')
                                                <button class="btn btn-sm btn-danger deleteFeeBtnAdmin" data-id="{{ $fee->id }}"
                                                    data-student="{{ $fee->student->name ?? 'N/A' }}"
                                                    data-amount="{{ number_format($fee->amount, 2) }}"
                                                    title="Delete fee">
                                                    <i class="mdi mdi-trash-can"></i>
                                                </button>
                                                <form id="delete_fee_admin_{{ $fee->id }}" method="POST"
                                                    action="{{ route('admin.reports.fee.destroy', $fee->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif

                                        </td>
                                    @endif

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="9" class="text-center">
                                        No Fees
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                    <div class="mt-3">
                        {{ $fees->links() }}
                    </div>

                </div>

            </div>

        </div>

    </div>

    @if($isAction == 'true')
        {{-- Payment Modal --}}

        <div class="modal fade" id="paymentModal">

            <div class="modal-dialog">
                <div class="modal-content">

                    <form method="POST" action="{{ route('staff.fees.pay') }}">
                        @csrf

                        <input type="hidden" name="fee_id" id="fee_id">

                        <div class="modal-header">
                            <h5>Mark Payment</h5>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label>Total Fee</label>
                                <input type="text" id="total_fee" class="form-control" readonly>
                            </div>

                            <div class="mb-3">
                                <label>Balance to Pay</label>
                                <input type="text" id="balance_to_pay" class="form-control" readonly>
                            </div>

                            <div class="mb-3">
                                <label>Amount Paying</label>
                                <input type="number" step="0.01" name="paid_amount" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Paid Date</label>
                                <input type="date" name="paid_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control"></textarea>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-success" type="submit"
                                onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">Save
                                Payment</button>
                        </div>

                    </form>

                </div>
            </div>

        </div>

        {{-- Payment History Modal --}}
        <div class="modal fade" id="paymentsModal">

            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5>Payment History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p><strong>Total Paid:</strong> ₹ <span id="totalPaid"></span></p>

                        <table class="table table-bordered  align-middle table-nowrap mb-0">

                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>

                            <tbody id="paymentsTableBody">

                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Loading...
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

        </div>

    @endif

    @if(auth('admin')->check())
        {{-- Refund Fee Modal --}}
        <div class="modal fade" id="refundFeeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.fees.refund') }}">
                        @csrf
                        <input type="hidden" name="fee_id" id="refund_fee_id">

                        <div class="modal-header">
                            <h5 class="modal-title">Record Fee Refund</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Student Name</label>
                                <input type="text" id="refund_student_name" class="form-control-plaintext border-bottom p-0 fw-bold text-dark" readonly style="font-size: 1.1rem;">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Total Fee Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="text" id="refund_total_fee" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Net Paid Amount (Max Refundable)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="text" id="refund_net_paid" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Refund Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="refund_amount" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Payment Method</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Refund Date</label>
                                <input type="date" name="refund_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="submit"
                                onclick="this.disabled=true; this.innerText='Processing...'; this.form.submit();">
                                Process Refund
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Refund History Modal --}}
    <div class="modal fade" id="refundHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Refund History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p><strong>Total Refunded:</strong> ₹ <span id="totalRefundedAmount">0.00</span></p>

                    <table class="table table-bordered align-middle table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody id="refundsTableBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    @if($isAction == 'true')

        {{--
        <script>
            $('.select2').select2({
                placeholder: "Select option",
                allowClear: true
            });
        </script> --}}
        <script>

            $('.markPaidBtn').click(function () {

                let feeId = $(this).data('id');
                let amount = $(this).data('amount');
                let remaining = $(this).data('remaining');
                let walletBalance = parseFloat($(this).data('wallet') || 0);

                $('#fee_id').val(feeId);
                $('#total_fee').val(amount);
                $('#balance_to_pay').val(remaining);

                let select = $('#paymentModal select[name="payment_method"]');
                select.find('option[value="wallet"]').remove();
                if (walletBalance > 0) {
                    select.append('<option value="wallet">Wallet Balance (Available: ₹ ' + walletBalance.toFixed(2) + ')</option>');
                }

                $('#paymentModal input[name="paid_amount"]').val(remaining).attr('max', remaining);

                $('#paymentModal').modal('show');

            });

        </script>

        <script>

            $('.viewPaymentsBtn').click(function () {

                let url = $(this).data('url');

                $('#paymentsTableBody').html(`
                                                        <tr>
                                                            <td colspan="4" class="text-center">Loading...</td>
                                                        </tr>
                                                    `);

                $('#totalPaid').text('0.00');

                $.get(url, function (res) {

                    let rows = '';
                    let total = 0;

                    if (res.payments.length === 0) {

                        rows = `
                                                                <tr>
                                                                    <td colspan="4" class="text-center text-muted">
                                                                        No payments found
                                                                    </td>
                                                                </tr>
                                                            `;

                    } else {

                        res.payments.forEach(p => {

                            let amount = parseFloat(p.paid_amount);
                            total += amount;

                            rows += `
                                                                    <tr>
                                                                        <td>${formatDate(p.paid_date)}</td>
                                                                        <td>₹ ${amount.toFixed(2)}</td>
                                                                        <td>${formatMethod(p.payment_method)}</td>
                                                                        <td>${p.notes ?? '-'}</td>
                                                                    </tr>
                                                                `;

                        });

                    }

                    $('#paymentsTableBody').html(rows);
                    $('#totalPaid').text(total.toFixed(2));

                    $('#paymentsModal').modal('show');

                });

            });

            function formatDate(dateStr) {
                let d = new Date(dateStr);
                return d.toLocaleDateString('en-IN', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            }

            function formatMethod(method) {
                return method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            }

            // Delete Fee Handler (Admin only)
            $(document).on('click', '.deleteFeeBtnAdmin', function (e) {
                e.preventDefault();
                var feeId = $(this).data('id');
                var studentName = $(this).data('student');
                var amount = $(this).data('amount');

                Swal.fire({
                    title: 'Delete Fee?',
                    html: '<p>You are about to delete an unpaid fee for <strong>' + studentName + '</strong>.</p>' +
                        '<ul>' +
                        '<li>Fee amount: ₹ ' + amount + '</li>' +
                        '</ul>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $('#delete_fee_admin_' + feeId).submit();
                    }
                });
            });

            // Send Notification Handler
            $('.sendNotificationBtn').click(function (e) {
                e.preventDefault();

                let feeId = $(this).data('id');
                let button = $(this);

                if (confirm('Send notification to student?')) {
                    button.prop('disabled', true);
                    button.html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        type: 'POST',
                        url: '{{ route("staff.fees.send-notification") }}',
                        data: {
                            _token: '{{ csrf_token() }}',
                            fee_id: feeId
                        },
                        success: function (response) {
                            if (response.success) {
                                alert(response.message);
                                button.html('<i class="fas fa-check text-success"></i>');
                            } else {
                                alert('Error: ' + response.message);
                                button.prop('disabled', false);
                                button.html('<i class="fas fa-bell"></i>');
                            }
                        },
                        error: function (xhr) {
                            let errorMsg = 'An error occurred';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            alert('Error: ' + errorMsg);
                            button.prop('disabled', false);
                            button.html('<i class="fas fa-bell"></i>');
                        }
                    });
                }
            });

        </script>
    @endif

    @if(auth('admin')->check())
        <script>
            // Delete Fee Handler (Admin only)
            $(document).on('click', '.deleteFeeBtnAdmin', function (e) {
                e.preventDefault();
                var feeId = $(this).data('id');
                var studentName = $(this).data('student');
                var amount = $(this).data('amount');

                Swal.fire({
                    title: 'Delete Fee?',
                    html: '<p>You are about to delete an unpaid fee for <strong>' + studentName + '</strong>.</p>' +
                        '<ul>' +
                        '<li>Fee amount: ₹ ' + amount + '</li>' +
                        '</ul>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $('#delete_fee_admin_' + feeId).submit();
                    }
                });
            });

            // Refund Fee Handler (Admin only)
            $(document).on('click', '.refundFeeBtn', function () {
                let feeId = $(this).data('id');
                let student = $(this).data('student');
                let amount = parseFloat($(this).data('amount'));
                let netPaid = parseFloat($(this).data('netpaid'));

                $('#refund_fee_id').val(feeId);
                $('#refund_student_name').val(student);
                $('#refund_total_fee').val(amount.toFixed(2));
                $('#refund_net_paid').val(netPaid.toFixed(2));
                $('#refund_amount').val(netPaid.toFixed(2)).attr('max', netPaid);

                $('#refundFeeModal').modal('show');
            });
        </script>
    @endif

    <script>
        $(document).on('click', '.viewRefundsBtn', function () {
            let url = $(this).data('url');

            $('#refundsTableBody').html(`
                <tr>
                    <td colspan="4" class="text-center">Loading...</td>
                </tr>
            `);
            $('#totalRefundedAmount').text('0.00');

            $.get(url, function (res) {
                let rows = '';
                let total = 0;

                if (!res.refunds || res.refunds.length === 0) {
                    rows = `
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No refunds found
                            </td>
                        </tr>
                    `;
                } else {
                    res.refunds.forEach(r => {
                        let amount = parseFloat(r.amount);
                        total += amount;

                        rows += `
                            <tr>
                                <td>${formatDate(r.refund_date)}</td>
                                <td>₹ ${amount.toFixed(2)}</td>
                                <td>${formatMethod(r.payment_method)}</td>
                                <td>${r.notes ?? '-'}</td>
                            </tr>
                        `;
                    });
                }

                $('#refundsTableBody').html(rows);
                $('#totalRefundedAmount').text(total.toFixed(2));
                $('#refundHistoryModal').modal('show');
            });
        });

        // Date helper if not defined in this scope
        if (typeof formatDate !== 'function') {
            window.formatDate = function(dateStr) {
                let d = new Date(dateStr);
                return d.toLocaleDateString('en-IN', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            }
        }

        // Method helper if not defined in this scope
        if (typeof formatMethod !== 'function') {
            window.formatMethod = function(method) {
                return method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            }
        }
    </script>

@endsection