@extends('staff.layouts.master')
@section('title', 'Students')

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <a href="javascript:window.history.back();"
                    class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                    <i class="fas fa-chevron-left"></i>
                </a>
                Students ({{ $students->total() }})
            </h4>
            @if(auth('staff')->user()->hasRoleId(utility('id_enrolment_dept')) || auth('staff')->user()->hasRoleId(utility('id_operation_dept')))
                <a href="{{ route('staff.students.create') }}" class="btn btn-primary">
                    Add Student
                </a>
            @endif
        </div>

        <div class="card-body table-responsive">

            <form method="GET" class="row mb-3">

                <div class="col-md-3">
                    <label class="form-label fw-bold">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search name or contact">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-control select2">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="passout" {{ request('status') == 'passout' ? 'selected' : '' }}>Passout</option>
                        <option value="dropout" {{ request('status') == 'dropout' ? 'selected' : '' }}>Dropout</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Blocked Status</label>
                    <select name="is_blocked" class="form-control select2">
                        <option value="">All</option>
                        <option value="1" {{ request('is_blocked') == '1' ? 'selected' : '' }}>Blocked</option>
                        <option value="0" {{ request('is_blocked') == '0' ? 'selected' : '' }}>Unblocked</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('staff.students.index') }}" class="btn btn-light">Reset</a>
                </div>

            </form>

            <table class="table table-bordered align-middle">

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($students as $student)

                        <tr>

                            <td><a href="{{ route('staff.students.show', encrypt($student->id)) }}">{{ $student->name }}</a><br>
                                @isset($student->dob)
                                    <br><small>DOB: {{ $student->dob_formatted }}</small>
                                @endisset
                            </td>
                            <td>{{ $student->formatted_contact_number }}
                                @if($student->is_whatsapp_different)
                                    <br><small class="text-success"><i class="mdi mdi-whatsapp"></i>
                                        {{ $student->formatted_whatsapp_number }}</small>
                                @endif
                            </td>
                            <td>{{ $student->email ?? '-' }}</td>

                            <td>
                                <span
                                    class="badge {{ $student->status == 'active' ? 'bg-success' : '' }} {{ $student->status == 'passout' ? 'bg-info' : '' }} {{ $student->status == 'dropout' ? 'bg-danger' : '' }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                                @if($student->is_blocked)
                                    <span class="badge bg-danger">Blocked</span>
                                @endif
                            </td>

                            <td>
                                @php
                                    $enrolmentRoleId = utility('id_enrolment_dept');
                                    $administratorRoleId = utility('id_administrator_dept');
                                    $financeRoleId = utility('id_finance_dept');
                                    $hrRoleId = utility('id_hr_dept');
                                    $operationRoleId = utility('id_operation_dept');
                                    $staff = auth('staff')->user();
                                @endphp

                                <div class="d-flex gap-2 align-items-center">

                                    <a href="{{ route('staff.students.show', encrypt($student->id)) }}"
                                        title="View Student Details">
                                        <i class="mdi mdi-eye text-primary font-size-16"></i>
                                    </a>
                                    @if($staff->hasRoleId($enrolmentRoleId) || $staff->hasRoleId($operationRoleId))
                                        <a href="{{ route('staff.students.edit', encrypt($student->id)) }}" title="Edit Student">
                                            <i class="mdi mdi-pencil text-success font-size-16"></i>
                                        </a>
                                        <a href="{{ route('staff.students.toggleBlock', encrypt($student->id)) }}"
                                            onclick="return confirm('Are you sure you want to {{ $student->is_blocked ? 'unblock' : 'block' }} this student?')"
                                            title="{{ $student->is_blocked ? 'Unblock Student' : 'Block Student' }}">
                                            <i
                                                class="mdi {{ $student->is_blocked ? 'mdi-lock-open text-warning' : 'mdi-lock text-danger' }} font-size-16"></i>
                                        </a>
                                    @endif
                                    @if($staff->hasRoleId($operationRoleId))
                                        <a href="#" data-plugin="delete-student"
                                            data-check-url="{{ route('staff.students.check_related', encrypt($student->id)) }}"
                                            data-target-form="#delete_{{ $student->id }}">
                                            <i class="mdi mdi-trash-can text-danger"></i>
                                        </a>

                                        <form id="delete_{{ $student->id }}" method="POST"
                                            action="{{ route('staff.students.destroy', encrypt($student->id)) }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif

                                </div>


                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center">No Students Found</td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

            {{ $students->links() }}

        </div>
    </div>

@endsection

@section('script')
    <script>
        $('.select2').select2();

        $(document).on('click', '[data-plugin="delete-student"]', function (e) {
            e.preventDefault();
            var checkUrl = $(this).data('check-url');
            var targetForm = $(this).data('target-form');

            $.get(checkUrl, function (data) {
                var lines = [];
                if (data.fees > 0) lines.push('<li>' + data.fees + ' fee record(s)</li>');
                if (data.attendances > 0) lines.push('<li>' + data.attendances + ' attendance record(s)</li>');
                if (data.class_rooms > 0) lines.push('<li>' + data.class_rooms + ' class assignment(s)</li>');

                var html = lines.length
                    ? '<p>The following related data will also be affected:</p><ul>' + lines.join('') + '</ul>'
                    : '<p>No related records found.</p>';

                Swal.fire({
                    title: 'Delete ' + data.name + '?',
                    html: html,
                    icon: lines.length ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $(targetForm).submit();
                    }
                });
            });
        });
    </script>
@endsection