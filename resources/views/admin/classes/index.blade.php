@extends('admin.layouts.master')
@section('title','Classes')

@section('content')

<div class="card">
<div class="card-header d-flex justify-content-between">
<h4>Classes ({{ $classes->total() }})</h4>
<a href="{{ route('admin.classes.create') }}" class="btn btn-primary">Add Class</a>
</div>

<div class="card-body table-responsive">

    <form method="GET" class="row mb-3">

    <div class="col-md-3">
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

    <div class="col-md-3">
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

    <div class="col-md-3">
    <select name="status" class="form-control">
    <option value="">All Status</option>
    <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
    <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
    </select>
    </div>

    <div class="col-md-3 d-flex gap-2">
    <button class="btn btn-primary">Filter</button>
    <a href="{{ route('admin.classes.index') }}" class="btn btn-light">Reset</a>
    </div>

    </form>

<table class="table table-bordered align-middle">
<thead>
<tr>
<th>Name</th>
<th>Course</th>
<th>Type</th>
<th>Days</th>
<th>Time</th>
<th>Fee</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>
@foreach($classes as $c)
<tr>
<td>{{ $c->name }}</td>
<td>{{ $c->course->name ?? '-' }}</td>
<td><span class="badge bg-info">{{ ucfirst($c->classType->name) }}</span></td>
<td>{{ $c->days }}</td>
<td>{{ $c->slot_time }}</td>
<td>{{ $c->monthly_fee }}</td>

<td>
<span class="badge {{ $c->is_completed?'bg-danger':'bg-success' }}">
{{ $c->is_completed?'Completed':'Active' }}
</span>
</td>

<td>
<div class="d-flex gap-2">

<a href="{{ route('admin.classes.edit',encrypt($c->id)) }}">
<i class="mdi mdi-pencil text-success"></i>
</a>

<a href="{{ route('admin.classes.changeStatus',encrypt($c->id)) }}">
<i class="fas fa-power-off text-warning"></i>
</a>

<a href="#" data-plugin="delete-data"
data-target-form="#delete_{{ $c->id }}">
<i class="mdi mdi-trash-can text-danger"></i>
</a>

<form id="delete_{{ $c->id }}"
method="POST"
action="{{ route('admin.classes.destroy',encrypt($c->id)) }}">
@csrf
<input type="hidden" name="_method" value="DELETE">
</form>

</div>
</td>
</tr>
@endforeach
</tbody>
</table>

{{ $classes->links() }}

</div>
</div>

@endsection

@section('script')
<script>
$('.select2').select2();
</script>
@endsection
