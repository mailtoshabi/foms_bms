@extends('teacher.layouts.master-layouts-noleft')

@section('title', 'Class Details')

@section('content')

    @include('components.alerts')

    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle" title="Go Back" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">
                {{ $class->name }} 
                @if ($class->is_completed)
                    <span class="badge bg-soft-success text-success border border-success rounded-pill px-3 py-1 font-size-12 ms-2">
                        <i class="fas fa-check-circle me-1"></i> Completed
                    </span>
                @else
                    <span class="badge bg-soft-primary text-primary border border-primary rounded-pill px-3 py-1 font-size-12 ms-2">
                        <i class="fas fa-play-circle me-1"></i> Active
                    </span>
                @endif
            </h4>
        </div>
        @if (!$class->is_completed)
            <button class="portal-btn portal-btn-primary" data-bs-toggle="modal" data-bs-target="#startClassModal">
                <i class="fas fa-plus"></i> Create New Session
            </button>
        @endif
    </div>

    {{-- Class Information Cards Grid --}}
    <div class="portal-info-grid">
        <div class="portal-info-card">
            <div class="portal-info-card-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="portal-info-card-content">
                <span class="portal-info-card-label">Course</span>
                <span class="portal-info-card-value">{{ $class->course->name ?? '-' }}</span>
            </div>
        </div>

        <div class="portal-info-card">
            <div class="portal-info-card-icon">
                <i class="fas fa-chalkboard"></i>
            </div>
            <div class="portal-info-card-content">
                <span class="portal-info-card-label">Type</span>
                <span class="portal-info-card-value">{{ ucfirst($class->classType->name ?? '-') }} Class</span>
            </div>
        </div>

        <div class="portal-info-card">
            <div class="portal-info-card-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="portal-info-card-content">
                <span class="portal-info-card-label">Days</span>
                <span class="portal-info-card-value">{{ implode(', ', $class->selected_days ?? []) }}</span>
            </div>
        </div>

        <div class="portal-info-card">
            <div class="portal-info-card-icon">
                <i class="far fa-clock"></i>
            </div>
            <div class="portal-info-card-content">
                <span class="portal-info-card-label">Time Slot</span>
                <span
                    class="portal-info-card-value">{{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}</span>
            </div>
        </div>

        <div class="portal-info-card">
            <div class="portal-info-card-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="portal-info-card-content">
                <span class="portal-info-card-label">Duration</span>
                <span class="portal-info-card-value">{{ $class->slot_duration }} minutes</span>
            </div>
        </div>

        <div class="portal-info-card">
            <div class="portal-info-card-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="portal-info-card-content">
                <span class="portal-info-card-label">Monthly Sessions</span>
                <span class="portal-info-card-value">{{ $class->classes_per_week * 4 }} Sessions</span>
            </div>
        </div>

        <div class="portal-info-card">
            <div class="portal-info-card-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="portal-info-card-content">
                <span class="portal-info-card-label">Hourly Wage</span>
                <span
                    class="portal-info-card-value text-success">₹{{ $class->teachers()->find(Auth::guard('teacher')->user()->id)->pivot->hourly_wage }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Students List Card --}}
        <div class="col-xl-4 col-lg-5">
            <div class="portal-card">
                <div class="portal-card-header">
                    <h4>Assigned Students</h4>
                    <span class="portal-badge portal-badge-primary">Total: {{ count($class->students) }}</span>
                </div>
                <div class="portal-card-body p-0 table-responsive">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-end">Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($class->students as $student)
                                @php
                                    $stat = $attendanceStats[$student->id] ?? null;
                                    $present = $stat->present ?? 0;
                                    $total = $totalClasses ?: 1;
                                    $percentage = round(($present / $total) * 100);
                                @endphp
                                <tr>
                                    <td class="fw-bold text-dark">{{ $student->name }}</td>
                                    <td class="text-end">
                                        <span
                                            class="portal-badge {{ $percentage >= 75 ? 'portal-badge-success' : ($percentage >= 50 ? 'portal-badge-warning' : 'portal-badge-danger') }}">
                                            {{ $present }}/{{ $total }} ({{ $percentage }}%)
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sessions Overview List Card --}}
        <div class="col-xl-8 col-lg-7">
            <div class="portal-card">
                <div class="portal-card-header">
                    <h4>Recent Sessions</h4>
                    <a href="{{ route('teacher.sessions.index') }}" class="portal-btn btn-light">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
                <div class="portal-card-body p-0 table-responsive">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Session Date</th>
                                <th>Class Timing</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($class->classHours->take(5) as $hour)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span
                                                class="text-dark fw-bold mb-1">{{ \Carbon\Carbon::parse($hour->created_at)->format('d M Y') }}</span>
                                            <small class="text-muted"><i
                                                    class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($hour->created_at)->format('h:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($hour->joined_at)
                                            <div class="d-flex flex-column">
                                                <span class="text-dark fw-semibold mb-1">Joined:
                                                    {{ \Carbon\Carbon::parse($hour->joined_at)->format('h:i A') }}</span>
                                                @if($hour->completed_at)
                                                    <small class="text-success"><i class="fas fa-check-circle me-1"></i>Completed:
                                                        {{ \Carbon\Carbon::parse($hour->completed_at)->format('h:i A') }}</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hour->status == 'completed')
                                            <span class="portal-badge portal-badge-success">Completed</span>
                                        @else
                                            <span class="portal-badge portal-badge-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            @if($hour->google_meet_link && $hour->status == 'pending')
                                                @if(\Carbon\Carbon::parse($hour->link_updated_at)->isToday())
                                                    <a href="{{ $hour->google_meet_link }}" target="_blank" rel="noopener"
                                                        class="portal-btn portal-btn-primary"
                                                        onclick="fetch('{{ route('teacher.class-hours.join', encrypt($hour->id)) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })">
                                                        <i class="fas fa-video"></i> Join
                                                    </a>
                                                @else
                                                    <button class="portal-btn btn-light text-nowrap" disabled
                                                        title="Google Meet link must be updated today to join.">
                                                        <i class="fas fa-video text-muted"></i> Join (Update Link)
                                                    </button>
                                                @endif
                                            @endif

                                            @if($hour->status == 'pending')
                                                <button class="portal-btn editClassHour text-white" data-id="{{ $hour->id }}"
                                                    data-link="{{ $hour->google_meet_link }}"
                                                    style="background-color: #f59e0b !important;">
                                                    <i class="fas fa-edit"></i> Link
                                                </button>
                                                <button class="portal-btn btn-success openAttendanceModal text-white"
                                                    data-id="{{ $hour->id }}" style="background-color: #10b981 !important;">
                                                    <i class="fas fa-check-double"></i> Complete
                                                </button>
                                            @else
                                                <button class="portal-btn btn-light" disabled>
                                                    <i class="fas fa-lock text-muted"></i> Locked
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-0">
                                        <div class="portal-empty-state">
                                            <i class="fas fa-history portal-empty-state-icon"></i>
                                            <div class="portal-empty-state-title">No Sessions Found</div>
                                            <p class="text-muted small m-0">No teaching sessions have been created for this
                                                class room yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Class Notes Card --}}
    <div class="portal-card">
        <div class="portal-card-header">
            <h4>Class Notes & Resources</h4>
            <a href="{{ route('teacher.notes.index') }}" class="portal-btn btn-light">
                <i class="fas fa-list"></i> View All Notes
            </a>
        </div>
        <div class="portal-card-body">
            <div id="classNotesList" class="row">
                @forelse($class->notes->take(5) as $note)
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded h-100 d-flex flex-column justify-content-between"
                            style="background: rgba(248, 250, 252, 0.5); border-radius: 12px !important;">
                            <div>
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="fw-bold text-dark m-0">
                                        {{ $note->title }}
                                        <span
                                            class="portal-badge {{ $note->visibility === 'public' ? 'portal-badge-info' : 'portal-badge-warning' }} ms-2">
                                            {{ ucfirst($note->visibility) }}
                                        </span>
                                    </h6>
                                    @if($note->teacher_id === Auth::guard('teacher')->user()->id)
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light p-0 border-0" type="button"
                                                data-bs-toggle="dropdown">
                                                <i class="mdi mdi-dots-vertical" style="font-size: 1.1rem;"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item text-danger delete-note" href="#"
                                                        data-note-id="{{ encrypt($note->id) }}">
                                                        <i class="mdi mdi-trash-can me-1"></i> Delete Note
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1 mb-2">
                                    By {{ $note->teacher->name ?? '-' }} • {{ $note->created_at->format('M d, Y H:i') }}
                                </small>
                                @if($note->content)
                                    <p class="text-muted small m-0 mt-2" style="line-height: 1.5;">
                                        {{ Str::limit($note->content, 120) }}</p>
                                @endif
                            </div>

                            {{-- Files List --}}
                            @if($note->files->count() > 0)
                                <div class="mt-3 border-top pt-2">
                                    <small class="text-muted fw-bold d-block mb-2">
                                        <i class="mdi mdi-attachment me-1"></i> {{ $note->files->count() }} file(s)
                                    </small>
                                    <div class="list-group list-group-flush">
                                        @foreach($note->files as $file)
                                            <div
                                                class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 bg-transparent border-0">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="mdi mdi-file-document-outline text-primary font-size-18"></i>
                                                    <div>
                                                        <strong
                                                            class="text-dark d-block font-size-12">{{ Str::limit($file->name, 25) }}</strong>
                                                        <small class="text-muted d-block font-size-10">{{ $file->size }}</small>
                                                    </div>
                                                </div>
                                                <a href="{{ route('teacher.notes.show', encrypt($file->id)) }}"
                                                    class="portal-btn btn-light py-1 px-2 font-size-11">
                                                    <i class="mdi mdi-eye"></i> View
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="portal-empty-state">
                            <i class="fas fa-folder portal-empty-state-icon"></i>
                            <div class="portal-empty-state-title">No Class Notes</div>
                            <p class="text-muted small m-0">No lecture notes, materials, or documents have been posted yet.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Create Session Modal --}}
    <div class="modal fade" id="startClassModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <form method="POST" action="{{ route('teacher.classes.start') }}">
                    @csrf
                    <input type="hidden" name="class_room_id" value="{{ $class->id }}">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold text-dark mb-0">Create New Session</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="portal-label">Google Meet Link</label>
                            <input type="url" name="google_meet_link" class="portal-input"
                                placeholder="https://meet.google.com/..." required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button class="portal-btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="portal-btn portal-btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Creating...'; this.form.submit();">Create
                            Session</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Class Modal --}}
    <div class="modal fade" id="editClassHourModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <form method="POST" id="editClassHourForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold text-dark mb-0">Edit Session Link</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="portal-label">Google Meet Link</label>
                            <input type="url" name="google_meet_link" id="edit_meet_link" class="portal-input"
                                placeholder="https://meet.google.com/..." required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button class="portal-btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="portal-btn portal-btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">Update
                            Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Attendance Modal --}}
    <div class="modal fade" id="attendanceModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <form method="POST" id="attendanceForm">
                    @csrf
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold text-dark mb-0">Mark Attendance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="portal-btn btn-light py-1 px-3" id="checkAll">Select All</button>
                            <button type="button" class="portal-btn btn-light py-1 px-3" id="uncheckAll">Select
                                None</button>
                        </div>
                        <div class="row" id="attendanceList">
                            {{-- Filled via JS --}}
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button class="portal-btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="portal-btn portal-btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">Save & Complete
                            Class</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $('.editClassHour').click(function () {
            let id = $(this).data('id');
            let link = $(this).data('link');

            $('#edit_meet_link').val(link);
            $('#editClassHourForm').attr('action', '/teacher/class-hours/' + id);
            $('#editClassHourModal').modal('show');
        });
    </script>

    <script>
        $('.openAttendanceModal').click(function () {
            let classHourId = $(this).data('id');

            $('#attendanceForm').attr('action', '/teacher/class-hours/' + classHourId + '/complete');

            $.get('/teacher/class-hours/' + classHourId + '/students', function (res) {
                let html = '';
                res.students.forEach(student => {
                    html += `
                            <div class="col-md-6 mb-2">
                                <label class="d-flex align-items-center border rounded p-3 portal-attendance-label bg-light" style="cursor: pointer; border-radius: 10px !important;">
                                    <input type="checkbox"
                                        name="attendance[${student.id}]"
                                        value="1"
                                        class="form-check-input me-3"
                                        style="transform: scale(1.1);"
                                        checked>
                                    <span class="text-dark fw-bold">${student.name}</span>
                                </label>
                            </div>
                        `;
                });
                $('#attendanceList').html(html);
                $('#attendanceModal').modal('show');
            });
        });
    </script>

    <script>
        $('#checkAll').click(function () {
            $('input[name^="attendance"]').prop('checked', true);
        });

        $('#uncheckAll').click(function () {
            $('input[name^="attendance"]').prop('checked', false);
        });
    </script>
@endsection