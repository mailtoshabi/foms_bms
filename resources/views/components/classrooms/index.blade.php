@props([
    'class_rooms',
    'courses',
    'types',
    'createRoute',
    'indexRoute',
    'editRoute',
    'deleteRoute',
    'showRoute'
])

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">

<div class="card-header d-flex justify-content-between">

<h4>Classes ({{ $class_rooms->total() }})</h4>

<a href="{{ $createRoute }}" class="btn btn-primary">
Add Class
</a>

</div>


<div class="card-body table-responsive">

{{-- ================= FILTER ================= --}}
<form method="GET" class="row mb-3">

<div class="col-md-2">

<select name="course_id" class="form-control select2">

<option value="">All Courses</option>

@foreach($courses as $course)

<option value="{{ $course->id }}"
{{ request('course_id')==$course->id?'selected':'' }}>

{{ $course->name }}

</option>

@endforeach

</select>

</div>


<div class="col-md-2">

<select name="class_type_id" class="form-control select2">

<option value="">All Types</option>

@foreach($types as $type)

<option value="{{ $type->id }}"
{{ request('class_type_id')==$type->id?'selected':'' }}>

{{ ucfirst($type->name) }}

</option>

@endforeach

</select>

</div>


<div class="col-md-2">

<select name="status" class="form-control">

<option value="">All Status</option>

<option value="active"
{{ request('status')=='active'?'selected':'' }}>
Active
</option>

<option value="completed"
{{ request('status')=='completed'?'selected':'' }}>
Completed
</option>

</select>

</div>

<div class="col-md-3">

<input type="text" name="name" value="{{ request('name') }}" class="form-control" placeholder="Search Class Name...">

</div>


<div class="col-md-3 d-flex gap-2">

<button class="btn btn-primary px-3">
Filter
</button>

<a href="{{ $indexRoute }}" class="btn btn-light px-3">
Reset
</a>

</div>

</form>



{{-- ================= TABLE ================= --}}

<table class="table table-bordered align-middle">

<thead>

<tr>
<th>Course</th>
<th>Class</th>
<th>Type</th>
<th>Schedule</th>
<th>Fees</th>
<th>Status</th>
<th>Action</th>
</tr>

</thead>


<tbody>

@forelse($class_rooms as $class)

<tr>

<td>{{ $class->course->name ?? '-' }}</td>

<td>{{ $class->name }}</td>

<td>{{ ucfirst($class->classType->name ?? '-') }}</td>

<td>

@if($class->selected_days)



{{ implode(', ', $class->selected_days ?? []) }}
<small>
<br>

{{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}

</small>

@endif

</td>


<td>

{{ $class->classType->id == 1 ? 'First Month Fee' : 'Admission Fee' }}: ₹{{ number_format($class->admission_fee,2) }} <br>

Monthly: ₹{{ number_format($class->monthly_fee,2) }}

</td>


<td>

<span class="badge {{ $class->is_completed ? 'bg-secondary' : 'bg-success' }}">

{{ $class->is_completed ? 'Completed' : 'Active' }}

</span>

</td>


<td>

<div class="d-flex gap-2">

{{-- <a href="{{ route('staff.class_rooms.show', encrypt($class->id)) }}">
<i class="fas fa-eye"></i>
</a> --}}

<a href="{{ $showRoute(encrypt($class->id)) }}">
<i class="fas fa-eye"></i>
</a>

<a href="{{ $editRoute(encrypt($class->id)) }}">
<i class="mdi mdi-pencil text-success"></i>
</a>

@php
    $canDelete = false;
    if (auth('admin')->check()) {
        $canDelete = true;
    } elseif (auth('staff')->check()) {
        $staff = auth('staff')->user();
        if ($staff->hasRoleId(utility('id_operation_dept'))) {
            $canDelete = true;
        }
    }
@endphp

@if($canDelete)
<a href="#"
data-plugin="delete-data"
data-target-form="#delete_{{ $class->id }}">
<i class="mdi mdi-trash-can text-danger"></i>
</a>

<form id="delete_{{ $class->id }}"
method="POST"
action="{{ $deleteRoute(encrypt($class->id)) }}">

@csrf
@method('DELETE')

</form>
@endif

</div>

</td>

</tr>

@empty

<tr>
<td colspan="7" class="text-center">
No Classes Found
</td>
</tr>

@endforelse

</tbody>

</table>


{{ $class_rooms->links() }}

</div>

</div>
