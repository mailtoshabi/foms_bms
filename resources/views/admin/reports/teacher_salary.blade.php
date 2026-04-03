@extends('admin.layouts.master')
@section('title','Teacher Salary Report')

@section('content')

<div class="card">

<div class="card-header d-flex justify-content-between">
<h4>Teacher Salary Report</h4>
</div>

<div class="card-body table-responsive">

<form method="GET" class="row mb-3">

<div class="col-md-3">
    <label>Name OR Phone</label>
<input type="text"
       name="search"
       value="{{ request('search') }}"
       class="form-control"
       placeholder="Search teacher">
</div>

<div class="col-md-2">
    <label>Status</label>
<select name="status" class="form-control">
<option value="">All Status</option>
<option value="paid" {{ request('status')=='paid'?'selected':'' }}>Paid</option>
<option value="unpaid" {{ request('status')=='unpaid'?'selected':'' }}>Unpaid</option>
<option value="partial" {{ request('status')=='partial'?'selected':'' }}>Partial</option>
</select>
</div>

<div class="col-md-2">
<label>From</label>
<input type="date"
       name="from_date"
       value="{{ request('from_date') }}"
       class="form-control">
</div>

<div class="col-md-2">
<label>To</label>
<input type="date"
       name="to_date"
       value="{{ request('to_date') }}"
       class="form-control">
</div>

<div class="col-md-3 d-flex align-items-end gap-2">
<button class="btn btn-primary">Filter</button>

<a href="{{ route('admin.reports.teacher.salary') }}"
class="btn btn-light">Reset</a>

<a href="{{ route('admin.reports.teacher.salary.export',request()->query()) }}"
class="btn btn-success">
<i class="mdi mdi-file-excel"></i> Export
</a>
</div>

</form>

<table class="table table-bordered align-middle">

<thead>
<tr>
<th>Teacher</th>
<th>Phone</th>
<th>Salary</th>
<th>Cycle</th>
<th>Status</th>
<th>Paid Date</th>
</tr>
</thead>

<tbody>

@forelse($data as $row)

<tr>
<td>{{ $row->name }}</td>
<td>{{ $row->phone }}</td>
<td>₹ {{ number_format($row->total_amount,2) }}</td>
<td>
{{ \Carbon\Carbon::parse($row->cycle_start)->format('d M') }}
-
{{ \Carbon\Carbon::parse($row->cycle_end)->format('d M Y') }}
</td>
<td>
<span class="badge {{ $row->status=='paid'?'bg-success':'bg-warning' }}">
{{ ucfirst($row->status) }}
</span>
</td>
<td>
{{ $row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format('d M Y') : '-' }}
</td>
</tr>

@empty
<tr>
<td colspan="6" class="text-center">No Records Found</td>
</tr>
@endforelse

</tbody>

</table>

{{ $data->links() }}

</div>
</div>

@endsection
