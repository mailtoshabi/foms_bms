@extends('teacher.layouts.master-layouts-noleft')

@section('title', 'Teacher Dashboard')

@section('content')
    @php $teacher = Auth::guard('teacher')->user(); @endphp

    <div class="teacher-dashboard container-fluid px-0">

        {{-- WELCOME BANNER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card profile-banner shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                            @if($teacher->photo)
                                <div class="avatar-container">
                                    <div class="avatar-ring">
                                        <img src="{{ asset('storage/' . $teacher->photo) }}" class="avatar-img shadow-lg"
                                            width="90" height="90" style="object-fit: cover;">
                                    </div>
                                </div>
                            @else
                                <div class="avatar-container">
                                    <div class="avatar-ring">
                                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle shadow-lg text-primary"
                                            style="width: 90px; height: 90px; font-size: 2rem; font-weight: 700;">
                                            {{ strtoupper(substr($teacher->name, 0, 1)) }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="text-center text-md-start">
                                <span
                                    class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-2 mb-2 small fw-bold"
                                    style="backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                    <i class="fas fa-chalkboard-teacher me-1 text-info"></i> Instructor Portal
                                </span>
                                <span
                                    class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-2 mb-2 small fw-bold ms-md-2 badge-interactive"
                                    style="backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.25) !important;"
                                    data-bs-toggle="modal" data-bs-target="#scoreDetailsModal">
                                    <i class="fas fa-trophy me-1 text-warning"></i> Level: <strong>{{ $rankData['label'] }}</strong> (Score: <strong>{{ $rankData['score'] }}</strong>)
                                    <i class="fas fa-info-circle ms-1 text-info" style="font-size: 0.9em;"></i>
                                </span>
                                <h2 class="mb-1 text-white fw-bold">Welcome back, {{ $teacher->name }}!</h2>
                                <p class="mb-0 text-white text-opacity-80"><i class="fas fa-envelope me-1"></i>
                                    {{ $teacher->email }} @if($teacher->phone) | <i class="fas fa-phone me-1"></i>
                                    {{ $teacher->formatted_phone }} @endif</p>
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
                    <div class="card border-0 shadow-sm" style="border-left: 4px solid #ffc107 !important; background-color: #fffdf0; border-radius: 12px;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(255, 193, 7, 0.15);">
                                        <i class="fas fa-calendar-alt text-warning" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1" style="color: #856404;">Upcoming Holidays</h6>
                                        <div class="text-muted small">
                                            @foreach($holidays as $holiday)
                                                <span class="me-3 d-inline-block">
                                                    <i class="fas fa-bullhorn text-warning me-1"></i>
                                                    <strong class="text-dark">{{ $holiday->title }}</strong>: 
                                                    <span class="text-primary fw-semibold">{{ $holiday->date->format('d M Y') }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-warning text-dark border-warning py-1 px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#holidaysAnnouncementModal">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Holidays Announcement Modal -->
            <div class="modal fade" id="holidaysAnnouncementModal" tabindex="-1" aria-labelledby="holidaysAnnouncementModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                        <div class="modal-header bg-warning text-dark border-0 py-3">
                            <h5 class="modal-title fw-bold" id="holidaysAnnouncementModalLabel">
                                <i class="fas fa-bullhorn me-2"></i> Holiday Announcement!
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            @foreach($holidays as $holiday)
                                <div class="mb-4 pb-3 {{ !$loop->last ? 'border-bottom border-light' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="fw-bold text-dark mb-0">{{ $holiday->title }}</h5>
                                        <span class="badge bg-soft-info text-info font-size-12 px-2.5 py-1.5 rounded-pill" style="background-color: rgba(43, 154, 233, 0.1);">
                                            <i class="far fa-calendar-alt me-1"></i> {{ $holiday->date->format('d M Y') }}
                                        </span>
                                    </div>
                                    <p class="text-muted mb-0" style="white-space: pre-line;">{{ $holiday->description ?: 'No additional details provided.' }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer border-0 bg-light p-3">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- HIGHLIGHT SUMMARY CARDS --}}
        <div class="row g-4 mb-4">
            {{-- ACTIVE CLASS ROOMS --}}
            <div class="col-md-4">
                <a href="{{ route('teacher.classes.index') }}" class="text-decoration-none">
                    <div class="card dashboard-card h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted fw-bold mb-1 small tracking-wider">Active Classrooms
                                </h6>
                                <span class="metric-value text-dark fw-extrabold">{{ $classes->count() }}</span>
                            </div>
                            <div class="icon-wrap icon-wrap-primary">
                                <i class="fas fa-laptop-house"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- COMPLETED SESSIONS --}}
            <div class="col-md-4">
                <a href="{{ route('teacher.sessions.index') }}" class="text-decoration-none">
                    <div class="card dashboard-card h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted fw-bold mb-1 small tracking-wider">Completed Sessions
                                </h6>
                                <span
                                    class="metric-value text-success glow-text-success fw-extrabold">{{ $completedSessions }}</span>
                            </div>
                            <div class="icon-wrap icon-wrap-success">
                                <i class="fas fa-check-double"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- THIS MONTH NOTES --}}
            <div class="col-md-4">
                <a href="{{ route('teacher.sessions.index') }}?filter=status_pending&date_from=&date_to="
                    class="text-decoration-none">
                    <div class="card dashboard-card h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted fw-bold mb-1 small tracking-wider">Pending Sessions
                                </h6>
                                <span
                                    class="metric-value text-warning glow-text-warning fw-extrabold">{{ $pendingSessions }}</span>
                            </div>
                            <div class="icon-wrap icon-wrap-warning">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- SECONDARY METRICS GRID --}}
        <div class="row g-4 mb-4">
            {{-- TOTAL HOURS --}}
            <div class="col-6 col-lg-3">
                <div class="card dashboard-card sub-card h-100">
                    <div class="card-body p-3">
                        <small class="text-muted fw-bold d-block text-uppercase mb-1"
                            style="font-size: 0.68rem; letter-spacing: 0.05em;">This Month Hours</small>
                        <span class="fs-4 fw-extrabold text-primary">{{ $totalHours }} <span
                                class="fs-6 text-muted font-normal">hrs</span></span>
                    </div>
                </div>
            </div>

            {{-- YEARLY EARNINGS --}}
            <div class="col-6 col-lg-3">
                <div class="card dashboard-card sub-card h-100">
                    <div class="card-body p-3">
                        <small class="text-muted fw-bold d-block text-uppercase mb-1"
                            style="font-size: 0.68rem; letter-spacing: 0.05em;">Yearly Earnings</small>
                        <span class="fs-4 fw-extrabold text-success">₹{{ number_format($yearlyEarnings, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- UPCOMING SALARY --}}
            <div class="col-6 col-lg-3">
                <div class="card dashboard-card sub-card h-100">
                    <div class="card-body p-3">
                        <small class="text-muted fw-bold d-block text-uppercase mb-1"
                            style="font-size: 0.68rem; letter-spacing: 0.05em;">Upcoming Salary</small>
                        <span class="fs-4 fw-extrabold text-danger">₹{{ number_format($upcomingSalary, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- SALARY PENDING CALCULATION --}}
            <div class="col-6 col-lg-3">
                <div class="card dashboard-card sub-card h-100">
                    <div class="card-body p-3">
                        <small class="text-muted fw-bold d-block text-uppercase mb-1"
                            style="font-size: 0.68rem; letter-spacing: 0.05em;">Pending Salary</small>
                        <span class="fs-4 fw-extrabold text-warning">₹{{ number_format($pendingSalary, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- DUAL COLUMN GRID: CLASS NOTES & SALARY HISTORY --}}
        <div class="row g-4">
            {{-- LATEST CLASS NOTES --}}
            <div class="col-md-6">
                <div class="card dashboard-card h-100">
                    <div
                        class="card-header bg-white border-light py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="m-0 fw-bold text-dark"><i class="fas fa-file-signature text-warning me-2"></i>Latest
                            Class Notes</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('teacher.notes.index') }}"
                                class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fw-semibold"><i
                                    class="fas fa-list me-1"></i> View All</a>
                            <a href="{{ route('teacher.notes.create') }}"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold"><i
                                    class="fas fa-plus me-1"></i> Add Note</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Class</th>
                                        <th>Files</th>
                                        <th>Created</th>
                                        <th width="110" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notes as $note)
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $note->title }}</td>
                                            <td>{{ $note->classRoom?->name ?? '-' }}</td>
                                            <td>
                                                @if($note->files->count() > 0)
                                                    <span
                                                        class="badge bg-soft-primary text-primary rounded-pill px-2.5 py-1">{{ $note->files->count() }}
                                                        file(s)</span>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td><small class="text-muted">{{ $note->created_at->format('d M Y') }}</small></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="{{ route('teacher.notes.show', encrypt($note->id)) }}"
                                                        class="btn btn-sm btn-light text-primary rounded-circle border"
                                                        style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                        title="View">
                                                        <i class="fas fa-eye small"></i>
                                                    </a>
                                                    <form action="{{ route('teacher.notes.destroy', encrypt($note->id)) }}"
                                                        method="POST" style="display:inline;"
                                                        onsubmit="return confirm('Delete this note?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-light text-danger rounded-circle border"
                                                            style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                            title="Delete">
                                                            <i class="fas fa-trash small"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No notes uploaded yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SALARY HISTORY --}}
            <div class="col-md-6">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-light py-3 px-4">
                        <h5 class="m-0 fw-bold text-dark"><i class="fas fa-history text-success me-2"></i>Latest Salary
                            History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Cycle</th>
                                        <th>Total Hours</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($salaries as $salary)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-dark">
                                                    {{ \Carbon\Carbon::parse($salary->cycle_start)->format('d M Y') }}
                                                </div>
                                                <small class="text-muted">to
                                                    {{ \Carbon\Carbon::parse($salary->cycle_end)->format('d M Y') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark rounded-pill px-2.5 py-1">
                                                    {{ number_format($salary->total_hours, 2) }} hrs
                                                </span>
                                            </td>
                                            <td>
                                                <strong
                                                    class="text-success d-block">₹{{ number_format($salary->total_amount, 2) }}</strong>
                                                @if($salary->status == 'paid')
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Paid:
                                                        {{ optional($salary->payment_date)->format('d M Y') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($salary->status == 'paid')
                                                    <span class="badge badge-premium badge-premium-success">Paid</span>
                                                @else
                                                    <span
                                                        class="badge bg-soft-warning text-warning border-warning border rounded-pill px-2.5 py-1">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No salary payout logs found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ASSIGNED CLASSES TABLE --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div
                            class="card-header bg-white border-light py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="m-0 fw-bold text-dark"><i class="fas fa-laptop-house text-info me-2"></i>Active
                                Classes
                            </h5>
                            <span class="badge badge-premium badge-premium-primary">{{ $classes->count() }} Active
                                Classes</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Class Name</th>
                                            <th>Type</th>
                                            <th>Schedule Days & Timing</th>
                                            <th>Hourly Wage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($classes as $class)
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $class->course->name ?? '-' }}</td>
                                                <td>{{ $class->name }}</td>
                                                <td><span
                                                        class="badge badge-premium badge-premium-primary">{{ ucfirst($class->classType->name ?? '-') }}</span>
                                                </td>
                                                <td>
                                                    @if($class->selected_days)
                                                        <div class="fw-semibold text-dark">
                                                            {{ implode(', ', $class->selected_days ?? []) }}
                                                        </div>
                                                        <small class="text-muted"><i class="far fa-clock me-1"></i>
                                                            {{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="fw-bold text-success">
                                                    ₹{{ number_format($class->pivot->hourly_wage, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No classrooms assigned yet
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ANALYTICS CHART CARD --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div class="card-header bg-white border-light py-3 px-4">
                            <h5 class="m-0 fw-bold text-dark"><i class="fas fa-chart-line text-primary me-2"></i>Sessions &
                                Earnings Analytics</h5>
                        </div>
                        <div class="card-body p-4">
                            <div style="height: 320px; position: relative;">
                                <canvas id="teacherChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Score Details Modal --}}
        <div class="modal fade" id="scoreDetailsModal" tabindex="-1" aria-labelledby="scoreDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                    <div class="modal-header border-bottom-0 text-white p-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-chart-line text-info fs-4"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold m-0" id="scoreDetailsModalLabel">Performance Insights & Rating</h5>
                                <small class="text-white text-opacity-70">Understand your teaching metrics & level metrics</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <!-- Top stats grid -->
                        <div class="row g-3 mb-4">
                            <!-- Hero Card: Level & Stars -->
                            <div class="col-md-5">
                                <div class="card border-0 h-100 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: white;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between text-center">
                                        <div>
                                            <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-3 py-1.5 mb-3 small fw-bold text-uppercase tracking-wider" style="font-size: 0.7rem;">
                                                Current Status
                                            </span>
                                            <h3 class="fw-bold mb-1">{{ $rankData['label'] }} Tier</h3>
                                            <div class="mb-3">
                                                @for($s = 1; $s <= 5; $s++)
                                                    <i class="fas fa-star" style="font-size: 1.4rem; color: {{ $s <= $rankData['stars'] ? '#fbbf24' : 'rgba(255, 255, 255, 0.2)' }};"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="my-3">
                                            <div class="display-4 fw-extrabold text-warning mb-0">{{ $rankData['score'] }}</div>
                                            <small class="text-white text-opacity-65">Performance Score</small>
                                        </div>
                                        <div class="mt-2 bg-white bg-opacity-10 rounded-3 py-2 px-3">
                                            <span class="small"><i class="fas fa-trophy text-warning me-1"></i> Ranked <strong>#{{ $rankData['rank'] }}</strong> among all teachers</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info/Guide Card -->
                            <div class="col-md-7">
                                <div class="card border-0 h-100 shadow-sm" style="border-radius: 16px;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-2"><i class="fas fa-rocket text-primary me-2"></i>How to Level Up & Improve?</h6>
                                            <p class="text-muted small mb-3" style="line-height: 1.5;">
                                                Your tier is calculated using a weighted index of your teaching activity, student retention, class notes delivery, and performance. Follow these recommendations to boost your score:
                                            </p>
                                            <ul class="list-unstyled mb-0 text-muted small">
                                                <li class="mb-2 d-flex align-items-start">
                                                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                                    <span><strong>Engage More Students (40% Weight):</strong> The number of active students you manage is the most significant factor. Retaining and attracting students to classes boosts your rating fastest.</span>
                                                </li>
                                                <li class="mb-2 d-flex align-items-start">
                                                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                                    <span><strong>Maintain High Attendance (10% Weight):</strong> Encourage regularity among your students. Active student presence ensures a solid score contribution.</span>
                                                </li>
                                                <li class="mb-2 d-flex align-items-start">
                                                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                                    <span><strong>Log Complete Sessions & Hours (25% combined):</strong> Complete all scheduled classroom slots. Both active class count (15%) and total teaching hours (10%) count directly.</span>
                                                </li>
                                                <li class="mb-0 d-flex align-items-start">
                                                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                                    <span><strong>Upload Class Notes (10% Weight):</strong> Share notes, slides, or homework resources after class. Uploading resources keeps students active and increases score.</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Breakdown -->
                        <div class="card border-0 shadow-sm mb-0" style="border-radius: 16px;">
                            <div class="card-header bg-white border-light py-3 px-4">
                                <h6 class="fw-bold text-dark m-0"><i class="fas fa-list-ol text-primary me-2"></i>Detailed Performance Metrics Breakdown</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="border-collapse: collapse;">
                                        <thead>
                                            <tr class="table-light">
                                                <th class="ps-4">Metric Dimension</th>
                                                <th class="text-center">Current Value</th>
                                                <th class="text-center">Weight</th>
                                                <th class="text-end pe-4">Score Points Contribution</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="icon-wrap icon-wrap-primary rounded-circle" style="width: 35px; height: 35px; min-width: 35px;">
                                                            <i class="fas fa-users" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block">Active Students</strong>
                                                            <span class="text-muted small" style="font-size: 0.75rem;">Unique active student count</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center fw-bold">{{ $rankData['studentsCount'] }}</td>
                                                <td class="text-center text-muted">40%</td>
                                                <td class="text-end fw-bold text-primary pe-4">+{{ round($rankData['studentsCount'] * 0.40 / 4, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="icon-wrap icon-wrap-success rounded-circle" style="width: 35px; height: 35px; min-width: 35px;">
                                                            <i class="fas fa-laptop-house" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block">Active Classes</strong>
                                                            <span class="text-muted small" style="font-size: 0.75rem;">Sessions completed</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center fw-bold">{{ $rankData['totalClasses'] }}</td>
                                                <td class="text-center text-muted">15%</td>
                                                <td class="text-end fw-bold text-success pe-4">+{{ round($rankData['totalClasses'] * 0.15 / 4, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="icon-wrap icon-wrap-info rounded-circle" style="width: 35px; height: 35px; min-width: 35px;">
                                                            <i class="fas fa-clock" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block">Total Hours</strong>
                                                            <span class="text-muted small" style="font-size: 0.75rem;">Total hours conducted</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center fw-bold">{{ $rankData['totalHours'] }} hrs</td>
                                                <td class="text-center text-muted">10%</td>
                                                <td class="text-end fw-bold text-info pe-4">+{{ round($rankData['totalHours'] * 0.10 / 4, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="icon-wrap icon-wrap-warning rounded-circle" style="width: 35px; height: 35px; min-width: 35px;">
                                                            <i class="fas fa-user-check" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block">Attendance Rate</strong>
                                                            <span class="text-muted small" style="font-size: 0.75rem;">Avg student attendance %</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center fw-bold">{{ $rankData['attendancePercent'] }}%</td>
                                                <td class="text-center text-muted">10%</td>
                                                <td class="text-end fw-bold text-warning pe-4">+{{ round($rankData['attendancePercent'] * 0.10 / 4, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="icon-wrap icon-wrap-danger rounded-circle" style="width: 35px; height: 35px; min-width: 35px;">
                                                            <i class="fas fa-file-signature" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block">Class Notes Uploaded</strong>
                                                            <span class="text-muted small" style="font-size: 0.75rem;">Total uploaded class resources</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center fw-bold">{{ $rankData['totalNotes'] }}</td>
                                                <td class="text-center text-muted">10%</td>
                                                <td class="text-end fw-bold text-danger pe-4">+{{ round($rankData['totalNotes'] * 0.10 / 4, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="icon-wrap rounded-circle" style="width: 35px; height: 35px; min-width: 35px; background: rgba(16, 185, 129, 0.08); color: #10b981;">
                                                            <i class="fas fa-wallet" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block">Earnings Factor</strong>
                                                            <span class="text-muted small" style="font-size: 0.75rem;">Earnings (scaled) index</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center fw-bold">₹{{ number_format($rankData['earnings'], 2) }}</td>
                                                <td class="text-center text-muted">15%</td>
                                                <td class="text-end fw-bold text-success pe-4">+{{ round(($rankData['earnings'] / 100) * 0.15 / 4, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 bg-light p-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('teacherChart');
            if (ctx) {
                // Create modern gradients for chart datasets
                const ctx2d = ctx.getContext('2d');

                const gradientPrimary = ctx2d.createLinearGradient(0, 0, 0, 300);
                gradientPrimary.addColorStop(0, 'rgba(79, 70, 229, 0.45)');
                gradientPrimary.addColorStop(1, 'rgba(79, 70, 229, 0.02)');

                const gradientSuccess = ctx2d.createLinearGradient(0, 0, 0, 300);
                gradientSuccess.addColorStop(0, 'rgba(16, 185, 129, 0.45)');
                gradientSuccess.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [
                            {
                                label: 'Sessions Conducted',
                                data: @json($classCounts),
                                borderColor: '#4f46e5',
                                backgroundColor: gradientPrimary,
                                fill: true,
                                tension: 0.35,
                                borderWidth: 3,
                                pointBackgroundColor: '#4f46e5',
                                pointHoverRadius: 6,
                                pointRadius: 4,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Earnings (₹)',
                                data: @json($earnings),
                                borderColor: '#10b981',
                                backgroundColor: gradientSuccess,
                                fill: true,
                                tension: 0.35,
                                borderWidth: 3,
                                pointBackgroundColor: '#10b981',
                                pointHoverRadius: 6,
                                pointRadius: 4,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: { family: 'Inter', size: 12, weight: '500' },
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 12,
                                titleFont: { family: 'Inter', size: 13, weight: '700' },
                                bodyFont: { family: 'Inter', size: 12 },
                                cornerRadius: 10
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Inter', size: 11, weight: '500' }, color: '#64748b' }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: { color: '#f1f5f9' },
                                ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                ticks: {
                                    font: { family: 'Inter', size: 11 },
                                    color: '#64748b',
                                    callback: function (value) { return '₹' + value; }
                                }
                            }
                        }
                    }
                });
            // Auto open holidays announcement modal if present
            if ($('#holidaysAnnouncementModal').length > 0) {
                var holidayModal = new bootstrap.Modal(document.getElementById('holidaysAnnouncementModal'));
                holidayModal.show();
            }
        });
    </script>
@endsection

@section('css')
    <style>
        /* Custom variables for teacher portal */
        :root {
            --teacher-primary: #4f46e5;
            --teacher-primary-hover: #4338ca;
            --teacher-success: #10b981;
            --teacher-warning: #f59e0b;
            --teacher-danger: #ef4444;
            --teacher-info: #06b6d4;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            --card-shadow-hover: 0 20px 35px -5px rgba(79, 70, 229, 0.1), 0 10px 15px -5px rgba(79, 70, 229, 0.04);
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Dashboard Font System */
        .teacher-dashboard {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
        }

        /* Instructor Profile Banner */
        .profile-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%) !important;
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
            background: rgba(255, 255, 255, 0.05);
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
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Avatar with cyan/indigo glowing outline */
        .avatar-container {
            position: relative;
            display: inline-block;
        }

        .avatar-ring {
            padding: 4px;
            background: linear-gradient(135deg, #06b6d4, #4f46e5);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 8px 20px rgba(6, 182, 212, 0.25);
        }

        .avatar-img {
            border: 4px solid #ffffff;
            border-radius: 50%;
            transition: var(--transition-smooth);
        }

        /* Dashboard Premium Card Styling */
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
            border-top: 3px solid var(--teacher-primary) !important;
        }

        /* Small metrics card override */
        .dashboard-card.sub-card {
            border-top: none !important;
            border-left: 3px solid transparent !important;
        }

        .dashboard-card.sub-card:hover {
            border-top: none !important;
            border-left: 3px solid var(--teacher-primary) !important;
            transform: translateY(-3px);
        }

        /* Glow Text Accents */
        .glow-text-success {
            text-shadow: 0 4px 12px rgba(16, 185, 129, 0.12);
        }

        .glow-text-warning {
            text-shadow: 0 4px 12px rgba(245, 158, 11, 0.12);
        }

        /* Custom Soft Icon Highlighter */
        .icon-wrap {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
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
            transform: scale(1.1) rotate(5deg);
        }

        /* Premium Customized Badges */
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

        /* Metric text styling */
        .metric-value {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        /* Modernized table styling */
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

        .fw-extrabold {
            font-weight: 850;
        }

        /* Interactive Badge */
        .badge-interactive {
            cursor: pointer;
            transition: var(--transition-smooth);
        }
        .badge-interactive:hover {
            background-color: rgba(255, 255, 255, 0.35) !important;
            transform: translateY(-1px);
        }
    </style>
@endsection