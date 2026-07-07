@extends('student.layouts.master-layouts-noleft')

@section('title', 'Student Dashboard')

@section('content')
    <div class="student-dashboard container-fluid px-0">

        {{-- WELCOME BANNER ROW --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card profile-banner shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                            @if($student->photo)
                                <div class="avatar-container">
                                    <div class="avatar-ring">
                                        <img src="{{ asset('storage/' . $student->photo) }}" class="avatar-img shadow-lg"
                                            width="90" height="90" style="object-fit: cover;">
                                    </div>
                                </div>
                            @else
                                <div class="avatar-container">
                                    <div class="avatar-ring">
                                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle shadow-lg text-primary"
                                            style="width: 90px; height: 90px; font-size: 2rem; font-weight: 700;">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="text-center text-md-start">
                                <span
                                    class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-2 mb-2 small fw-bold"
                                    style="backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                    <i class="fas fa-graduation-cap me-1 text-info"></i> Student Portal
                                </span>
                                <h2 class="mb-1 text-white fw-bold">Welcome back, {{ $student->name }}!</h2>
                                <p class="mb-2 text-white text-opacity-80"><i class="fas fa-phone me-1"></i>
                                    {{ $student->formatted_contact_number }}</p>
                                <span class="badge rounded-pill px-3 py-1.5 mt-1 fw-bold"
                                    style="background-color: rgba(16, 185, 129, 0.2) !important; color: #10b981 !important; border: 1px solid rgba(16, 185, 129, 0.3) !important; display: inline-flex; align-items: center; gap: 4px;">
                                    <span class="position-relative d-flex" style="width: 6px; height: 6px;">
                                        <span
                                            class="animate-ping position-absolute inline-flex h-100 w-100 rounded-circle bg-success opacity-75"></span>
                                        <span class="relative inline-flex rounded-circle h-100 w-100 bg-success"></span>
                                    </span>
                                    {{ ucfirst($student->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- HOLIDAYS ALERT/POPUP --}}
        @if($holidays->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm"
                        style="border-left: 4px solid #ffc107 !important; background-color: #fffdf0; border-radius: 12px;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; background-color: rgba(255, 193, 7, 0.15);">
                                        <i class="fas fa-calendar-alt text-warning" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1" style="color: #856404;">Upcoming Alerts</h6>
                                        <div class="text-muted small">
                                            @foreach($holidays as $holiday)
                                                <span class="me-3 d-inline-block">
                                                    <i class="fas fa-bullhorn text-warning me-1"></i>
                                                    <strong class="text-dark">{{ $holiday->title }}</strong>:
                                                    <span
                                                        class="text-primary fw-semibold">{{ $holiday->date->format('d M Y') }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-warning text-dark border-warning py-1 px-3 rounded-pill"
                                    data-bs-toggle="modal" data-bs-target="#holidaysAnnouncementModal">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Holidays Announcement Modal -->
            <div class="modal fade" id="holidaysAnnouncementModal" tabindex="-1"
                aria-labelledby="holidaysAnnouncementModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                        <div class="modal-header bg-warning text-dark border-0 py-3">
                            <h5 class="modal-title fw-bold" id="holidaysAnnouncementModalLabel">
                                <i class="fas fa-bullhorn me-2"></i>Announcement!
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            @foreach($holidays as $holiday)
                                <div class="mb-4 pb-3 {{ !$loop->last ? 'border-bottom border-light' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="fw-bold text-dark mb-0">{{ $holiday->title }}</h5>
                                        <span class="badge bg-soft-info text-info font-size-12 px-2.5 py-1.5 rounded-pill"
                                            style="background-color: rgba(43, 154, 233, 0.1);">
                                            <i class="far fa-calendar-alt me-1"></i> {{ $holiday->date->format('d M Y') }}
                                        </span>
                                    </div>
                                    <p class="text-muted mb-0" style="white-space: pre-line;">
                                        {{ $holiday->description ?: 'No additional details provided.' }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer border-0 bg-light p-3">
                            <button type="button" class="btn btn-secondary rounded-pill px-4"
                                data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- QUICK NAVIGATION GRID --}}
        <div class="row g-4 mb-4">

            {{-- UPCOMING SESSION --}}
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="icon-wrap icon-wrap-warning">
                                <i class="fas fa-video"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Live Session</h5>
                            @if($pendingClassHours->count() > 0)
                                @php $nextSession = $pendingClassHours->first(); @endphp
                                <div class="bg-light p-3 rounded-12 mb-3">
                                    <h6 class="mb-1 fw-bold text-dark">{{ $nextSession->classRoom->name ?? '-' }}</h6>
                                    <p class="mb-0 text-muted small"><i class="far fa-clock me-1"></i>
                                        {{ $nextSession->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                            @else
                                <p class="text-muted small mb-3">There are no upcoming live class sessions scheduled.</p>
                            @endif
                        </div>
                        <div>
                            @if($pendingClassHours->count() > 0)
                                <div class="d-flex gap-2">
                                    @if($nextSession->google_meet_link)
                                        @if(\Carbon\Carbon::parse($nextSession->link_updated_at)->isToday())
                                            <a href="{{ $nextSession->google_meet_link }}" target="_blank"
                                                class="btn btn-warning text-dark rounded-pill py-2 px-3 fw-bold flex-grow-1"
                                                onclick="fetch('{{ route('student.classes.join', encrypt($nextSession->id)) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })">
                                                <i class="fas fa-video me-1"></i> Join Meet
                                            </a>
                                        @else
                                            <button class="btn btn-light rounded-pill py-2 px-3 fw-bold flex-grow-1 text-muted" disabled
                                                title="Wait for teacher to update the class link.">
                                                <i class="fas fa-video me-1"></i> Join (Wait for Link)
                                            </button>
                                        @endif
                                    @endif
                                    @if($pendingClassHours->count() > 1)
                                        <button class="btn btn-outline-secondary rounded-pill py-2 px-3" data-bs-toggle="modal"
                                            data-bs-target="#allPendingSessionsModal" title="All Pending Sessions">
                                            <i class="fas fa-list"></i>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- CURRENT CLASS --}}
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="icon-wrap icon-wrap-primary">
                                <i class="fas fa-laptop-house"></i>
                            </div>
                            <h5 class="fw-bold mb-2">My Active Class</h5>
                            @if($currentClass)
                                <div class="bg-light p-3 rounded-12 mb-3">
                                    <h6 class="mb-1 fw-bold text-dark">{{ $currentClass->name }}</h6>
                                    <p class="mb-0 text-muted small"><i class="fas fa-bookmark me-1"></i>
                                        {{ $currentClass->course->name ?? '-' }}</p>
                                </div>
                            @else
                                <p class="text-muted small mb-3">No classroom is currently assigned to your account.</p>
                            @endif
                        </div>
                        <div>
                            @if($currentClass)
                                <div class="d-flex gap-2">
                                    <a href="{{ route('student.classes.show', encrypt($currentClass->id)) }}"
                                        class="btn btn-primary rounded-pill py-2 px-3 fw-semibold flex-grow-1">
                                        <i class="fas fa-sign-in-alt me-1"></i> Enter Class
                                    </a>
                                    @if($student->class_rooms->count() > 1)
                                        <button class="btn btn-outline-secondary rounded-pill py-2 px-3" data-bs-toggle="modal"
                                            data-bs-target="#allClassesModal" title="All Classes">
                                            <i class="fas fa-list"></i>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- TEACHER --}}
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="icon-wrap icon-wrap-info">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <h5 class="fw-bold mb-2">My Teacher</h5>
                            @if($teacher)
                                <div class="bg-light p-3 rounded-12 mb-3">
                                    <h6 class="mb-1 fw-bold text-dark">{{ $teacher->name }}</h6>
                                    <p class="mb-0 text-muted small"><i class="fas fa-chalkboard-teacher me-1"></i> Assigned
                                        Instructor</p>
                                </div>
                            @else
                                <p class="text-muted small mb-3">No teacher is currently assigned to your class.</p>
                            @endif
                        </div>
                        <div>
                            @if($teacher)
                                <div class="d-flex gap-2">
                                    <button class="btn btn-info text-white rounded-pill py-2 px-3 fw-semibold flex-grow-1"
                                        data-bs-toggle="modal" data-bs-target="#messageTeacherModal"
                                        data-teacher-id="{{ $teacher->id }}" data-teacher-name="{{ $teacher->name }}">
                                        <i class="fas fa-paper-plane me-1"></i> Send Message
                                    </button>
                                    @if($allTeachers->count() > 1)
                                        <button class="btn btn-outline-secondary rounded-pill py-2 px-3" data-bs-toggle="modal"
                                            data-bs-target="#allTeachersModal" title="All Teachers">
                                            <i class="fas fa-users"></i>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- MESSAGES --}}
            <div class="col-md-6 col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 text-dark">Recent Messages</h5>
                                <div class="icon-wrap icon-wrap-primary mb-0">
                                    <i class="fas fa-comments"></i>
                                </div>
                            </div>
                            @if($allMessages->count() > 0)
                                @php $latestMsg = $allMessages->first(); @endphp
                                <div class="border-start border-primary border-4 ps-3 py-1 mb-3">
                                    <p class="text-dark mb-1 font-italic">"{{ Str::limit($latestMsg->message, 80) }}"</p>
                                    <span class="text-muted small"><i class="far fa-clock me-1"></i>
                                        {{ $latestMsg->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                            @else
                                <p class="text-muted small mb-3">No inbox messages received yet.</p>
                            @endif
                        </div>
                        <div>
                            @if($allMessages->count() > 0)
                                <button class="btn btn-outline-primary rounded-pill py-2 px-3 fw-semibold w-100"
                                    data-bs-toggle="modal" data-bs-target="#allMessagesModal">
                                    <i class="fas fa-envelope-open-text me-1"></i> View All Messages
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- CLASS NOTES --}}
            <div class="col-md-6 col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 text-dark">Latest Class Notes</h5>
                                <div class="icon-wrap icon-wrap-info mb-0">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                            </div>
                            @if($latestNotes->count() > 0)
                                <div class="border-start border-info border-4 ps-3 py-1 mb-3">
                                    <h6 class="text-dark fw-bold mb-1">{{ ucfirst($latestNotes->first()->title) }}</h6>
                                    <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i> Published:
                                        {{ $latestNotes->first()->created_at->format('d M Y') }}</span>
                                </div>
                            @else
                                <p class="text-muted small mb-3">No study materials or notes available yet.</p>
                            @endif
                        </div>
                        <div>
                            @if($latestNotes->count() > 0)
                                <button class="btn btn-outline-info rounded-pill py-2 px-3 fw-semibold w-100"
                                    data-bs-toggle="modal" data-bs-target="#allClassNotesModal">
                                    <i class="fas fa-book-reader me-1"></i> Browse Class Notes
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN METRICS ROW (Attendance & Fee Due side-by-side) --}}
        <div class="row g-4 mb-4">
            {{-- ATTENDANCE --}}
            <div class="col-md-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-uppercase text-muted fw-bold mb-0 small tracking-wider">Overall Attendance
                                </h6>
                                <div class="icon-wrap icon-wrap-success mb-0">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-baseline mb-3">
                                <span class="metric-value text-success glow-text-success cursor-pointer"
                                    data-bs-toggle="modal" data-bs-target="#attendanceDetailsModal">
                                    {{ $attendancePercent }}%
                                </span>
                            </div>
                            <div class="progress-container cursor-pointer mb-2" data-bs-toggle="modal"
                                data-bs-target="#attendanceDetailsModal">
                                <div class="progress-bar-premium" style="width: {{ $attendancePercent }}%"></div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-light border w-100 text-secondary rounded-pill py-2 fw-semibold"
                                data-bs-toggle="modal" data-bs-target="#attendanceDetailsModal">
                                <i class="fas fa-chart-line me-1"></i> View Attendance Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FEE DUE --}}
            <div class="col-md-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-uppercase text-muted fw-bold mb-0 small tracking-wider">Fee Outstanding</h6>
                                <div class="icon-wrap {{ $feeDue > 0 ? 'icon-wrap-danger' : 'icon-wrap-success' }} mb-0">
                                    <i class="fas fa-wallet"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-baseline mb-3">
                                <span
                                    class="metric-value {{ $feeDue > 0 ? 'text-danger glow-text-danger' : 'text-success glow-text-success' }}">
                                    ₹{{ number_format($feeDue, 2) }}
                                </span>
                            </div>
                            <div class="mb-2 d-flex flex-wrap gap-2">
                                @if($feeDue > 0)
                                    <span class="badge badge-premium badge-premium-danger px-3 py-2 fw-bold"><i
                                            class="fas fa-exclamation-circle me-1"></i> Payment Pending</span>
                                @else
                                    <span class="badge badge-premium badge-premium-success px-3 py-2 fw-bold"><i
                                            class="fas fa-check-circle me-1"></i> Fully Paid</span>
                                @endif
                                @if(isset($student->wallet_balance) && $student->wallet_balance > 0)
                                    <span class="badge badge-premium badge-premium-success px-3 py-2 fw-bold"
                                        style="background-color: rgba(16, 185, 129, 0.15) !important; color: #10b981 !important; border: 1px solid rgba(16, 185, 129, 0.3) !important;"><i
                                            class="fas fa-plus-circle me-1"></i> Advance Wallet:
                                        ₹{{ number_format($student->wallet_balance, 2) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-light border w-100 text-secondary rounded-pill py-2 fw-semibold"
                                data-bs-toggle="modal" data-bs-target="#feeDetailsModal">
                                <i class="fas fa-receipt me-1"></i> View Ledger & Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FEE DETAILS MODAL --}}
    <div class="modal fade" id="feeDetailsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="m-0">Fee Ledger Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($feeDetails->isEmpty())
                        <p class="text-muted text-center py-4">No fee records found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
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
                                            <td class="fw-bold">{{ $fee->classRoom->name ?? '-' }}</td>
                                            <td><span
                                                    class="badge bg-light text-dark rounded-pill px-2.5 py-1">{{ ucfirst($fee->type) }}</span>
                                            </td>
                                            <td>₹{{ number_format($fee->amount, 2) }}</td>
                                            <td class="text-success fw-semibold">₹{{ number_format($paid, 2) }}</td>
                                            <td class="{{ $balance > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                                ₹{{ number_format($balance, 2) }}</td>
                                            <td>{{ $fee->due_date ? \Carbon\Carbon::parse($fee->due_date)->format('d M Y') : '-' }}
                                            </td>
                                            <td>
                                                @if($fee->status == 'paid')
                                                    <span class="badge badge-premium badge-premium-success px-2.5 py-1">Paid</span>
                                                @elseif($fee->status == 'partial')
                                                    <span
                                                        class="badge bg-soft-warning text-warning border-warning border rounded-pill px-2.5 py-1">Partial</span>
                                                @else
                                                    <span class="badge badge-premium badge-premium-danger px-2.5 py-1">Unpaid</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold bg-light">
                                        <td colspan="3" class="text-end">Total:</td>
                                        <td>₹{{ number_format($feeDetails->sum('amount'), 2) }}</td>
                                        <td class="text-success">₹{{ number_format($feeDetails->sum('paid_amount'), 2) }}</td>
                                        <td class="text-danger">
                                            ₹{{ number_format($feeDetails->sum('amount') - $feeDetails->sum('paid_amount'), 2) }}
                                        </td>
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
                        <h5 class="m-0">Message <span id="messageTeacherName"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Message Content</label>
                            <textarea name="message" class="form-control" rows="4" required style="border-radius: 12px;"
                                placeholder="Type your message to the instructor here..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="fas fa-paper-plane me-1"></i> Send Message
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
                        <h5 class="m-0">All Assigned Classes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
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
                                            <td class="fw-bold">
                                                {{ $class->name }} <br>
                                                <small
                                                    class="badge badge-premium badge-premium-primary py-0.5 px-2 mt-1">{{ ucwords($class->classType->name ?? '-') }}</small>
                                            </td>
                                            <td>{{ $class->course->name ?? '-' }}</td>
                                            <td>{{ $class->teachers->pluck('name')->join(', ') ?: '-' }}</td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ implode(', ', $class->selected_days ?? []) }}
                                                </div>
                                                <small class="text-muted"><i class="far fa-clock me-1"></i>
                                                    {{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('student.classes.show', encrypt($class->id)) }}"
                                                    class="btn btn-sm btn-primary rounded-pill px-3">
                                                    <i class="fas fa-sign-in-alt me-1"></i> Enter
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('student.classes.index') }}" class="btn btn-primary rounded-pill px-4">View All
                            Classes</a>
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
                        <h5 class="m-0">Instructors List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group list-group-flush">
                            @foreach($allTeachers->take(10) as $t)
                                <div
                                    class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-light">
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $t->name }}</h6>
                                        <small class="text-muted d-block"><i class="fas fa-phone-alt me-1"></i>
                                            {{ $t->formatted_phone }}</small>
                                    </div>
                                    <button class="btn btn-sm btn-success messageTeacherBtn rounded-pill px-3"
                                        data-teacher-id="{{ $t->id }}" data-teacher-name="{{ $t->name }}">
                                        <i class="fas fa-envelope me-1"></i> Message
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
                        <h5 class="m-0">All Pending Sessions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
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
                                            <td class="fw-bold">{{ $hour->classRoom->name ?? '-' }}</td>
                                            <td>{{ $hour->link_updated_at->format('d M Y, h:i A') }}</td>
                                            <td><span
                                                    class="badge bg-light text-dark rounded-pill px-2.5 py-1">{{ $hour->duration }}
                                                    min</span></td>
                                            <td>
                                                @if($hour->google_meet_link)
                                                    @if(\Carbon\Carbon::parse($hour->link_updated_at)->isToday())
                                                        <a href="{{ $hour->google_meet_link }}" target="_blank"
                                                            class="btn btn-sm btn-success rounded-pill px-3"
                                                            onclick="fetch('{{ route('student.classes.join', encrypt($hour->id)) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })">
                                                            <i class="fas fa-video me-1"></i> Join Class
                                                        </a>
                                                    @else
                                                        <button class="btn btn-sm btn-light rounded-pill px-3" disabled
                                                            title="Wait for teacher to update the class link.">
                                                            <i class="fas fa-video text-muted"></i> Join (Wait for Link)
                                                        </button>
                                                    @endif
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
                        <h5 class="m-0">Messages Thread</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
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
                                                    <span class="badge badge-premium badge-premium-primary px-2 py-1">Sent</span>
                                                @else
                                                    <span class="badge badge-premium badge-premium-success px-2 py-1">Received</span>
                                                @endif
                                            </td>
                                            <td class="fw-bold">
                                                @if($msg->sender_type == 'App\\Models\\Student')
                                                    {{ $msg->receiver->name ?? '-' }}
                                                @else
                                                    {{ $msg->sender->name ?? '-' }}
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-wrap" style="max-width: 300px;">{{ $msg->message }}</div>
                                            </td>
                                            <td><small class="text-muted">{{ $msg->created_at->format('d M Y, h:i A') }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('student.messages.index') }}" class="btn btn-primary rounded-pill px-4">View Full
                            Inbox</a>
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
                    <h5 class="m-0">Study Material & Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($allNotes->isEmpty())
                        <p class="text-muted text-center py-4">No class notes found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
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
                                            <td class="fw-bold">{{ $note->title }}</td>
                                            <td>{{ $note->classRoom->name ?? '-' }}</td>
                                            <td>{{ $note->teacher->name ?? '-' }}</td>
                                            <td>{{ $note->created_at->format('d M Y') }}</td>
                                            <td>
                                                <a href="{{ route('student.notes.show', encrypt($note->id)) }}"
                                                    class="btn btn-sm btn-info text-white rounded-pill px-3">
                                                    <i class="fas fa-eye me-1"></i> View Note
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
                    <a href="{{ route('student.notes.index') }}" class="btn btn-primary rounded-pill px-4">Browse All
                        Materials</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ATTENDANCE DETAILS MODAL --}}
    <div class="modal fade" id="attendanceDetailsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="m-0">Attendance Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row text-center g-3 mb-3">
                        <div class="col-6">
                            <div class="bg-light p-3 rounded-12">
                                <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.75rem;">Total
                                    Sessions</small>
                                <span class="fs-4 fw-bold text-dark">{{ $totalAttendance }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-3 rounded-12">
                                <small class="text-muted d-block uppercase fw-bold"
                                    style="font-size: 0.75rem;">Attended</small>
                                <span class="fs-4 fw-bold text-success">{{ $presentAttendance }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-center p-4 rounded-12 border-0"
                        style="background: rgba(16, 185, 129, 0.08) !important; border: 1px solid rgba(16, 185, 129, 0.15) !important;">
                        <small class="text-success d-block fw-bold mb-1 uppercase"
                            style="letter-spacing: 0.05em; font-size: 0.72rem; text-transform: uppercase;">Your Attendance
                            Rate</small>
                        <span class="display-5 fw-extrabold text-success d-block"
                            style="font-weight: 800 !important; letter-spacing: -0.03em;">{{ $attendancePercent }}%</span>
                        <p class="text-success text-opacity-75 small mt-2 mb-0 fw-medium">Maintain at least 75% attendance
                            for optimal learning outcomes.</p>
                    </div>
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
        $('.messageTeacherBtn').click(function () {
            var teacherId = $(this).data('teacher-id');
            var teacherName = $(this).data('teacher-name');
            $('#allTeachersModal').modal('hide');
            setTimeout(function () {
                $('#messageTeacherId').val(teacherId);
                $('#messageTeacherName').text(teacherName);
                $('#messageTeacherModal').modal('show');
            }, 500);
        });
        // Auto open holidays announcement modal on load if present
        $(document).ready(function () {
            if ($('#holidaysAnnouncementModal').length > 0) {
                $('#holidaysAnnouncementModal').modal('show');
            }
        });

    </script>

@endsection

@section('css')
    <style>
        /* Custom variables for student portal */
        :root {
            --student-primary: #4f46e5;
            --student-primary-hover: #4338ca;
            --student-success: #10b981;
            --student-warning: #f59e0b;
            --student-danger: #ef4444;
            --student-info: #06b6d4;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            --card-shadow-hover: 0 20px 35px -5px rgba(79, 70, 229, 0.1), 0 10px 15px -5px rgba(79, 70, 229, 0.04);
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Portal Design Base */
        .student-dashboard {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
        }

        /* Profile Welcome Banner */
        .profile-banner {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 60%, #06b6d4 100%) !important;
            border-radius: 20px !important;
            border: none !important;
            color: #ffffff !important;
            position: relative;
            overflow: hidden;
        }

        .profile-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 320px;
            height: 320px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            pointer-events: none;
        }

        .profile-banner::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Avatar styling with gold/indigo glowing border */
        .avatar-container {
            position: relative;
            display: inline-block;
        }

        .avatar-ring {
            padding: 4px;
            background: linear-gradient(135deg, #06b6d4, #4f46e5);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.25);
        }

        .avatar-img {
            border: 4px solid #ffffff;
            border-radius: 50%;
            transition: var(--transition-smooth);
        }

        /* Premium Card Styles */
        .dashboard-card {
            border: none !important;
            border-radius: 18px !important;
            background: #ffffff !important;
            box-shadow: var(--card-shadow) !important;
            transition: var(--transition-smooth) !important;
            overflow: hidden;
            border-top: 3px solid transparent !important;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover) !important;
            border-top: 3px solid var(--student-primary) !important;
        }

        /* Stat Soft Glow Accent Texts */
        .glow-text-success {
            text-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        }

        .glow-text-danger {
            text-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        }

        /* Modern Progress Indicators */
        .progress-container {
            background: #f1f5f9;
            border-radius: 30px;
            overflow: hidden;
            padding: 3px;
        }

        .progress-bar-premium {
            height: 10px;
            border-radius: 30px;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
            transition: width 1s ease-in-out;
        }

        /* Rounded utility shapes */
        .rounded-12 {
            border-radius: 12px !important;
        }

        /* Custom Icon Highlight Wrappers */
        .icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            transition: var(--transition-smooth);
        }

        .icon-wrap-primary {
            background: rgba(79, 70, 229, 0.08);
            color: #4f46e5;
        }

        .icon-wrap-success {
            background: rgba(16, 185, 129, 0.08);
            color: #10b981;
        }

        .icon-wrap-danger {
            background: rgba(239, 68, 68, 0.08);
            color: #ef4444;
        }

        .icon-wrap-warning {
            background: rgba(245, 158, 11, 0.08);
            color: #f59e0b;
        }

        .icon-wrap-info {
            background: rgba(6, 182, 212, 0.08);
            color: #06b6d4;
        }

        .dashboard-card:hover .icon-wrap {
            transform: scale(1.1) rotate(4deg);
        }

        /* Elegant Badges */
        .badge-premium {
            padding: 6px 12px !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            border-radius: 30px !important;
        }

        .badge-premium-success {
            background: rgba(16, 185, 129, 0.08) !important;
            color: #065f46 !important;
            border: 1px solid rgba(16, 185, 129, 0.15) !important;
        }

        .badge-premium-danger {
            background: rgba(239, 68, 68, 0.08) !important;
            color: #991b1b !important;
            border: 1px solid rgba(239, 68, 68, 0.15) !important;
        }

        .badge-premium-primary {
            background: rgba(79, 70, 229, 0.08) !important;
            color: #3730a3 !important;
            border: 1px solid rgba(79, 70, 229, 0.15) !important;
        }

        /* Metric Numbers */
        .metric-value {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        /* Premium Modal Overrides */
        .modal-content {
            border: none !important;
            border-radius: 20px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.12) !important;
        }

        .modal-header {
            border-bottom: 1px solid #f1f5f9 !important;
            background: #f8fafc;
            border-top-left-radius: 20px !important;
            border-top-right-radius: 20px !important;
            padding: 18px 24px !important;
        }

        .modal-header h5 {
            font-weight: 700 !important;
            color: #0f172a !important;
        }

        .modal-body {
            padding: 24px !important;
        }

        .modal-footer {
            border-top: 1px solid #f1f5f9 !important;
            padding: 16px 24px !important;
            border-bottom-left-radius: 20px !important;
            border-bottom-right-radius: 20px !important;
        }

        /* Modernized tables */
        .table {
            border-color: #f1f5f9 !important;
        }

        .table th {
            background: #f8fafc !important;
            color: #475569 !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            font-size: 0.72rem !important;
            letter-spacing: 0.04em !important;
            padding: 12px 16px !important;
            border-bottom: 2px solid #f1f5f9 !important;
        }

        .table td {
            padding: 14px 16px !important;
            color: #334155 !important;
            font-size: 0.85rem !important;
            vertical-align: middle !important;
        }
    </style>
@endsection