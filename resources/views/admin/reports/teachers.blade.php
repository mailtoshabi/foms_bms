@extends('admin.layouts.master')
@section('title', 'Teachers Report')

@section('content')

    <div class="card mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.teacher-leads') ? 'active' : '' }}"
                        href="{{ route('admin.reports.teacher-leads') }}">
                        Teacher Leads
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.teachers') ? 'active' : '' }}"
                        href="{{ route('admin.reports.teachers') }}">
                        Teachers
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.teacher.salary') ? 'active' : '' }}"
                        href="{{ route('admin.reports.teacher.salary') }}">
                        Salary
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
                Teachers Report
            </h4>
        </div>

        <div class="card-body table-responsive">

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card p-3">Total Teachers: {{ $totalTeachers }}</div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">Active: {{ $activeTeachers }}</div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">Inactive: {{ $inactiveTeachers }}</div>
                </div>
            </div>

            <form method="GET" class="row mb-3 align-items-end">

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
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.reports.teachers') }}" class="btn btn-secondary">Reset</a>
                </div>

                <div class="col-md-2 text-end">
                    <button type="submit" formaction="{{ route('admin.reports.teachers.export') }}" class="btn btn-success">
                        Export
                    </button>
                </div>

            </form>

            <table class="table table-bordered  align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Qualification</th>
                        <th>Status</th>
                        <th>Date Joined</th>
                        <th </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $teacher)
                        @php
                            $rank = teacherRankData($teacher->id);
                        @endphp
                        <tr>
                            <td>
                                <a
                                    href="{{ route('admin.reports.teachers.show', encrypt($teacher->id)) }}">{{ $teacher->name }}</a>
                            </td>
                            <td>{{ $teacher->formatted_contact_number }}
                                @if($teacher->is_whatsapp_different)
                                    <br><small class="text-success"><i class="mdi mdi-whatsapp"></i>
                                        {{ $teacher->formatted_whatsapp_number }}</small>
                                @endif
                            </td>
                            <td>{{ $teacher->email ?? 'N/A' }}</td>
                            <td>{{ $teacher->qualification ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $teacher->status == 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($teacher->status) }}
                                </span>
                                <span class="badge bg-{{ $rank['color'] }}  px-3 ">{{ $rank['label'] }}</span><br>
                                @for($s = 1; $s <= 5; $s++)
                                    <span
                                        style="font-size:1rem; color: {{ $s <= $rank['stars'] ? '#f1c40f' : '#ccc' }}">&#9733;</span>
                                @endfor
                                <small class="text-muted">Score: {{ $rank['score'] }}</small>
                            </td>
                            <td>{{ $teacher->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.reports.teachers.show', encrypt($teacher->id)) }}"
                                    class="btn btn-sm btn-info">
                                    View
                                </a>
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>

            {{ $teachers->links() }}

        </div>
    </div>

@endsection