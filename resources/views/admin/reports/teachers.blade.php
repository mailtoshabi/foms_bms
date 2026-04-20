@extends('admin.layouts.master')
@section('title','Teachers Report')

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

<div class="card-header d-flex justify-content-between">
<h4>Teachers Report</h4>
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

<form method="GET" class="row mb-3">

    <div class="col-md-2">
        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
    </div>

    <div class="col-md-2">
        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
    </div>

    <div class="col-md-2">
        <select name="status" class="form-control">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <div class="col-md-2">
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('admin.reports.teachers') }}" class="btn btn-secondary">Reset</a>
    </div>

    <div class="col-md-2 text-end">
        <a href="{{ route('admin.reports.teachers.export', request()->all()) }}" class="btn btn-success">
            Export
        </a>
    </div>

</form>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Qualification</th>
            <th>Status</th>
            <th>Date Joined</th>
            <th
        </tr>
    </thead>
    <tbody>
        @foreach($teachers as $teacher)
            <tr>
                <td>{{ $teacher->name }}</td>
                <td>{{ $teacher->formatted_contact_number }}
                    @if($teacher->is_whatsapp_different)
                        <br><small class="text-success"><i class="mdi mdi-whatsapp"></i> {{ $teacher->formatted_whatsapp_number }}</small>
                    @endif
                </td>
                <td>{{ $teacher->email ?? 'N/A' }}</td>
                <td>{{ $teacher->qualification ?? 'N/A' }}</td>
                <td>
                    <span class="badge bg-{{ $teacher->status == 'active' ? 'success' : 'danger' }}">
                        {{ ucfirst($teacher->status) }}
                    </span>
                </td>
                <td>{{ $teacher->created_at->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('admin.reports.teachers.show', encrypt($teacher->id)) }}" class="btn btn-sm btn-info">
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
