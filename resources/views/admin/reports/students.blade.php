@extends('admin.layouts.master')
@section('title', 'Students Report')

@section('content')

    <div class="card mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.student-leads') ? 'active' : '' }}"
                        href="{{ route('admin.reports.student-leads') }}">
                        Student Leads
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.students') ? 'active' : '' }}"
                        href="{{ route('admin.reports.students') }}">
                        Students
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
                Students Report
            </h4>
            <button type="submit" form="filter-form" formaction="{{ route('admin.reports.students.export') }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </button>
        </div>

        <div class="card-body table-responsive">

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card p-3">Total Students: {{ $totalStudents }}</div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">Active: {{ $activeStudents }}</div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">Inactive: {{ $inactiveStudents }}</div>
                </div>
            </div>

            <form method="GET" id="filter-form" class="row mb-3 align-items-end">

                <div class="col-md-2">
                    <label class="form-label fw-bold">Name/Phone</label>
                    <input type="text" name="name" class="form-control" value="{{ request('name') }}"
                        placeholder="Search Name/Phone">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="passout" {{ request('status') == 'passout' ? 'selected' : '' }}>Passout</option>
                        <option value="dropout" {{ request('status') == 'dropout' ? 'selected' : '' }}>Dropout</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Blocked Status</label>
                    <select name="is_blocked" class="form-control">
                        <option value="">All</option>
                        <option value="1" {{ request('is_blocked') == '1' ? 'selected' : '' }}>Blocked</option>
                        <option value="0" {{ request('is_blocked') == '0' ? 'selected' : '' }}>Unblocked</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.reports.students') }}" class="btn btn-secondary w-100">Reset</a>
                </div>

            </form>

            <table class="table table-bordered  align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Date Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>
                                <a href="{{ route('admin.reports.students.show', encrypt($student->id)) }}">
                                    {{ $student->admission_no }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.reports.students.show', encrypt($student->id)) }}">
                                    {{ $student->name }}
                                </a>
                            </td>
                            <td>{{ $student->formatted_contact_number }}
                                @if($student->is_whatsapp_different)
                                    <br><small class="text-success"><i class="mdi mdi-whatsapp"></i>
                                        {{ $student->formatted_whatsapp_number }}</small>
                                @endif
                            </td>
                            <td>{{ $student->email ?? 'N/A' }}</td>
                            <td>
                                <span
                                    class="badge bg-{{ $student->status == 'active' ? 'success' : ($student->status == 'passout' ? 'info' : 'danger') }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                                @if($student->is_blocked)
                                    <span class="badge bg-danger">Blocked</span>
                                @endif
                            </td>
                            <td>{{ $student->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.reports.students.show', encrypt($student->id)) }}"
                                    class="btn btn-sm btn-primary">
                                    View
                                </a>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $students->links() }}

        </div>
    </div>

@endsection