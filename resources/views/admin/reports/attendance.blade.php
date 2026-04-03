@extends('admin.layouts.master')
@section('title','Attendance Report')

@section('content')

<div class="card">

<div class="card-header d-flex justify-content-between">
<h4>Attendance Report</h4>
</div>

<div class="card-body table-responsive">

<form method="GET" class="row mb-3">

<div class="col-md-3">
<input type="text"
name="search"
value="{{ request('search') }}"
class="form-control"
placeholder="Search name or contact">
</div>

<div class="col-md-3">
<select name="status" class="form-control">
<option value="">All</option>
<option value="1" {{ request('status')==='1'?'selected':'' }}>Present</option>
<option value="0" {{ request('status')==='0'?'selected':'' }}>Absent</option>
</select>
</div>

<div class="col-md-3">
<input type="date"
name="date"
value="{{ request('date') }}"
class="form-control">
</div>

<div class="col-md-3 d-flex gap-2">
<button class="btn btn-primary">Filter</button>

<a href="{{ route('admin.reports.attendance') }}"
class="btn btn-light">Reset</a>

<a href="{{ route('admin.reports.attendance.export',request()->query()) }}"
class="btn btn-success">
<i class="mdi mdi-file-excel"></i> Export
</a>
</div>

</form>

<table class="table table-bordered align-middle">

<thead>
<tr>
<th>Student</th>
<th>Class</th>
<th>Date</th>
<th>Status</th>
</tr>
</thead>

<tbody>

@forelse($data as $row)

<tr>
<td>{{ $row->name }}
        <br><small>{{ $row->contact_number }}</small>
</td>
<td>{{ $row->class_name }}</td>
<td>{{ \Carbon\Carbon::parse($row->class_started_at)->format('d M Y') }}</td>
<td>
<span class="badge {{ $row->is_present ? 'bg-success' : 'bg-danger' }}">
{{ $row->is_present ? 'Present' : 'Absent' }}
</span>
</td>
</tr>

@empty
<tr>
<td colspan="4" class="text-center">No Records Found</td>
</tr>
@endforelse

</tbody>

</table>

{{ $data->links() }}

</div>
</div>

@endsection
