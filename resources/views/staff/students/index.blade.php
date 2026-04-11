@extends('staff.layouts.master')
@section('title','Students')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
<div class="card-header d-flex justify-content-between">
<h4>Students ({{ $students->total() }})</h4>

<a href="{{ route('staff.students.create') }}" class="btn btn-primary">
Add Student
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
<option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
<option value="passout" {{ request('status')=='passout'?'selected':'' }}>Passout</option>
<option value="dropout" {{ request('status')=='dropout'?'selected':'' }}>Dropout</option>
</select>
</div>

<div class="col-md-3 d-flex gap-2">
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

<td>{{ $student->name }}<br>
    @isset($student->dob)
        <br><small>DOB: {{ $student->dob_formatted }}</small>
    @endisset
</td>
<td>{{ $student->contact_number }}</td>
<td>{{ $student->email ?? '-' }}</td>

<td>
<span class="badge
{{ $student->status=='active'?'bg-success':'' }}
{{ $student->status=='passout'?'bg-info':'' }}
{{ $student->status=='dropout'?'bg-danger':'' }}">
{{ ucfirst($student->status) }}
</span>
</td>

<td>

<div class="d-flex gap-2">

<a href="{{ route('staff.students.show',encrypt($student->id)) }}">
<i class="mdi mdi-eye text-primary"></i>
</a>

<a href="{{ route('staff.students.edit',encrypt($student->id)) }}">
<i class="mdi mdi-pencil text-success"></i>
</a>

<a href="#"
data-plugin="delete-student"
data-check-url="{{ route('staff.students.check_related', encrypt($student->id)) }}"
data-target-form="#delete_{{ $student->id }}">
<i class="mdi mdi-trash-can text-danger"></i>
</a>

<form id="delete_{{ $student->id }}"
method="POST"
action="{{ route('staff.students.destroy',encrypt($student->id)) }}">
@csrf
@method('DELETE')
</form>

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

$(document).on('click', '[data-plugin="delete-student"]', function(e) {
    e.preventDefault();
    var checkUrl   = $(this).data('check-url');
    var targetForm = $(this).data('target-form');

    $.get(checkUrl, function(data) {
        var lines = [];
        if (data.fees        > 0) lines.push('<li>' + data.fees        + ' fee record(s)</li>');
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
        }).then(function(result) {
            if (result.isConfirmed) {
                $(targetForm).submit();
            }
        });
    });
});
</script>
@endsection
