@extends('admin.layouts.master')
@section('title','Teacher Leads Report')

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
<h4>Teacher Leads Report</h4>
</div>

<div class="card-body table-responsive">

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card p-3">Total Leads: {{ $totalLeads }}</div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">Converted: {{ $convertedLeads }}</div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">Pending: {{ $pendingLeads }}</div>
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
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
        </select>
    </div>

    <div class="col-md-2">
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('admin.reports.teacher-leads') }}" class="btn btn-secondary">Reset</a>
    </div>

    <div class="col-md-2 text-end">
        <a href="{{ route('admin.reports.teacher-leads.export', request()->all()) }}" class="btn btn-success">
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
            <th>Source</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($leads as $lead)
            <tr>
                <td>{{ $lead->name }}</td>
                <td>{{ $lead->contact_number }}</td>
                <td>{{ $lead->email ?? 'N/A' }}</td>
                <td>{{ $lead->source->name ?? '-' }}</td>
                <td>
                    <span class="badge bg-{{ $lead->status == 'approved' ? 'success' : 'warning' }}">
                        {{ ucfirst($lead->status) }}
                    </span>
                </td>
                <td>{{ $lead->created_at->format('d M Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $leads->links() }}

</div>
</div>

@endsection
