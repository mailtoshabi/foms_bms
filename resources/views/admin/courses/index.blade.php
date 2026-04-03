@extends('admin.layouts.master')
@section('title') Course List @endsection

@section('content')

@component('admin.breadcrumbs.breadcrumb')
@slot('li_1') Course Manage @endslot
@slot('li_2') Courses @endslot
@slot('title') Course List @endslot
@endcomponent

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
<div class="card-body">

<div class="d-flex justify-content-between mb-3">
<h5>Course List ({{ $courses->total() }})</h5>

<a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
<i class="fas fa-plus"></i> Add Course
</a>
</div>

{{-- ================= Filters ================= --}}
<form method="GET" class="card p-3 mb-3">
<div class="row">

<div class="col-md-4">
<input type="text"
       name="name"
       value="{{ request('name') }}"
       class="form-control"
       placeholder="Search by Course Name">
</div>

<div class="col-md-4">
<select name="category_id" class="form-control select2">
<option value="">All Categories</option>

@foreach($categories as $category)
<option value="{{ $category->id }}"
@if(request('category_id')==$category->id) selected @endif>
{{ ucfirst($category->name) }}
</option>
@endforeach

</select>
</div>

<div class="col-md-2 d-grid">
<button class="btn btn-primary">
<i class="fas fa-search"></i> Filter
</button>
</div>

<div class="col-md-2 d-grid">
<a href="{{ route('admin.courses.index') }}" class="btn btn-light">
Reset
</a>
</div>

</div>
</form>

<table class="table table-bordered">
<thead>
<tr>
<th>Category</th>
<th>Name</th>
{{-- <th>Fee</th> --}}
<th>Created</th>
<th>Action</th>
</tr>
</thead>

<tbody>
@foreach($courses as $course)
<tr>

<td>
<span class="badge bg-info">{{ ucfirst($course->category->name) }}</span>
</td>

<td>{{ $course->name }}</td>

{{-- <td>{{ number_format($course->course_fee,2) }}</td> --}}

<td>{{ $course->created_at->format('d M Y') }}</td>

<td class="text-nowrap">

<div class="d-flex gap-2">

<a href="{{ route('admin.courses.edit',encrypt($course->id)) }}">
<i class="mdi mdi-pencil text-success"></i>
</a>

<a href="#" data-plugin="delete-data"
data-target-form="#delete_{{ $course->id }}">
<i class="mdi mdi-trash-can text-danger"></i>
</a>

<form id="delete_{{ $course->id }}" method="POST"
action="{{ route('admin.courses.destroy',encrypt($course->id)) }}">
@csrf
<input type="hidden" name="_method" value="DELETE">
</form>

</div>

</td>

</tr>
@endforeach
</tbody>
</table>

{{ $courses->links() }}

</div>
</div>

@endsection

@section('script')
<script>
$('.select2').select2();
</script>
@endsection
