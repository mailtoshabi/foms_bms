@section('title', 'Fees')

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
                                <label class="form-label fw-bold">From Due Date</label>
                                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">To Due Date</label>
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
                                    $remaining = $fee->amount - $paid;

                                    $dueDate = \Carbon\Carbon::parse($fee->due_date);

                                    // Only calculate overdue if still pending
                                    $daysOverdue = ($remaining > 0 && $dueDate->isPast())
                                        ? $dueDate->startOfDay()->diffInDays(now()->startOfDay())
                                        : 0;
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
                                        </a>
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

                                        <small class="text-danger">
                                            Remaining: ₹ {{ number_format($remaining, 2) }}
                                        </small>
                                        @php
                                            $percentage = $fee->amount > 0 ? ($paid / $fee->amount) * 100 : 0;
                                        @endphp

                                        <div class="progress mt-1" style="height:6px;" title="{{ round($percentage) }}% paid">
                                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%">
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($fee->due_date)->format('d M Y') }}
                                        @php
                                            $lastPaymentDate = $fee->payments()->max('paid_date');
                                        @endphp
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
                                    </td>
                                    @if($isAction == 'true' || auth('admin')->check())
                                        <td>

                                            @if($isAction == 'true')
                                                @if($tab !== 'paid')
                                                    <button class="btn btn-sm btn-success markPaidBtn" data-id="{{ $fee->id }}"
                                                        data-amount="{{ $fee->amount }}" data-remaining="{{ $remaining }}" {{ $remaining <= 0 ? 'disabled' : '' }}>
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
                                    <td colspan="8" class="text-center">
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

        {{-- Payment History Modal End --}}
    @endif

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

                $('#fee_id').val(feeId);
                $('#total_fee').val(amount);

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
        </script>
    @endif

@endsection