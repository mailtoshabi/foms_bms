@section('title', 'Teacher Details')

@section('content')
    @php
        $staff = auth('staff')->user();
        $isAdmin = auth('admin')->check();
        $isOperation = $staff && $staff->hasRoleId(utility('id_operation_dept'));
        $isAdministrator = $staff && $staff->hasRoleId(utility('id_administrator_dept'));
    @endphp
    <div class="row">

        {{-- =========================
        TEACHER PROFILE
        ========================= --}}
        <div class="col-md-4">

            <div class="card">

                <div class="card-header d-flex align-items-center">
                    <a href="javascript:window.history.back();"
                        class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <h5 class="mb-0">Teacher Profile</h5>
                </div>

                <div class="card-body text-center">

                    <img src="@if($teacher->photo){{ asset('storage/' . $teacher->photo) }}@else https://ui-avatars.com/api/?name={{ urlencode($teacher->name) }}&size=150 @endif"
                        class="rounded-circle mb-3 border shadow-sm" width="120" height="120" style="object-fit: cover;">

                    <h5>{{ $teacher->name }}</h5>

                    <p class="text-muted mb-1">
                        {{ $teacher->formatted_phone }}
                    </p>

                    <p class="text-muted mb-1">
                        {{ $teacher->email ?? '-' }}
                    </p>

                    <span class="badge bg-success">
                        {{ ucfirst($teacher->status) }}
                    </span>

                    <div class="mt-3 pt-3 border-top text-start">
                        <h6 class="text-dark fw-bold mb-2">Tutor Agreement</h6>
                        @if($teacher->agreed_rules)
                            <div class="d-flex align-items-center text-success mb-2">
                                <i class="fas fa-check-circle me-2" style="font-size: 1.1rem;"></i>
                                <span class="fw-semibold">Agreed Rules & Regulations</span>
                            </div>
                        @else
                            <div class="d-flex align-items-center text-danger mb-2">
                                <i class="fas fa-times-circle me-2" style="font-size: 1.1rem;"></i>
                                <span class="fw-semibold">Rules Not Agreed Yet</span>
                            </div>
                            <form action="{{ route('staff.teachers.update-agreement', encrypt($teacher->id)) }}" method="POST"
                                class="d-inline-block w-100 mb-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success w-100 py-1"
                                    onclick="return confirm('Mark this teacher as agreed to the Rules & Regulations?')">
                                    <i class="fas fa-check me-1"></i> Mark as Agreed
                                </button>
                            </form>
                        @endif
                        <a href="{{ asset('agreement/tutor_agreement.pdf') }}" target="_blank"
                            class="btn btn-sm btn-light border w-100 py-1">
                            <i class="fas fa-file-pdf text-danger me-1"></i> View Tutor Agreement PDF
                        </a>
                        @if($teacher->id_proof)
                            <a href="{{ asset('storage/' . $teacher->id_proof) }}" target="_blank"
                                class="btn btn-sm btn-outline-info w-100 py-1 mt-2">
                                <i class="fas fa-id-card me-1"></i> View ID Proof
                            </a>
                        @endif
                    </div>

                    @php $rank = teacherRankData($teacher->id); @endphp

                    <div class="mt-2 mb-1">
                        <span class="badge bg-{{ $rank['color'] }} fs-6 px-3 py-2">{{ $rank['label'] }}</span>
                    </div>
                    <div class="mb-1">
                        @for($s = 1; $s <= 5; $s++)
                            <span
                                style="font-size:1.2rem; color: {{ $s <= $rank['stars'] ? '#f1c40f' : '#ccc' }}">&#9733;</span>
                        @endfor
                    </div>
                    <small class="text-muted">Score: {{ $rank['score'] }}</small>

                    <hr>

                    @if($teacher->salary_cycle_day)
                        @php
                            $salaryDate = \Carbon\Carbon::create(
                                now()->year,
                                now()->month,
                                min($teacher->salary_cycle_day, now()->daysInMonth)
                            );
                            $creditDate = $salaryDate->copy()->addDays(10);
                        @endphp
                        <p><strong>Salary Date:</strong></p>
                        <p class="text-muted">
                            {{ $salaryDate->format('d M Y') }}
                        </p>
                        <p><strong>Credit Date:</strong></p>
                        <p class="text-muted">
                            {{ $creditDate->format('d M Y') }}
                        </p>
                    @endif

                    @if($teacher->qualification)
                        <p><strong>Qualification:</strong></p>
                        <p class="text-muted">
                            {{ $teacher->qualification }}
                        </p>
                    @endif

                    @if($teacher->experience !== null && $teacher->experience !== '')
                        <p><strong>Experience:</strong></p>
                        <p class="text-muted">
                            {{ $teacher->experience }}
                        </p>
                    @endif

                    @if($teacher->address)
                        <p><strong>Address:</strong></p>
                        <p class="text-muted">
                            {{ $teacher->address }}
                        </p>
                    @endif

                </div>

            </div>

        </div>


        {{-- =========================
        SALARY PAYMENT HISTORY
        ========================= --}}
        <div class="col-md-8">

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">Latest Salary Details</h5>

                    {{-- <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#salaryModal">

                        <i class="fas fa-plus"></i> Add Salary

                    </button> --}}

                    {{-- <a class="btn btn-sm btn-primary"
                        href="{{ route('staff.process.teacher.salary',encrypt($teacher->id)) }}"><i class="fas fa-plus"></i>
                        Add Salary</a> --}}

                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">

                            <thead>
                                <tr>
                                    <th>Due Date</th>
                                    <th>Total Amount</th>
                                    <th>Method</th>
                                    <th>status</th>
                                    <th width="120">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($teacher->salaries as $salary)

                                    <tr>

                                        <td>{{ $teacher->SalaryCreditDate }}</td>

                                        <td>₹ {{ number_format($salary->total_amount, 2) }}</td>

                                        <td>{{ ucfirst($salary->payment_method ?? '-') }}</td>

                                        <td>{{ $salary->status }}</td>

                                        <td>
                                            @if($isAdmin || $isOperation || $isAdministrator)
                                                <button
                                                    class="btn btn-sm  editSalary {{ $salary->status == 'paid' ? 'disabled' : '' }}"
                                                    title="Make Payment" data-id="{{ $salary->id }}"
                                                    data-total_amount="{{ $salary->total_amount }}"
                                                    data-method="{{ $salary->payment_method }}"
                                                    data-date="{{ optional($salary->payment_date)->format('d M Y') }}"
                                                    data-notes="{{ $salary->notes }}">

                                                    <i class="fas fa-money-bill-wave text-success"></i>

                                                </button>

                                                <button class="btn btn-sm btn-primary viewSalaryBtn"
                                                    style="transition: opacity 0.15s ease-in-out;"
                                                    onmouseover="this.style.opacity='0.7';" onmouseout="this.style.opacity='1';"
                                                    title="View Details"
                                                    data-cycle_start="{{ optional($salary->cycle_start)->format('d M Y') ?? '-' }}"
                                                    data-cycle_end="{{ optional($salary->cycle_end)->format('d M Y') ?? '-' }}"
                                                    data-total_hours="{{ $salary->total_hours ?? '-' }}"
                                                    data-total_amount="₹ {{ number_format($salary->total_amount, 2) }}"
                                                    data-method="{{ ucfirst($salary->payment_method ?? '-') }}"
                                                    data-date="{{ optional($salary->payment_date)->format('d M Y') ?? '-' }}"
                                                    data-reference_number="{{ $salary->reference_number ?? '-' }}"
                                                    data-notes="{{ $salary->notes ?? '-' }}"
                                                    data-status="{{ ucfirst($salary->status) }}"
                                                    data-credit_date="{{ optional($salary->credit_date)->format('d M Y') ?? '-' }}">

                                                    <i class="fas fa-eye text-white"></i>

                                                </button>
                                            @endif
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No salary payments yet
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

        </div>

        {{-- =========================
        CLASS LIST
        ========================= --}}
        <div class="col-md-12">

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">Assigned Classes</h5>
                    @if($showButtons == 'true')
                        @if($isAdmin || $isOperation || $isAdministrator)
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignClassModal">

                                <i class="fas fa-plus"></i> Assign Classes

                            </button>
                        @endif
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
                                    <th>Hourly Wage</th>
                                    @if($isAdmin) <!--  || $isOperation || $isAdministrator -->
                                        <th width="100">Action</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($teacher->classRooms as $class)

                                    <tr>

                                        <td>{{ $class->course->name ?? '-' }}</td>

                                        <td>
                                            <a
                                                href="{{ $isAdmin ? route('admin.class_rooms.show', encrypt($class->id)) : route('staff.class_rooms.show', encrypt($class->id)) }}">
                                                {{ $class->name }}
                                            </a>
                                        </td>

                                        <td>{{ ucfirst($class->classType->name ?? '-') }}</td>

                                        <td>
                                            @if($class->selected_days)

                                                <small>

                                                    {{ implode(', ', $class->selected_days ?? []) }}

                                                    <br>

                                                    {{ $class->time_slot ? \Carbon\Carbon::parse($class->time_slot)->format('h:i A') : '' }}

                                                </small>

                                            @endif
                                        </td>

                                        <td>

                                            <form method="POST"
                                                action="{{ $isAdmin ? route('admin.teachers.update.wage') : route('staff.teachers.update.wage') }}"
                                                class="d-flex">

                                                @csrf
                                                @method('PUT')

                                                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                                                <input type="hidden" name="class_room_id" value="{{ $class->id }}">

                                                <input type="number" step="0.01" name="hourly_wage"
                                                    value="{{ $class->pivot->hourly_wage }}"
                                                    class="form-control form-control-sm me-2" style="width:120px">
                                                @if($isAdmin || $isOperation || $isAdministrator)
                                                    <button class="btn btn-sm btn-success" type="submit"
                                                        onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                @endif

                                            </form>

                                        </td>
                                        @if($isAdmin) <!-- || $isOperation || $isAdministrator -->
                                            <td>

                                                <form method="POST"
                                                    action="{{ $isAdmin ? route('admin.teachers.classrooms.destroy', [$teacher->id, $class->id]) : route('staff.teachers.classrooms.destroy', [$teacher->id, $class->id]) }}"
                                                    onsubmit="let reason = prompt('Please enter the reason for removing this teacher:'); if (reason === null) return false; if (reason.trim() === '') { alert('Reason is required.'); return false; } this.querySelector('.removal-reason').value = reason; return confirm('Are you sure you want to remove this teacher?\n\nWarning:\nPENDING class sessions assigned to this teacher in this classroom will be DELETED.');">

                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="reason" class="removal-reason">

                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times"></i>
                                                    </button>

                                                </form>

                                            </td>
                                        @endif
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No classes assigned
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

        </div>

        {{-- =========================
        REMOVED CLASSES HISTORY
        ========================= --}}
        @php
            $removalLogs = \App\Models\TeacherRemovalLog::with(['classroom.course', 'classroom.classType'])
                ->where('teacher_id', $teacher->id)
                ->latest()
                ->get();
        @endphp

        <div class="col-md-12 mt-4">

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Removed Classes History</h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle table-nowrap mb-0">

                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Class</th>
                                    <th>Type</th>
                                    <th>Removed Date & Time</th>
                                    <th>Removed By</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($removalLogs as $log)

                                    <tr>

                                        <td>{{ $log->classroom->course->name ?? '-' }}</td>

                                        <td>
                                            @if($log->classroom)
                                                @if($log->classroom->trashed())
                                                    {{ $log->classroom->name }} <span class="badge bg-danger">Deleted Class</span>
                                                @else
                                                    <a href="{{ $isAdmin ? route('admin.class_rooms.show', encrypt($log->classroom->id)) : route('staff.class_rooms.show', encrypt($log->classroom->id)) }}">
                                                        {{ $log->classroom->name }}
                                                    </a>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>{{ ucfirst($log->classroom->classType->name ?? '-') }}</td>

                                        <td>
                                            <span class="fw-bold">{{ $log->date ? $log->date->format('d M Y') : '-' }}</span>
                                            <br>
                                            <small class="text-muted">{{ $log->date ? $log->date->format('h:i A') : '-' }}</small>
                                        </td>

                                        <td>{{ $log->remover_name }}</td>
                                        
                                        <td>{{ $log->reason ?? '-' }}</td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No removed classes recorded
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

        </div>



        {{-- =========================
        CLASS NOTES CREATED
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
        WHATSAPP CREDENTIALS
        ========================= --}}
        <div class="col-md-12">

            <div class="card">

                <div class="card-header">
                    <h5>Send Login Credentials</h5>
                </div>

                <div class="card-body">

                    <a href="{{ teacherWhatsappMessage($teacher, $teacher->phone) }}" class="btn btn-success">

                        <i class="fab fa-whatsapp"></i> Send Credentials

                    </a>

                </div>

            </div>

        </div>


    </div>


    {{-- Modal for assigne class to teacher --}}
    <div class="modal fade" id="assignClassModal">

        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST"
                    action="{{ $isAdmin ? route('admin.teachers.assign.classrooms') : route('staff.teachers.assign.classrooms') }}">

                    @csrf

                    <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title">Assign Class</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        {{-- Class Selection --}}
                        <div class="mb-3">

                            <label class="form-label">Class Room</label>

                            <select name="class_room_id" class="form-control select2-class-ajax"
                                data-ajax-url="{{ $isAdmin ? route('admin.students.active-classes.search') : route('staff.students.active-classes.search') }}"
                                required>

                                <option value="">Search active class...</option>

                            </select>

                        </div>

                        {{-- Hourly Wage --}}
                        <div class="mb-3">

                            <label class="form-label">Wage Per Hour (₹)</label>

                            <input type="number" step="0.01" name="hourly_wage" class="form-control"
                                placeholder="Enter hourly wage">

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button class="btn btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Assigning...'; this.form.submit();">

                            Assign Class

                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>


    {{-- salary modal --}}

    <div class="modal fade" id="salaryModal">

        <div class="modal-dialog">

            <div class="modal-content">

                <form method="POST" id="salaryForm" action="">
                    {{-- {{ route('staff.teacher-salaries.store',$teacher->id) }} --}}
                    <input type="hidden" name="status">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">

                        <h5 class="salary_modal modal-title">Add Salary Payment</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">Amount</label>

                            <input readonly type="number" name="total_amount" class="form-control" required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">Payment Date</label>

                            <input type="date" name="payment_date" class="form-control" required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">Payment Method</label>

                            <select name="payment_method" class="form-control">

                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="upi">UPI</option>
                                <option value="bank">Bank Transfer</option>
                            </select>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">Notes</label>

                            <textarea name="notes" class="form-control" rows="2"></textarea>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button class="btn btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">

                            Save Payment

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- View Salary Details Modal --}}

    <div class="modal fade" id="viewSalaryModal" tabindex="-1" aria-labelledby="viewSalaryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSalaryModalLabel">Salary Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="w-50">Cycle Start</th>
                                <td id="val_cycle_start"></td>
                            </tr>
                            <tr>
                                <th>Cycle End</th>
                                <td id="val_cycle_end"></td>
                            </tr>
                            <tr>
                                <th>Total Hours</th>
                                <td id="val_total_hours"></td>
                            </tr>
                            <tr>
                                <th>Total Amount</th>
                                <td id="val_total_amount" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th>Due/Credit Date</th>
                                <td id="val_credit_date"></td>
                            </tr>
                            <tr>
                                <th>Payment Date</th>
                                <td id="val_payment_date"></td>
                            </tr>
                            <tr>
                                <th>Payment Method</th>
                                <td id="val_payment_method"></td>
                            </tr>
                            <tr>
                                <th>Reference Number</th>
                                <td id="val_reference_number"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="val_status"></td>
                            </tr>
                            <tr>
                                <th>Notes</th>
                                <td id="val_notes"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Teacher attendance modal --}}

    <div class="modal fade" id="attendanceModal">

        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="">
                    {{-- {{ route('staff.teacher-attendance.store') }} --}}

                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Mark Teacher Attendance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

                        {{-- Class --}}
                        <div class="mb-3">

                            <label class="form-label">Class Room</label>

                            <select name="class_room_id" class="form-control select2" required>

                                <option value="">Select Class</option>

                                @foreach($teacher->classRooms as $class)

                                    <option value="{{ $class->id }}">
                                        {{ $class->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Attendance Date --}}
                        <div class="mb-3">

                            <label class="form-label">Attendance Date</label>

                            <input type="date" name="attendance_date" class="form-control" required>

                        </div>


                        {{-- Session Link --}}
                        <div class="mb-3">

                            <label class="form-label">Session Link</label>

                            <input type="url" name="google_meet_link" class="form-control"
                                placeholder="https://meet.google.com/...">

                            <small class="text-muted">
                                Optional â€“ for online classes
                            </small>

                        </div>


                        {{-- Attendance --}}
                        <div class="mb-3">

                            <label class="form-label">Attendance</label>

                            <select name="is_present" class="form-control">

                                <option value="1">Present</option>
                                <option value="0">Absent</option>

                            </select>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button class="btn btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                            Save Attendance
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>

@endsection



@section('script')

    {{--
    <script>

        $('.select2').select2({
            placeholder: 'Select Class Rooms',
            width: '100%'
        });

    </script> --}}

    <script>

        // $('.select2').select2({
        // dropdownParent: $('#assignClassModal'),
        // width:'100%'
        // });

    </script>

    <script>

        $('.editSalary').click(function () {

            let id = $(this).data('id');
            let total_amount = $(this).data('total_amount');
            let method = $(this).data('method');
            let date = $(this).data('date');
            let notes = $(this).data('notes');

            let actionUrl = "{{ $isAdmin ? route('admin.teacher-salaries.update', ':id') : route('staff.teacher-salaries.update', ':id') }}";
            $('#salaryForm').attr('action', actionUrl.replace(':id', id));

            $('input[name=total_amount]').val(total_amount);
            $('input[name=status]').val('paid');
            $('input[name=payment_date]').val(date);
            $('select[name=payment_method]').val(method);
            $('textarea[name=notes]').val(notes);

            $('.salary_modal.modal-title').text('Pay Salary');

            $('#salaryModal').modal('show');

        });

        $(document).on('click', '.viewSalaryBtn', function () {
            let cycleStart = $(this).data('cycle_start') || '-';
            let cycleEnd = $(this).data('cycle_end') || '-';
            let totalHours = $(this).data('total_hours') || '-';
            let totalAmount = $(this).data('total_amount') || '-';
            let creditDate = $(this).data('credit_date') || '-';
            let date = $(this).data('date') || '-';
            let method = $(this).data('method') || '-';
            let refNum = $(this).data('reference_number') || '-';
            let status = $(this).data('status') || '';
            let notes = $(this).data('notes') || '-';

            $('#val_cycle_start').text(cycleStart);
            $('#val_cycle_end').text(cycleEnd);
            $('#val_total_hours').text(totalHours);
            $('#val_total_amount').text(totalAmount);
            $('#val_credit_date').text(creditDate);
            $('#val_payment_date').text(date);
            $('#val_payment_method').text(method);
            $('#val_reference_number').text(refNum);

            // Apply badge color to status
            let badgeClass = 'badge bg-secondary';
            let statusLower = status.toString().toLowerCase();
            if (statusLower === 'paid') {
                badgeClass = 'badge bg-success';
            } else if (statusLower === 'unpaid') {
                badgeClass = 'badge bg-danger';
            } else if (statusLower === 'pending') {
                badgeClass = 'badge bg-warning text-dark';
            }
            if (status) {
                $('#val_status').html('<span class="' + badgeClass + '">' + status + '</span>');
            } else {
                $('#val_status').text('-');
            }

            $('#val_notes').text(notes);

            $('#viewSalaryModal').modal('show');
        });

    </script>

@endsection