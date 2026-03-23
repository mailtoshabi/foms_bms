@extends('staff.layouts.master')
@section('title','Teachers')

@section('content')

<div class="card">

<div class="card-header d-flex justify-content-between">

<h4>Teachers ({{ $teachers->total() }})</h4>

<a href="{{ route('staff.teachers.create') }}" class="btn btn-primary">
Add Teacher
</a>

</div>


<div class="card-body table-responsive">

<form method="GET" class="row mb-3">

<div class="col-md-4">

<input type="text"
name="search"
value="{{ request('search') }}"
class="form-control"
placeholder="Search name or contact">

</div>

<div class="col-md-3">

<select name="status" class="form-control select2">

<option value="">All Status</option>

<option value="active"
{{ request('status')=='active'?'selected':'' }}>
Active
</option>

<option value="inactive"
{{ request('status')=='inactive'?'selected':'' }}>
Inactive
</option>

</select>

</div>

<div class="col-md-3 d-flex gap-2">

<button class="btn btn-primary">
Filter
</button>

<a href="{{ route('staff.teachers.index') }}"
class="btn btn-light">
Reset
</a>

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

@forelse($teachers as $teacher)

<tr>

<td>
{{ $teacher->name }}

<br>

@isset($teacher->dob)
<small>DOB: {{ $teacher->dob_formatted }}</small>
@endisset

</td>

<td>{{ $teacher->contact_number }}</td>

<td>{{ $teacher->email ?? '-' }}</td>


<td>

<span class="badge
{{ $teacher->status=='active'?'bg-success':'' }}
{{ $teacher->status=='inactive'?'bg-danger':'' }}">

{{ ucfirst($teacher->status) }}

</span>

</td>


<td>

<div class="d-flex gap-2">

<a href="{{ route('staff.teachers.show',encrypt($teacher->id)) }}">
<i class="mdi mdi-eye text-primary"></i>
</a>

<a href="{{ route('staff.teachers.edit',encrypt($teacher->id)) }}">
<i class="mdi mdi-pencil text-success"></i>
</a>

<a href="#"
data-plugin="delete-data"
data-target-form="#delete_{{ $teacher->id }}">
<i class="mdi mdi-trash-can text-danger"></i>
</a>

<form id="delete_{{ $teacher->id }}"
method="POST"
action="{{ route('staff.teachers.destroy',encrypt($teacher->id)) }}">
@csrf
@method('DELETE')
</form>

</div>

</td>

</tr>

@empty

<tr>
<td colspan="5" class="text-center">No Teachers Found</td>
</tr>

@endforelse

</tbody>

</table>

{{ $teachers->links() }}

</div>
</div>

@endsection


@section('script')
<script>
$('.select2').select2();
</script>
@endsection
