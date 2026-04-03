@extends('admin.layouts.master')
@section('title','Staff Salary Report')

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
<h4>Staff Salary Report</h4>
</div>

<div class="card-body table-responsive">

<form method="GET" class="row mb-3">
<div class="col-md-3">
<input type="text"
name="search"
value="{{ request('search') }}"
class="form-control"
placeholder="Search staff name or phone">
</div>

<div class="col-md-2">
<select name="status" class="form-control">
<option value="">All Status</option>
<option value="paid" {{ request('status')=='paid'?'selected':'' }}>Paid</option>
<option value="partial" {{ request('status')=='partial'?'selected':'' }}>Partial</option>
<option value="not_paid" {{ request('status')=='not_paid'?'selected':'' }}>Not Paid</option>
</select>
</div>

<div class="col-md-2">
<input type="month"
name="from_month"
value="{{ request('from_month') }}"
class="form-control">
</div>

<div class="col-md-2">
<input type="month"
name="to_month"
value="{{ request('to_month') }}"
class="form-control">
</div>

<div class="col-md-3 d-flex align-items-end gap-2">
<button class="btn btn-primary">Filter</button>

<a href="{{ route('admin.reports.staff.salary') }}"
class="btn btn-light">Reset</a>

<a href="{{ route('admin.reports.staff.salary.export',request()->query()) }}"
class="btn btn-success">
<i class="mdi mdi-file-excel"></i> Export
</a>
</div>

</form>

<table class="table table-bordered align-middle">
<thead>
<tr>
<th>Staff</th>
<th>Phone</th>
<th>Month</th>
<th>Salary</th>
<th>Paid</th>
<th>Balance</th>
<th>Status</th>
<th>Paid Date</th>
</tr>
</thead>

<tbody>
@forelse($data as $row)
<tr>
<td>{{ $row->name }}</td>
<td>{{ $row->phone ?? 'N/A' }}</td>
<td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row->salary_month)->format('M Y') }}</td>
<td>₹ {{ number_format($row->salary_amount, 2) }}</td>
<td>₹ {{ number_format($row->paid_amount, 2) }}</td>
<td>₹ {{ number_format($row->balance_due, 2) }}</td>
<td>
<span class="badge {{ $row->status == 'paid' ? 'bg-success' : ($row->status == 'partial' ? 'bg-warning text-dark' : 'bg-danger') }}">
{{ ucfirst(str_replace('_', ' ', $row->status)) }}
</span>
</td>
<td>{{ $row->paid_date ? \Carbon\Carbon::parse($row->paid_date)->format('d M Y') : '-' }}</td>
</tr>
@empty
<tr>
<td colspan="8" class="text-center">No Records Found</td>
</tr>
@endforelse
</tbody>
</table>

{{ $data->links() }}

</div>
</div>

@endsection
