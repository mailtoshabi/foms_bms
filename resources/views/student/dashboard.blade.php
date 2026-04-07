@extends('student.layouts.master-layouts-noleft')

@section('title','Student Dashboard')

@section('content')

<div class="row">

    {{-- STUDENT PROFILE --}}
    <div class="col-md-12">
        <div class="card text-center">
            <div class="card-body">
                @if($student->photo)
                    <img src="{{ asset('storage/'.$student->photo) }}"
                        class="rounded-circle mb-2" width="80">
                @endif
                <h5>Welcome, {{ $student->name }}</h5>
                <p class="text-muted">{{ $student->contact_number }}</p>
                <span class="badge bg-success">{{ ucfirst($student->status) }}</span>
            </div>
        </div>
    </div>

    {{-- CURRENT CLASS --}}
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Latest Class</h6>
                @if($currentClass)
                    <strong>{{ $currentClass->name }}</strong> |
                    <span class="mb-0">{{ $currentClass->course->name ?? '-' }}</span>
                    <br><a href="{{ route('student.classes.show', encrypt($currentClass->id)) }}"
                        class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-sign-in-alt"></i> Enter
                    </a>
                    @if($student->class_rooms->count() > 1)
                        <button class="btn btn-sm btn-outline-secondary mt-2"
                            data-bs-toggle="modal"
                            data-bs-target="#allClassesModal">
                            <i class="fas fa-list"></i> All Classes
                        </button>
                    @endif
                @else
                    <span class="text-muted">Not Assigned</span>
                @endif
            </div>
        </div>
    </div>

    {{-- TEACHER --}}
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Latest Teacher</h6>
                @if($teacher)
                    <strong>{{ $teacher->name }}</strong>
                    {{-- <p class="text-muted">{{ $teacher->phone }}</p> --}}<br>
                    <button class="btn btn-sm btn-success mt-2"
                        data-bs-toggle="modal"
                        data-bs-target="#messageTeacherModal"
                        data-teacher-id="{{ $teacher->id }}"
                        data-teacher-name="{{ $teacher->name }}">
                        <i class="fas fa-envelope"></i> Message
                    </button>
                    @if($allTeachers->count() > 1)
                        <button class="btn btn-sm btn-outline-secondary mt-2"
                            data-bs-toggle="modal"
                            data-bs-target="#allTeachersModal">
                            <i class="fas fa-users"></i> All Teachers
                        </button>
                    @endif
                @else
                    <span class="text-muted">Not Assigned</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ATTENDANCE --}}
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Attendance</h6>
                <h3 class="text-success" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#attendanceDetailsModal">{{ $attendancePercent }}%</h3>
                <div class="progress" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#attendanceDetailsModal">
                    <div class="progress-bar bg-success"
                        style="width: {{ $attendancePercent }}%"></div>
                </div>
                {{-- <span class="badge bg-primary" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#attendanceDetailsModal"><a href="#" class="text-white">View Details</a></span> --}}
            </div>
        </div>
    </div>

    {{-- FEE DUE --}}
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Fee Due</h6>
                <h4 class="{{ $feeDue > 0 ? 'text-danger' : 'text-success' }}">
                    &#8377; {{ number_format($feeDue, 2) }}
                </h4>
                @if($feeDue > 0)
                    <span class="badge bg-danger">Pending</span>
                @else
                    <span class="badge bg-success">Paid</span>
                @endif
                <span class="badge bg-primary" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#feeDetailsModal"><a href="#" class="text-white">View Details</a></span>
            </div>
        </div>
    </div>

    {{-- UPCOMING SESSION --}}
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Upcoming Session</h6>
                @if($pendingClassHours->count() > 0)
                    @php $nextSession = $pendingClassHours->first(); @endphp
                    <strong>{{ $nextSession->classRoom->name ?? '-' }}</strong>
                    <p class="text-muted small mb-1">{{ $nextSession->created_at->format('d M Y, h:i A') }}</p>
                    @if($nextSession->google_meet_link)
                        <a href="{{ $nextSession->google_meet_link }}" target="_blank"
                            class="btn btn-sm btn-success mt-2">
                            <i class="fas fa-video"></i> Join
                        </a>
                    @endif
                    @if($pendingClassHours->count() > 1)
                        <button class="btn btn-sm btn-outline-secondary mt-2"
                            data-bs-toggle="modal"
                            data-bs-target="#allPendingSessionsModal">
                            <i class="fas fa-list"></i> More
                        </button>
                    @endif
                @else
                    <span class="text-muted">No upcoming sessions</span>
                @endif
            </div>
        </div>
    </div>

    {{-- MESSAGES --}}
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Messages</h6>
                @if($allMessages->count() > 0)
                    @php $latestMsg = $allMessages->first(); @endphp
                    <strong>{{ Str::limit($latestMsg->message, 40) }}</strong>
                    <p class="text-muted small mb-1">{{ $latestMsg->created_at->format('d M Y, h:i A') }}</p>
                    {{-- @if($allMessages->count() > 1) --}}
                        <button class="btn btn-sm btn-outline-secondary mt-2"
                            data-bs-toggle="modal"
                            data-bs-target="#allMessagesModal">
                            <i class="fas fa-list"></i> More
                        </button>
                    {{-- @endif --}}
                @else
                    <span class="text-muted">No messages yet</span>
                @endif
            </div>
        </div>
    </div>

    {{-- CLASS NOTES --}}
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Class Notes</h6>
                @if($latestNotes->count() > 0)
                    <strong>{{ ucfirst($latestNotes->first()->title) }}</strong>
                    <p class="text-muted small mb-1">{{ $latestNotes->first()->created_at->format('d M Y') }}</p>
                    <button class="btn btn-sm btn-primary mt-2"
                        data-bs-toggle="modal"
                        data-bs-target="#allClassNotesModal">
                        <i class="fas fa-list"></i> All Notes
                    </button>
                @else
                    <span class="text-muted">No notes available</span>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- FEE DETAILS MODAL --}}
<div class="modal fade" id="feeDetailsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Fee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($feeDetails->isEmpty())
                    <p class="text-muted">No fee records found.</p>
                @else
                    <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Class</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feeDetails as $fee)
                                @php
                                    $paid = $fee->paid_amount;
                                    $balance = $fee->amount - $paid;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $fee->classRoom->name ?? '-' }}</td>
                                    <td>{{ ucfirst($fee->type) }}</td>
                                    <td>&#8377; {{ number_format($fee->amount, 2) }}</td>
                                    <td class="text-success">&#8377; {{ number_format($paid, 2) }}</td>
                                    <td class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">&#8377; {{ number_format($balance, 2) }}</td>
                                    <td>{{ $fee->due_date ? \Carbon\Carbon::parse($fee->due_date)->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if($fee->status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($fee->status == 'partial')
                                            <span class="badge bg-warning">Partial</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total:</td>
                                <td>&#8377; {{ number_format($feeDetails->sum('amount'), 2) }}</td>
                                <td class="text-success">&#8377; {{ number_format($feeDetails->sum('paid_amount'), 2) }}</td>
                                <td class="text-danger">&#8377; {{ number_format($feeDetails->sum('amount') - $feeDetails->sum('paid_amount'), 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MESSAGE TEACHER MODAL --}}
<div class="modal fade" id="messageTeacherModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('student.messages.store') }}">
                @csrf
                <input type="hidden" name="teacher_id" id="messageTeacherId">
                <div class="modal-header">
                    <h5>Message <span id="messageTeacherName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required placeholder="Type your message..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ALL CLASSES MODAL --}}
@if($student->class_rooms->count() > 1)
<div class="modal fade" id="allClassesModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>My Classes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Class Name</th>
                            <th>Course</th>
                            <th>Teacher</th>
                            <th>Schedule</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($student->class_rooms->take(10) as $class)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $class->name }} <br><small class="badge bg-soft-primary text-primary">{{ ucwords($class->classType->name ?? '-') }} Class</small></td>
                                <td>{{ $class->course->name ?? '-' }}</td>
                                <td>{{ $class->teachers->pluck('name')->join(', ') ?: '-' }}</td>
                                <td>
                                    {{ implode(', ', $class->selected_days ?? []) }}
                                    <small><br>{{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('student.classes.show', encrypt($class->id)) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Enter
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('student.classes.index') }}" class="btn btn-primary">View All</a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ALL TEACHERS MODAL --}}
@if(isset($allTeachers) && $allTeachers->count() > 1)
<div class="modal fade" id="allTeachersModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>All Teachers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    @foreach($allTeachers->take(10) as $t)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $t->name }}</strong>
                                <small class="text-muted d-block">{{ $t->phone }}</small>
                            </div>
                            <button class="btn btn-sm btn-success messageTeacherBtn"
                                data-teacher-id="{{ $t->id }}"
                                data-teacher-name="{{ $t->name }}">
                                <i class="fas fa-envelope"></i> Message
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ALL PENDING SESSIONS MODAL --}}
@if($pendingClassHours->count() > 1)
<div class="modal fade" id="allPendingSessionsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>All Pending Sessions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Duration</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingClassHours->take(10) as $hour)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $hour->classRoom->name ?? '-' }}</td>
                                <td>{{ $hour->created_at->format('d M Y, h:i A') }}</td>
                                <td>{{ $hour->duration }} min</td>
                                <td>
                                    @if($hour->google_meet_link)
                                        <a href="{{ $hour->google_meet_link }}" target="_blank"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-video"></i> Join
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ALL MESSAGES MODAL --}}
@if($allMessages->count() > 1)
<div class="modal fade" id="allMessagesModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>All Messages</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Direction</th>
                            <th>Teacher</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allMessages->take(10) as $msg)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($msg->sender_type == 'App\\Models\\Student' && $msg->sender_id == $student->id)
                                        <span class="badge bg-primary">Sent</span>
                                    @else
                                        <span class="badge bg-success">Received</span>
                                    @endif
                                </td>
                                <td>
                                    @if($msg->sender_type == 'App\\Models\\Student')
                                        {{ $msg->receiver->name ?? '-' }}
                                    @else
                                        {{ $msg->sender->name ?? '-' }}
                                    @endif
                                </td>
                                <td>{{ $msg->message }}</td>
                                <td>{{ $msg->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('student.messages.index') }}" class="btn btn-primary">View All</a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ALL CLASS NOTES MODAL --}}
<div class="modal fade" id="allClassNotesModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>All Class Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($allNotes->isEmpty())
                    <p class="text-muted">No class notes found.</p>
                @else
                    <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Class</th>
                                <th>Teacher</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allNotes->take(10) as $note)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $note->title }}</td>
                                    <td>{{ $note->classRoom->name ?? '-' }}</td>
                                    <td>{{ $note->teacher->name ?? '-' }}</td>
                                    <td>{{ $note->created_at->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('student.notes.show', encrypt($note->id)) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <a href="{{ route('student.notes.index') }}" class="btn btn-primary">View All</a>
            </div>
        </div>
    </div>
</div>

{{-- ATTENDANCE DETAILS MODAL --}}
<div class="modal fade" id="attendanceDetailsModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Attendance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Total Attendance: {{ $totalAttendance }}</p>
                <p>Present: {{ $presentAttendance }}</p>
                <p>Attendance Percentage: {{ $attendancePercent }}%</p>
            </div>
        </div>
    </div>
</div>


@endsection

@section('script')
<script>
    // Set teacher info when opening message modal from dashboard
    $('#messageTeacherModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        if (button.data('teacher-id')) {
            $('#messageTeacherId').val(button.data('teacher-id'));
            $('#messageTeacherName').text(button.data('teacher-name'));
        }
    });

    // Handle message button from All Teachers modal
    $('.messageTeacherBtn').click(function() {
        var teacherId = $(this).data('teacher-id');
        var teacherName = $(this).data('teacher-name');
        $('#allTeachersModal').modal('hide');
        setTimeout(function() {
            $('#messageTeacherId').val(teacherId);
            $('#messageTeacherName').text(teacherName);
            $('#messageTeacherModal').modal('show');
        }, 500);
    });
</script>
@endsection

@section('css')
    <style>
        /* Custom styles for dashboard */
        .progress {
            /* height: .500rem !important; */
        }
    </style>
@endsection
