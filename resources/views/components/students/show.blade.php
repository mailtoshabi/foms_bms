@section('title', 'Student Details')

@section('content')

    @if(auth('admin')->check())
        <x-alerts />
    @endif

    <div class="row">

        {{-- =========================
        STUDENT PROFILE
        ========================= --}}
        <div class="col-md-4">

            <div class="card">

                <div class="card-header d-flex align-items-center">
                    <a href="javascript:window.history.back();"
                        class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <h5 class="mb-0">Student Profile</h5>
                </div>

                <div class="card-body text-center">

                    @if($student->photo)
                        <img src="{{ asset('storage/' . $student->photo) }}" class="rounded-circle mb-3" width="120">
                    @endif

                    <h5>{{ $student->name }}</h5>

                    <p class="text-muted mb-1">
                        {{ $student->admission_no }}
                    </p>

                    <p class="text-muted mb-1">
                        {{ $student->formatted_contact_number }}
                        @if($student->is_whatsapp_different)
                            <br><small class="text-success"><i class="mdi mdi-whatsapp"></i>
                                {{ $student->formatted_whatsapp_number }}</small>
                        @endif
                    </p>

                    <p class="text-muted mb-1">
                        {{ $student->email ?? '-' }}
                    </p>

                    <span class="badge bg-success">
                        {{ ucfirst($student->status) }}
                    </span>
                    @if($student->is_blocked)
                        <span class="badge bg-danger">Blocked</span>
                    @endif

                    @if($showButtons == 'true' && auth('admin')->check())
                        <div class="mt-4 p-3 bg-light rounded text-start shadow-sm border border-2 border-soft-primary">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-uppercase text-muted font-size-11 fw-bold tracking-wider">Wallet Balance</span>
                                <span class="badge bg-info font-size-10">Advance</span>
                            </div>
                            <h3 class="mb-3 text-primary">₹ {{ number_format($student->wallet_balance ?? 0, 2) }}</h3>

                            <!-- Autopay Toggle Form -->
                            <form method="POST" action="{{ route('admin.students.wallet.toggle-autopay', encrypt($student->id)) }}" class="mb-3">
                                @csrf
                                <div class="form-check form-switch d-flex align-items-center justify-content-between p-0">
                                    <label class="form-check-label text-muted font-size-12 cursor-pointer" for="autopaySwitch">
                                        <i class="fas fa-magic text-primary me-1"></i> Fee Autopay from Balance
                                    </label>
                                    <input class="form-check-input ms-0 cursor-pointer" type="checkbox" id="autopaySwitch" 
                                           style="width: 2.2em; height: 1.1em;"
                                           {{ $student->is_wallet_autopay_enabled ? 'checked' : '' }} 
                                           onchange="this.form.submit()">
                                </div>
                            </form>

                            <style>
                                @media (max-width: 376px) {
                                    .wallet-action-buttons {
                                        flex-direction: column !important;
                                        align-items: center !important;
                                    }
                                    .wallet-action-buttons button {
                                        width: 150px !important;
                                        flex-grow: 0 !important;
                                    }
                                }
                            </style>
                            <div class="d-flex gap-2 wallet-action-buttons">
                                <button class="btn btn-sm btn-primary flex-grow-1 font-size-11" data-bs-toggle="modal" data-bs-target="#walletDepositModal">
                                    <i class="fas fa-plus-circle me-1"></i> Record Advance
                                </button>
                                @if(($student->wallet_balance ?? 0) > 0)
                                    <button class="btn btn-sm btn-outline-danger flex-grow-1 font-size-11" data-bs-toggle="modal" data-bs-target="#walletRefundModal">
                                        <i class="fas fa-arrow-alt-circle-down me-1"></i> Refund
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="mt-3 p-2 bg-light rounded text-start border">
                            <small class="text-muted d-block uppercase font-size-10 fw-bold">Wallet Balance</small>
                            <span class="text-primary fw-bold">₹ {{ number_format($student->wallet_balance ?? 0, 2) }}</span>
                        </div>
                    @endif

                    <hr>

                    <p><strong>Parent:</strong> {{ $student->parent_name ?? '-' }}</p>

                    <p><strong>DOB:</strong>
                        {{ $student->dob ? $student->dob->format('d M Y') : '-' }}
                    </p>

                    <p><strong>Address:</strong></p>
                    <p class="text-muted">
                        {{ $student->address ?? '-' }}
                    </p>

                    <hr>

                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#familyMembersModal">
                            <i class="fas fa-users me-1"></i> View Family Members ({{ $student->relatedStudents->count() }})
                        </button>

                        @if(auth('staff')->check())
                            <a href="{{ route('staff.students.create', ['relative_of' => encrypt($student->id)]) }}" 
                               class="btn btn-sm btn-outline-primary w-100">
                                <i class="fas fa-user-plus me-1"></i> Add Family Member
                            </a>
                        @elseif(auth('admin')->check())
                            <a href="{{ route('admin.students.create', ['relative_of' => encrypt($student->id)]) }}" 
                               class="btn btn-sm btn-outline-primary w-100">
                                <i class="fas fa-user-plus me-1"></i> Add Family Member
                            </a>
                        @endif

                        @if(auth('staff')->check() || auth('admin')->check())
                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#linkExistingFamilyModal">
                                <i class="fas fa-link me-1"></i> Link Existing Student
                            </button>
                        @endif
                    </div>

                </div>

            </div>

        </div>



        {{-- =========================
        CLASS / COURSE DETAILS
        ========================= --}}
        <div class="col-md-8">

            <div class="card">

                <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">

                    <h5 class="mb-0">Course & Class Details</h5>
                    @php $routePrefix = auth('admin')->check() ? 'admin' : 'staff'; @endphp
                    @if($showButtons == 'true' && (auth('staff')->check() || auth('admin')->check()))
                        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
                            <button class="btn btn-sm btn-primary w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#assignClassModal">

                                <i class="fas fa-plus"></i> Assign Class

                            </button>

                            <button class="btn btn-sm btn-info w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#changeClassModal">

                                <i class="fas fa-exchange-alt"></i> Change Class

                            </button>

                            <button class="btn btn-sm btn-success w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#promoteClassModal">

                                <i class="fas fa-arrow-up"></i> Promote Class

                            </button>
                        </div>
                    @endif

                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered  align-middle table-nowrap mb-0">

                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Class</th>
                                    <th>Type</th>
                                    <th>Days</th>
                                    <th>Time</th>
                                    <th>Assigned Date</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($student->class_rooms as $class)

                                    <tr>

                                        <td>{{ $class->course->name ?? '-' }}</td>

                                        <td>
                                            <a href="{{ auth('admin')->check() ? route('admin.class_rooms.show', encrypt($class->id)) : route('staff.class_rooms.show', encrypt($class->id)) }}">
                                                {{ $class->name }}
                                            </a>
                                            @if($class->is_completed)
                                                <span class="badge bg-success">Completed</span>
                                            @endif
                                        </td>

                                        <td>{{ ucfirst($class->classType->name ?? '-') }}</td>

                                        <td>
                                            @if($class->selected_days)

                                                <small>

                                                    {{ implode(', ', $class->selected_days ?? []) }}


                                                </small>

                                            @endif
                                        </td>

                                        <td>{{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}
                                        </td>

                                        <td>{{ $class->pivot->assigned_date ? \Carbon\Carbon::parse($class->pivot->assigned_date)->format('d M Y') : '-' }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

        </div>


        {{-- =========================
        TEACHER DETAILS
        ========================= --}}
        <div class="col-md-6">

            <div class="card">

                <div class="card-header">
                    <h5>Teacher Details</h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered  align-middle table-nowrap mb-0">

                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($teachers as $teacher)

                                    <tr>

                                        <td>
                                            <a href="{{ auth('admin')->check() ? route('admin.reports.teachers.show', encrypt($teacher->id)) : route('staff.teachers.show', encrypt($teacher->id)) }}">
                                                {{ $teacher->name }}
                                            </a>
                                        </td>
                                        <td>{{ $teacher->formatted_phone }}</td>
                                        <td>{{ $teacher->email }}</td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

        </div>



        {{-- =========================
        ATTENDANCE DETAILS
        ========================= --}}
        <div class="col-md-6">

            <div class="card">

                <div class="card-header">
                    <h5>Attendance Summary</h5>
                </div>

                <div class="card-body">

                    <p><strong>Total Classes:</strong> {{ $attendance['total'] }}</p>

                    <p><strong>Present:</strong>
                        <span class="text-success">
                            {{ $attendance['present'] }}
                        </span>
                    </p>

                    <p><strong>Absent:</strong>
                        <span class="text-danger">
                            {{ $attendance['absent'] }}
                        </span>
                    </p>

                </div>

            </div>

        </div>



        {{-- =========================
        FEE PAYMENT DETAILS
        ========================= --}}
        <div class="col-md-12">

            <div class="card">

                <div class="card-header">
                    <h5>Fee Payment History</h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered  align-middle table-nowrap mb-0">

                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Class</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    @if($showButtons == 'true' && auth('staff')->check())
                                        <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody>

                                @php
                                    $staffUser = auth('staff')->user();
                                    $isEnrolmentOnly = $staffUser && $staffUser->hasRoleId(utility('id_enrolment_dept'));
                                    $displayFees = $isEnrolmentOnly ? $student->fees->where('type', 'admission') : $student->fees;
                                @endphp

                                @foreach($displayFees as $fee)
                                    @php
                                        $paid = $fee->paid_amount ?? 0;
                                        $remaining = $fee->amount - $paid;
                                    @endphp
                                    <tr>

                                        <td>{{ $fee->created_at->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ auth('admin')->check() ? route('admin.class_rooms.show', encrypt($fee->classRoom->id)) : route('staff.class_rooms.show', encrypt($fee->classRoom->id)) }}">{{ $fee->classRoom->name ?? '-' }}</a></td>
                                        <td>{{ ucfirst($fee->type) . ' Fee' }}</td>

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

                                            <div class="progress mt-1" style="height:6px;"
                                                title="{{ round($percentage) }}% paid">
                                                <div class="progress-bar bg-success" style="width: {{ $percentage }}%">
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            @php
                                                $badgeClasses = [
                                                    'paid' => 'bg-success',
                                                    'partial' => 'bg-warning',
                                                    'unpaid' => 'bg-danger',
                                                ];
                                            @endphp
                                            <span class="badge {{ $badgeClasses[$fee->status] ?? 'bg-secondary' }}">
                                                {{ ucfirst($fee->status ?? '-') }}
                                            </span>
                                        </td>

                                        @if($showButtons == 'true' && auth('staff')->check())
                                            @php
                                                $feePaid = $fee->paid_amount ?? 0;
                                                $feeRemaining = $fee->amount - $feePaid;
                                            @endphp
                                            <td>
                                                @if($fee->status !== 'paid')
                                                    <button class="btn btn-sm btn-success studentFeeMarkPaidBtn"
                                                        data-id="{{ $fee->id }}" data-amount="{{ $fee->amount }}"
                                                        data-remaining="{{ $feeRemaining }}" data-wallet="{{ $student->wallet_balance ?? 0 }}" {{ $feeRemaining <= 0 ? 'disabled' : '' }}>
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif

                                                <button class="btn btn-sm btn-info studentFeeViewPaymentsBtn"
                                                    data-url="{{ route('staff.fees.payments', $fee->id) }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                @if($fee->status === 'paid')
                                                    <a href="{{ route('staff.fees.invoice.download', $fee->id) }}"
                                                        class="btn btn-sm btn-danger" title="Download Invoice PDF" target="_blank">
                                                        <i class="mdi mdi-file-pdf-box"></i>
                                                    </a>
                                                @endif

                                                @if($fee->status !== 'paid')
                                                    <button class="btn btn-sm btn-warning studentFeeSendNotificationBtn"
                                                        data-id="{{ $fee->id }}" title="Send notification">
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        @endif

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

        </div>

        {{-- =========================
        WALLET TRANSACTION HISTORY
        ========================= --}}
        <div class="col-md-12 mt-4">

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Wallet / Advance Payment Ledger</h5>
                    @if($student->wallet_balance > 0)
                        <span class="badge bg-info">Current Advance: ₹ {{ number_format($student->wallet_balance, 2) }}</span>
                    @endif
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle table-nowrap mb-0">

                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Reference/Invoice</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($student->walletTransactions as $tx)
                                    <tr>
                                        <td>{{ $tx->created_at->format('d M Y h:i A') }}</td>
                                        <td>
                                            @if($tx->type === 'deposit')
                                                <span class="badge bg-success">Deposit</span>
                                            @elseif($tx->type === 'refund')
                                                <span class="badge bg-danger">Refund</span>
                                            @else
                                                <span class="badge bg-primary">Fee Payment</span>
                                            @endif
                                        </td>
                                        <td class="{{ $tx->amount > 0 ? 'text-success' : 'text-danger' }}">
                                            <strong>{{ $tx->amount > 0 ? '+' : '' }} ₹ {{ number_format($tx->amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            {{ $tx->payment_method ? ucfirst(str_replace('_', ' ', $tx->payment_method)) : '-' }}
                                        </td>
                                        <td>
                                            @if($tx->fee_id && $tx->fee)
                                                <a href="{{ route('staff.fees.invoice', encrypt($tx->fee_id)) }}" target="_blank">
                                                    {{ $tx->fee->receipt_no }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $tx->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No wallet transactions recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

        </div>



        {{-- =========================
        CLASS NOTES
        ========================= --}}
        <div class="col-md-12">

            <div class="card">

                <div class="card-header">
                    <h5>Class Notes</h5>
                </div>

                <div class="card-body">

                    @forelse($notes as $note)

                        <div class="border rounded p-3 mb-3">

                            <strong>{{ $note->title }}</strong>

                            <p class="text-muted small">
                                {{ $note->created_at->format('d M Y') }}
                            </p>

                            <p>{{ $note->content }}</p>

                        </div>

                    @empty

                        <p class="text-muted">No notes available.</p>

                    @endforelse

                </div>

            </div>

        </div>


        {{-- =========================
        Whasapp link
        ========================= --}}
        <div class="col-md-12">

            <div class="card">

                <div class="card-header">
                    <h5>Send Login Credentials</h5>
                </div>

                <div class="card-body">

                    <a href="{{ studentWhatsappMessage($student, $student->phone) }}" class="btn btn-success">

                        <i class="fab fa-whatsapp"></i> Send Credentials

                    </a>

                </div>

            </div>

        </div>


        {{-- =========================
        Fee exemption
        ========================= --}}
        <div class="col-md-12 mb-5">

            <div class="card-header d-flex justify-content-between align-items-center">

                <h5 class="mb-0">Fee Exemption & Discount</h5>
                @if($showButtons == 'true' && auth('staff')->check())
                    @if(auth('staff')->user()->hasRoleId(utility('id_enrolment_dept')) || auth('staff')->user()->hasRoleId(utility('id_operation_dept')))
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#feeExemptionModal">

                                <i class="fas fa-ban"></i> Exemption

                            </button>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#discountModal">

                                <i class="fas fa-tag"></i> Discount

                            </button>
                        </div>
                    @endif
                @endif
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-2">

                        <strong>Admission/First Month Fee:</strong>

                        @if($student->is_admission_fee_exempted)
                            <span class="text-success ms-2">
                                <i class="fas fa-check-circle"></i> Exempted
                            </span>
                        @else
                            <span class="text-muted ms-2">
                                <i class="fas fa-times-circle"></i> Not Exempted
                            </span>
                        @endif

                    </div>

                    <div class="col-md-6 mb-2">

                        <strong>Monthly Fee:</strong>

                        {{-- @if($student->is_monthly_fee_exempted)
                        <span class="badge bg-success ms-2">Exempted</span>
                        @else
                        <span class="badge bg-secondary ms-2">Not Exempted</span>
                        @endif --}}

                        @if($student->is_monthly_fee_exempted)
                            <span class="text-success ms-2">
                                <i class="fas fa-check-circle"></i> Exempted
                            </span>
                        @else
                            <span class="text-muted ms-2">
                                <i class="fas fa-times-circle"></i> Not Exempted
                            </span>
                        @endif

                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Admission/First Month Fee Discount:</strong>
                        @if($student->admission_fee_discount > 0)
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="fas fa-tag"></i> ₹ {{ number_format($student->admission_fee_discount, 2) }}
                            </span>
                        @else
                            <span class="text-muted ms-2">No Discount</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Monthly Fee Discount:</strong>
                        @if($student->monthly_fee_discount > 0)
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="fas fa-tag"></i> ₹ {{ number_format($student->monthly_fee_discount, 2) }}
                            </span>
                        @else
                            <span class="text-muted ms-2">No Discount</span>
                        @endif
                    </div>

                </div>

            </div>

        </div>

        @if(auth('admin')->check())
            {{-- =========================
            BLOCK / UNBLOCK STUDENT
            ========================= --}}
            <div class="col-md-12 mb-5">
                <div class="card border border-danger">
                    <div class="card-header bg-soft-danger d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-danger"><i class="fas fa-user-shield me-1"></i> Student Access Control</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Account Status:</strong>
                                @if($student->is_blocked)
                                    <span class="badge bg-danger ms-2">Blocked</span>
                                    <p class="text-muted mt-2 mb-0 font-size-13">This student is currently blocked. They are unable to log in, and fee generation is suspended.</p>
                                @else
                                    <span class="badge bg-success ms-2">Unblocked / Active</span>
                                    <p class="text-muted mt-2 mb-0 font-size-13">This student has active access. They can log in and fees will generate normally.</p>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('admin.reports.students.toggleBlock', encrypt($student->id)) }}" 
                                   class="btn {{ $student->is_blocked ? 'btn-success' : 'btn-danger' }}"
                                   onclick="return confirm('Are you sure you want to {{ $student->is_blocked ? 'unblock' : 'block' }} this student?')">
                                    <i class="fas {{ $student->is_blocked ? 'fa-unlock' : 'fa-ban' }} me-1"></i>
                                    {{ $student->is_blocked ? 'Unblock Student' : 'Block Student' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- Assign Class Modal --}}

    <div class="modal fade" id="assignClassModal">

        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="{{ route($routePrefix . '.students.assign.class') }}">

                    @csrf

                    <input type="hidden" name="student_id" value="{{ $student->id }}">

                    <div class="modal-header">

                        <h5 class="modal-title">Assign Class</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">Select Class</label>

                            <select name="class_room_id" class="form-control select2-class-ajax"
                                data-ajax-url="{{ route($routePrefix . '.students.active-classes.search') }}?exclude_student_id={{ $student->id }}"
                                required>

                                <option value="">Search active class...</option>

                            </select>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button class="btn btn-primary" type="submit"
                            onclick="if(confirm('Have you checked and confirmed Fee Discount/Exemption?')) { this.disabled=true; this.innerText='Assigning...'; this.form.submit(); } else { return false; }">

                            Assign Class

                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>

    {{-- Change Class Modal --}}

    <div class="modal fade" id="changeClassModal">

        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="{{ route($routePrefix . '.students.change.class') }}">

                    @csrf

                    <input type="hidden" name="student_id" value="{{ $student->id }}">

                    <div class="modal-header">

                        <h5 class="modal-title">Change Class</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        @php
                            $hasGroupClasses = $student->class_rooms->filter(fn($c) => $c->classType && $c->classType->name == 'group')->count() > 0;
                        @endphp

                        @if(!$hasGroupClasses)
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                Student must be enrolled in at least one <strong>Group Class</strong> to use the Change Class
                                feature.
                            </div>
                        @endif

                        <div class="mb-3">

                            <label class="form-label">From Class</label>

                            <select name="from_class_id" class="form-control select2" required>

                                <option value="">Select current class...</option>
                                @foreach($student->class_rooms as $class)
                                    @if($class->classType && $class->classType->name == 'group')
                                        <option value="{{ $class->id }}">
                                            {{ $class->course->name ?? 'No Course' }} - {{ $class->name }}
                                        </option>
                                    @endif
                                @endforeach

                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">To Class</label>

                            <select name="to_class_id" class="form-control select2-class-ajax"
                                data-ajax-url="{{ route($routePrefix . '.students.active-classes.search') }}?type=group&exclude_student_id={{ $student->id }}"
                                required>

                                <option value="">Search new class...</option>

                            </select>

                            <small class="text-muted">Unpaid fees (Admission & Monthly) will be transferred to the new
                                class.</small>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button class="btn btn-info" type="submit"
                            onclick="if(confirm('Have you checked and confirmed Fee Discount/Exemption?')) { this.disabled=true; this.innerText='Changing...'; this.form.submit(); } else { return false; }">

                            Change Class

                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>

    {{-- Promote Class Modal --}}

    <div class="modal fade" id="promoteClassModal">

        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="{{ route($routePrefix . '.students.promote.class') }}">

                    @csrf

                    <input type="hidden" name="student_id" value="{{ $student->id }}">

                    <div class="modal-header">

                        <h5 class="modal-title">Promote Class</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">From Class</label>

                            <select name="from_class_id" class="form-control select2" required>

                                <option value="">Select current class...</option>
                                @foreach($student->class_rooms as $class)
                                    <option value="{{ $class->id }}">
                                        {{ $class->course->name ?? 'No Course' }} - {{ $class->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">To Class (Promotion)</label>

                            <select name="to_class_id" class="form-control select2-class-ajax"
                                data-ajax-url="{{ route($routePrefix . '.students.active-classes.search') }}?type=group,individual&exclude_student_id={{ $student->id }}"
                                required>

                                <option value="">Search new class...</option>

                            </select>

                            <div class="alert alert-warning mt-2">
                                <small>
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Requires all fees in the current class to be fully paid.
                                </small>
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button class="btn btn-success" type="submit"
                            onclick="if(confirm('Have you checked and confirmed Fee Discount/Exemption?')) { this.disabled=true; this.innerText='Promoting...'; this.form.submit(); } else { return false; }">

                            Promote Student

                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>

    {{-- Discount Modal --}}

    <div class="modal fade" id="discountModal">

        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="{{ route('staff.students.discount') }}">

                    @csrf

                    <input type="hidden" name="student_id" value="{{ $student->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title">Fee Discount</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Admission/First Month Fee Discount (₹)</label>
                            <input type="number" name="admission_fee_discount" class="form-control"
                                value="{{ $student->admission_fee_discount ?? 0 }}" min="0" step="0.01">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Monthly Fee Discount (₹)</label>
                            <input type="number" name="monthly_fee_discount" class="form-control"
                                value="{{ $student->monthly_fee_discount ?? 0 }}" min="0" step="0.01">
                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button class="btn btn-warning" type="submit"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                            <i class="fas fa-save"></i> Save Discount
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>

    {{-- Discount Modal End --}}

    {{-- Student Fee Payment Modal --}}
    @if($showButtons == 'true' && auth('staff')->check())
        <div class="modal fade" id="studentFeePaymentModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('staff.fees.pay') }}">
                        @csrf
                        <input type="hidden" name="fee_id" id="studentFeeId">
                        <div class="modal-header">
                            <h5>Mark Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Total Fee</label>
                                <input type="text" id="studentFeeTotalFee" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label>Balance to Pay</label>
                                <input type="text" id="studentFeeBalanceToPay" class="form-control" readonly>
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

        {{-- Student Fee Payment History Modal --}}
        <div class="modal fade" id="studentFeePaymentsModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Payment History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Total Paid:</strong> ₹ <span id="studentFeeTotalPaid"></span></p>
                        <table class="table table-bordered  align-middle table-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody id="studentFeePaymentsTableBody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Fee exemption Modal --}}

    <div class="modal fade" id="feeExemptionModal">

        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="{{ route('staff.students.fee.exemption') }}">

                    @csrf

                    <input type="hidden" name="student_id" value="{{ $student->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title">Fee Exemption</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="form-check mb-3">

                            <input type="checkbox" name="is_admission_fee_exempted" value="1" class="form-check-input" {{ $student->is_admission_fee_exempted ? 'checked' : '' }}>

                            <label class="form-check-label">
                                Admission/First Month Fee Exemption
                            </label>

                        </div>

                        <div class="form-check">

                            <input type="checkbox" name="is_monthly_fee_exempted" value="1" class="form-check-input" {{ $student->is_monthly_fee_exempted ? 'checked' : '' }}>

                            <label class="form-check-label">
                                Monthly Fee Exemption
                            </label>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button class="btn btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">

                            Save

                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>

    {{-- Wallet Deposit Modal --}}
    @if($showButtons == 'true' && auth('admin')->check())
        <div class="modal fade" id="walletDepositModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.fees.wallet.deposit') }}">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Record Advance Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Amount (₹)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required min="1">
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
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" placeholder="e.g. Advance payment for upcoming fees"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-success" type="submit"
                                onclick="this.disabled=true; this.innerText='Processing...'; this.form.submit();">
                                Record Advance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Wallet Refund Modal --}}
        <div class="modal fade" id="walletRefundModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.fees.wallet.refund') }}">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Record Wallet Refund</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i> Available balance for refund: <strong>₹ {{ number_format($student->wallet_balance ?? 0, 2) }}</strong>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Amount (₹)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required min="1" max="{{ $student->wallet_balance ?? 0 }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Refund Method</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" placeholder="e.g. Refund of remaining advance balance"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="submit"
                                onclick="this.disabled=true; this.innerText='Processing...'; this.form.submit();">
                                Record Refund
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Family Members Modal --}}
    <div class="modal fade" id="familyMembersModal" tabindex="-1" aria-labelledby="familyMembersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="familyMembersModalLabel">Family Members / Siblings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    @if($student->relatedStudents->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($student->relatedStudents as $relatedStudent)
                                <div class="list-group-item d-flex align-items-center justify-content-between p-3 border-0 border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <img class="rounded-circle" width="40" height="40"
                                             src="@if (($relatedStudent->photo == '') || (empty($relatedStudent->photo))) https://ui-avatars.com/api/?name={{ urlencode($relatedStudent->name) }}&size=200 @else {{ URL::asset('storage/' . $relatedStudent->photo) }} @endif"
                                             alt="Avatar">
                                        <div>
                                            <h6 class="mb-0 fw-bold">
                                                <a href="{{ auth('admin')->check() ? route('admin.reports.students.show', encrypt($relatedStudent->id)) : route('staff.students.show', encrypt($relatedStudent->id)) }}">
                                                    {{ $relatedStudent->name }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">{{ $relatedStudent->admission_no }}</small>
                                        </div>
                                    </div>
                                    
                                    {{-- Unlink button triggers modal --}}
                                    <button type="button" class="btn btn-sm btn-outline-danger unlink-sibling-btn"
                                            data-id="{{ encrypt($relatedStudent->id) }}"
                                            data-name="{{ $relatedStudent->name }}"
                                            data-action="{{ route($routePrefix . '.students.relations.destroy', ['id' => encrypt($student->id), 'related_id' => encrypt($relatedStudent->id)]) }}"
                                            title="Unlink Sibling">
                                        <i class="fas fa-unlink"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <p class="mb-0">No family members linked.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Link Existing Family Modal --}}
    @if(auth('staff')->check() || auth('admin')->check())
        <div class="modal fade" id="linkExistingFamilyModal" tabindex="-1" aria-labelledby="linkExistingFamilyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route($routePrefix . '.students.relations.store', encrypt($student->id)) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="linkExistingFamilyModalLabel">Link Existing Student as Family Member</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Student to Link</label>
                                <select name="related_student_id" id="related_student_id" class="form-control select2-relations-ajax" required>
                                    <option value="">Search student by name, admission no, or phone...</option>
                                </select>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle text-primary me-1"></i> 
                                    <strong>Important:</strong> The linked student's contact number, WhatsApp number, country, and login phone will be automatically updated to match <strong>{{ $student->name }}</strong>'s contact details.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-success" type="submit"
                                onclick="this.disabled=true; this.innerText='Linking...'; this.form.submit();">
                                <i class="fas fa-link me-1"></i> Link Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Unlink Sibling Modal --}}
        <div class="modal fade" id="unlinkSiblingModal" tabindex="-1" aria-labelledby="unlinkSiblingModalLabel" aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" id="unlinkSiblingForm" action="">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header">
                            <h5 class="modal-title" id="unlinkSiblingModalLabel">Unlink Sibling / Family Member</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>You are unlinking <strong id="unlinkSiblingName"></strong> from the family group.</p>
                            <p class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Since they will no longer share the family's phone number, you must assign a new unique contact number for this student.</p>
                            
                            <div class="mb-3">
                                <label for="new_contact_number" class="form-label fw-bold">New Contact Number</label>
                                <input type="text" name="new_contact_number" id="new_contact_number" class="form-control" required placeholder="e.g. 9876543210" maxlength="15">
                                <small class="text-muted">The student's password will also be automatically reset to this new number for login.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="submit"
                                onclick="this.disabled=true; this.innerText='Unlinking...'; this.form.submit();">
                                <i class="fas fa-unlink me-1"></i> Unlink Sibling
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @section('script')
        <script>
            @if($showButtons == 'true' && auth('staff')->check())
                 $('.studentFeeMarkPaidBtn').click(function () {
                     let feeId = $(this).data('id');
                     let amount = $(this).data('amount');
                     let remaining = $(this).data('remaining');
                     let walletBalance = parseFloat($(this).data('wallet') || 0);

                     $('#studentFeeId').val(feeId);
                     $('#studentFeeTotalFee').val(amount);
                     $('#studentFeeBalanceToPay').val(remaining);

                     let select = $('#studentFeePaymentModal select[name="payment_method"]');
                     select.find('option[value="wallet"]').remove();
                     if (walletBalance > 0) {
                         select.append(`<option value="wallet">Wallet Balance (Available: ₹${walletBalance.toFixed(2)})</option>`);
                     }

                     $('#studentFeePaymentModal input[name="paid_amount"]').val(remaining).attr('max', remaining);

                     $('#studentFeePaymentModal').modal('show');
                 });

                $('.studentFeeViewPaymentsBtn').click(function () {
                    let url = $(this).data('url');
                    $('#studentFeePaymentsTableBody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
                    $('#studentFeeTotalPaid').text('0.00');
                    $.get(url, function (res) {
                        let rows = '';
                        let total = 0;
                        if (res.payments.length === 0) {
                            rows = '<tr><td colspan="4" class="text-center text-muted">No payments found</td></tr>';
                        } else {
                            res.payments.forEach(p => {
                                let amt = parseFloat(p.paid_amount);
                                total += amt;
                                let d = new Date(p.paid_date);
                                let dateStr = d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                                let method = p.payment_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                                rows += '<tr><td>' + dateStr + '</td><td>₹ ' + amt.toFixed(2) + '</td><td>' + method + '</td><td>' + (p.notes ?? '-') + '</td></tr>';
                            });
                        }
                        $('#studentFeePaymentsTableBody').html(rows);
                        $('#studentFeeTotalPaid').text(total.toFixed(2));
                        $('#studentFeePaymentsModal').modal('show');
                    });
                });

                $('.studentFeeSendNotificationBtn').click(function (e) {
                    e.preventDefault();
                    let feeId = $(this).data('id');
                    let button = $(this);
                    if (confirm('Send notification to student?')) {
                        button.prop('disabled', true);
                        button.html('<i class="fas fa-spinner fa-spin"></i>');
                        $.ajax({
                            type: 'POST',
                            url: '{{ route("staff.fees.send-notification") }}',
                            data: { _token: '{{ csrf_token() }}', fee_id: feeId },
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
                                let errorMsg = xhr.responseJSON?.message ?? 'An error occurred';
                                alert('Error: ' + errorMsg);
                                button.prop('disabled', false);
                                button.html('<i class="fas fa-bell"></i>');
                            }
                        });
                    }
                });
            @endif

            @if(auth('staff')->check() || auth('admin')->check())
                $(document).on('show.bs.modal', '#linkExistingFamilyModal', function() {
                    var $el = $('#related_student_id');
                    if ($el.hasClass('select2-hidden-accessible')) { $el.select2('destroy'); }
                    $el.select2({
                        placeholder: 'Search student...', 
                        allowClear: true, 
                        width: '100%',
                        dropdownParent: $('#linkExistingFamilyModal'),
                        minimumInputLength: 1,
                        ajax: { 
                            url: '{{ route($routePrefix . ".students.search-relations", encrypt($student->id)) }}', 
                            dataType: 'json', 
                            delay: 250,
                            data: function(p) { return { q: p.term || '' }; },
                            processResults: function(d) { return { results: d.results }; },
                            cache: true
                        }
                    });
                });

                $(document).on('click', '.unlink-sibling-btn', function() {
                    var actionUrl = $(this).data('action');
                    var siblingName = $(this).data('name');
                    
                    $('#unlinkSiblingForm').attr('action', actionUrl);
                    $('#unlinkSiblingName').text(siblingName);
                    $('#new_contact_number').val('');
                    
                    $('#familyMembersModal').modal('hide');
                    
                    setTimeout(function() {
                        $('#unlinkSiblingModal').modal('show');
                    }, 300);
                });
            @endif
        </script>
    @endsection

@endsection