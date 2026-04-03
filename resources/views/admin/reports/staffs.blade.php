@extends('admin.layouts.master')
@section('title','Staff Report')

@section('content')

<div class="card mb-3">
    <div class="card-body p-2">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.staffs') ? 'active' : '' }}"
                href="{{ route('admin.reports.staffs') }}">
                    Staff List
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.staff.salary') ? 'active' : '' }}"
                href="{{ route('admin.reports.staff.salary') }}">
                    Salary
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="card">
<div class="card-header d-flex justify-content-between">
<h4>Staff Report</h4>
</div>

<div class="card-body table-responsive">

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card p-3">Total Staff: {{ $totalStaffs }}</div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">With Salary: {{ $withSalary }}</div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">Without Salary: {{ $withoutSalary }}</div>
        </div>
    </div>

<form method="GET" class="row mb-3">
    <div class="col-md-3">
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search name, phone, email">
    </div>

    <div class="col-md-2">
        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
    </div>

    <div class="col-md-2">
        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
    </div>

    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('admin.reports.staffs') }}" class="btn btn-secondary">Reset</a>
        <a href="{{ route('admin.reports.staffs.export', request()->all()) }}" class="btn btn-success">Export</a>
    </div>
</form>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Salary Amount</th>
            <th>Date Joined</th>
        </tr>
    </thead>
    <tbody>
        @forelse($staffs as $staff)
            <tr>
                <td>{{ $staff->name }}</td>
                <td>{{ $staff->phone ?? 'N/A' }}</td>
                <td>{{ $staff->email ?? 'N/A' }}</td>
                <td>₹ {{ number_format((float) ($staff->salary_amount ?? 0), 2) }}</td>
                <td>{{ optional($staff->created_at)->format('d M Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">No Records Found</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $staffs->links() }}

</div>
</div>

@endsection
