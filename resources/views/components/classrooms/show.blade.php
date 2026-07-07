@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="row">

    <div class="col-12">

        {{-- ================= CLASS DETAILS ================= --}}
        <div class="card">

            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <a href="javascript:window.history.back();"
                        class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <h4 class="mb-0">{{ $class->name }}</h4>
                    @if($class->is_completed)
                        <span class="badge bg-success ms-2">Completed</span>
                    @else
                        <span class="badge bg-primary ms-2">Active</span>
                    @endif
                </div>

                @php
                    $staff = auth('staff')->user();
                    $isAdmin = auth('admin')->check();
                    $isOperation = $staff && $staff->hasRoleId(utility('id_operation_dept'));
                    $isAdministrator = $staff && $staff->hasRoleId(utility('id_administrator_dept'));
                @endphp

                @if($isAdmin || $isOperation)
                    <a href="{{ route('staff.class_rooms.changeStatus', encrypt($class->id)) }}"
                        class="btn btn-sm {{ $class->is_completed ? 'btn-warning' : 'btn-success' }}"
                        onclick="return confirm('{{ $class->is_completed ? 'Are you sure you want to unmark this class as completed?' : 'Are you sure you want to mark this class as completed?' }}')">
                        <i class="fas {{ $class->is_completed ? 'fa-undo' : 'fa-check-circle' }}"></i>
                        {{ $class->is_completed ? 'Unmark Completed' : 'Mark as Completed' }}
                    </a>
                @endif
            </div>

            <div class="card-body">

                <p><strong>Course:</strong> {{ $class->course->name ?? '-' }}</p>
                <p><strong>Type:</strong> {{ ucfirst($class->classType->name ?? '-') }}</p>

                <p><strong>{{ $class->classType->id == 1 ? 'First Month Fee' : 'Admission Fee' }}:</strong> ₹
                    {{ number_format($class->admission_fee, 2) }}
                </p>
                <p><strong>Monthly Fee:</strong> ₹ {{ number_format($class->monthly_fee, 2) }}</p>

                <p><strong>Days:</strong> {{ implode(', ', $class->selected_days ?? []) }}</p>

                <p><strong>Time:</strong>
                    {{ $class->time_slot ? \Carbon\Carbon::parse($class->time_slot)->format('h:i A') : '-' }}
                </p>

                <p><strong>Duration:</strong> {{ $class->slot_duration }} mins</p>
                <p><strong>Classes :</strong> {{ $class->classes_per_week }} per week</p>
                <p><strong>Started From:</strong>
                    {{ $class->starting_date ? \Carbon\Carbon::parse($class->starting_date)->format('d F, Y') : '-' }}
                </p>

            </div>

        </div>


        {{-- ================= TEACHERS ================= --}}
        <div class="card">

            <div class="card-header d-flex justify-content-between">

                <h5>Teachers</h5>
                @if(!$class->is_completed)
                    @if($isAdmin || $isOperation || $isAdministrator)
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignTeacherModal" {{ $class->teachers->count() ? 'disabled' : '' }}>

                            <i class="fas fa-plus"></i> Assign Teacher

                        </button>
                    @endif
                @endif
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered  align-middle table-nowrap mb-0">

                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Wage/Hour</th>
                                @if(!$class->is_completed)
                                    @if($isAdmin || $isOperation || $isAdministrator)
                                    <th>Remove From Class</th> @endif
                                @endif
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($class->teachers as $teacher)

                                <tr>

                                    <td>
                                        <a
                                            href="{{ $isAdmin ? route('admin.reports.teachers.show', encrypt($teacher->id)) : route('staff.teachers.show', encrypt($teacher->id)) }}">
                                            {{ $teacher->name }}
                                        </a>
                                    </td>
                                    <td>{{ $teacher->formatted_phone }}</td>
                                    <td>
                                        ₹ {{ number_format($teacher->pivot->hourly_wage, 2) }}
                                    </td>
                                    @if(!$class->is_completed)
                                        @if($isAdmin || $isOperation || $isAdministrator)
                                            <td>

                                                <form method="POST" action="{{ route('staff.class_rooms.remove.teacher') }}"
                                                    onsubmit="return confirm('Are you sure you want to remove this teacher?\n\nWarning:\nPENDING class sessions assigned to this teacher in this classroom will be DELETED.')">

                                                    @csrf

                                                    <input type="hidden" name="class_room_id" value="{{ encrypt($class->id) }}">
                                                    <input type="hidden" name="teacher_id" value="{{ encrypt($teacher->id) }}">
                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times"></i>
                                                    </button>

                                                </form>

                                            </td>
                                        @endif
                                    @endif

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        No teachers assigned
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>
                </div>

            </div>

        </div>


        {{-- ================= STUDENTS ================= --}}
        <div class="card">

            <div class="card-header d-flex justify-content-between">

                <h5>Students</h5>
                @if(!$class->is_completed)
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignStudentModal"
                        @if(($class->classType->name ?? '') === 'individual' && $class->students->count() >= 1) disabled
                        title="Individual class already has a student" @endif>

                        <i class="fas fa-user-plus"></i> Add

                    </button>
                @endif

            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered  align-middle table-nowrap mb-0">
                        @php
                            $administratorRoleId = utility('id_administrator_dept');
                            $operationRoleId = utility('id_operation_dept');
                            $staff = auth('staff')->user();
                            $isAdmin = auth('admin')->check();
                        @endphp
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Assigned Date</th>
                                @if($isAdmin || $isOperation || $isAdministrator)
                                    @if(!$class->is_completed)
                                    <th>Remove from Class</th> @endif
                                @endif
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($class->students as $student)

                                <tr>

                                    <td>
                                        <a
                                            href="{{ $isAdmin ? route('admin.reports.students.show', encrypt($student->id)) : route('staff.students.show', encrypt($student->id)) }}">
                                            {{ $student->name }}
                                        </a>
                                    </td>
                                    <td>{{ $student->formatted_contact_number }}</td>
                                    <td>{{ $student->pivot->assigned_date ? \Carbon\Carbon::parse($student->pivot->assigned_date)->format('d M Y') : '-' }}
                                    </td>

                                    @if($isAdmin || $isOperation || $isAdministrator)
                                        @if(!$class->is_completed)
                                            <td>

                                                <form method="POST" action="{{ route('staff.class_rooms.remove.student') }}"
                                                    onsubmit="return confirm('Are you sure you want to remove this student?\n\nThis will:\n1. Delete all UNPAID fees for this student in this class.\n2. Delete PENDING class sessions (if individual class or last student).')">

                                                    @csrf

                                                    <input type="hidden" name="class_room_id" value="{{ encrypt($class->id) }}">
                                                    <input type="hidden" name="student_id" value="{{ encrypt($student->id) }}">

                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times"></i>
                                                    </button>

                                                </form>

                                            </td>
                                        @endif
                                    @endif
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        No students added
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>
                </div>

            </div>

        </div>


        {{-- ================= RECENT SESSIONS ================= --}}
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Recent Sessions</h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-nowrap mb-0">

                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Teacher</th>
                                <th>Duration</th>
                                <th>Wage / Hour</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($class->classHours->take(10) as $hour)
                                <tr>
                                    <td>
                                        <span
                                            class="fw-bold">{{ $hour->created_at ? $hour->created_at->format('d M Y') : '-' }}</span>
                                        <br>
                                        <small
                                            class="text-muted">{{ $hour->created_at ? $hour->created_at->format('h:i A') : '-' }}</small>
                                    </td>
                                    <td>{{ $hour->teacher->name ?? 'Not Assigned' }}</td>
                                    <td>{{ $hour->duration ? $hour->duration . ' mins' : '-' }}</td>
                                    <td>₹ {{ number_format($hour->hourly_wage, 2) }}</td>
                                    <td>
                                        @if($hour->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No recent sessions found
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


{{-- ================= ASSIGN TEACHER MODAL ================= --}}
<div class="modal fade" id="assignTeacherModal">

    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="{{ $assignTeacherRoute }}">
                @csrf

                <input type="hidden" name="class_room_id" value="{{ encrypt($class->id) }}">

                <div class="modal-header">
                    <h5>Assign Teacher</h5>
                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label>Teacher</label>

                        <select name="teacher_id" class="form-control select2" required>
                            @foreach($teachers as $teacher)
                                <option value="{{ encrypt($teacher->id) }}">
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                    <div class="mb-3">

                        <label>Wage Per Hour (₹)</label>

                        <input type="number" step="0.01" name="hourly_wage" class="form-control" required>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit"
                        onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">Save</button>
                </div>

            </form>

        </div>
    </div>

</div>


{{-- ================= ASSIGN STUDENTS MODAL ================= --}}
<div class="modal fade" id="assignStudentModal">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST" action="{{ $assignStudentRoute }}">
                @csrf

                <input type="hidden" name="class_room_id" value="{{ encrypt($class->id) }}">

                <div class="modal-header">
                    <h5>Add Students</h5>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <input type="text" id="studentSearch" class="form-control"
                            placeholder="Type name or contact to search students..." autocomplete="off">
                        <small class="text-muted">Search result will appear here. Selected students will be
                            preserved.</small>
                    </div>

                    {{-- Selected Students Preview --}}
                    <div id="selectedStudentsContainer" class="mb-3 d-flex flex-wrap gap-2">
                        {{-- Badges for selected students will appear here --}}
                    </div>

                    <div class="row" id="studentList">
                        {{-- Search results will be injected here via AJAX --}}
                        <div class="col-12 text-center py-4 text-muted" id="initialMessage">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <p>Start typing to find students to add...</p>
                        </div>
                    </div>

                    {{-- Hidden inputs for selected IDs --}}
                    <div id="hiddenInputsContainer"></div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit"
                        onclick="if(confirm('Have you checked and confirmed Fee Discount/Exemption?')) { this.disabled=true; this.innerText='Saving...'; this.form.submit(); } else { return false; }">Save</button>
                </div>

            </form>

        </div>
    </div>

</div>


@section('script')
    <script>
        $(document).ready(function () {
            let selectedStudents = new Map(); // Store as ID => Name
            let searchTimeout = null;

            function updateUI() {
                // Update badges
                let badgesHtml = '';
                let inputsHtml = '';

                selectedStudents.forEach((name, id) => {
                    badgesHtml += `
                                                                                                                                        <span class="badge bg-primary d-flex align-items-center gap-2 p-2">
                                                                                                                                            ${name}
                                                                                                                                            <i class="fas fa-times cursor-pointer remove-selected" data-id="${id}" style="cursor:pointer"></i>
                                                                                                                                        </span>`;
                    inputsHtml += `<input type="hidden" name="student_ids[]" value="${id}">`;
                });

                $('#selectedStudentsContainer').html(badgesHtml);
                $('#hiddenInputsContainer').html(inputsHtml);
            }

            // Handle checkbox changes in search results
            $(document).on('change', '.ajax-student-checkbox', function () {
                let id = $(this).val();
                let name = $(this).data('name');

                @if(($class->classType->name ?? '') === 'individual')
                    if ($(this).is(':checked')) {
                        selectedStudents.clear();
                        $('.ajax-student-checkbox').not(this).prop('checked', false);
                    }
                @endif

                                                                                                                                if ($(this).is(':checked')) {
                    selectedStudents.set(id, name);
                } else {
                    selectedStudents.delete(id);
                }
                updateUI();
            });

            // Handle badge removal
            $(document).on('click', '.remove-selected', function () {
                let id = $(this).data('id').toString();
                selectedStudents.delete(id);
                // Uncheck in the list if visible
                $(`.ajax-student-checkbox[value="${id}"]`).prop('checked', false);
                updateUI();
            });

            $('#studentSearch').on('input', function () {
                let q = $(this).val();
                clearTimeout(searchTimeout);

                if (q.length < 2) {
                    if (q.length === 0) {
                        $('#studentList').html(`
                                                                                                                                            <div class="col-12 text-center py-4 text-muted">
                                                                                                                                                <i class="fas fa-search fa-2x mb-2"></i>
                                                                                                                                                <p>Start typing to find students to add...</p>
                                                                                                                                            </div>`);
                    }
                    return;
                }

                searchTimeout = setTimeout(() => {
                    $('#studentList').html('<div class="col-12 text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');

                    $.ajax({
                        url: "{{ route('staff.students.search') }}",
                        method: 'GET',
                        data: {
                            q: q,
                            exclude_class_id: "{{ $class->id }}"
                        },
                        success: function (response) {
                            let html = '';

                            if (response.results.length === 0) {
                                html = '<div class="col-12 text-center py-4 text-muted">No students found.</div>';
                            } else {
                                response.results.forEach(student => {
                                    let isChecked = selectedStudents.has(student.id.toString()) ? 'checked' : '';
                                    html += `
                                                                                                                                                        <div class="col-md-6 mb-2">
                                                                                                                                                            <label class="d-flex align-items-center border p-2 rounded w-100 h-100" style="cursor: pointer;">
                                                                                                                                                                <input type="checkbox" value="${student.id}" data-name="${student.name}" 
                                                                                                                                                                    class="form-check-input me-2 ajax-student-checkbox" ${isChecked}>
                                                                                                                                                                <span>${student.name} <br><small class="text-muted">${student.admission_no || ''}</small></span>
                                                                                                                                                            </label>
                                                                                                                                                        </div>`;
                                });
                            }
                            $('#studentList').html(html);
                        }
                    });
                }, 300);
            });
        });
    </script>
@endsection