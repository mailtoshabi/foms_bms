@extends('admin.layouts.master')

@section('title')
@if(isset($course)) Edit Course @else Add Course @endif
@endsection

@section('css')
<link href="{{ URL::asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')

@component('admin.breadcrumbs.breadcrumb')
@slot('li_1') Course Manage @endslot
@slot('li_2') Courses @endslot
@slot('title')
@if(isset($course)) Edit Course @else Add Course @endif
@endslot
@endcomponent


<div class="row">
<form method="POST"
      action="{{ isset($course) ? route('admin.courses.update') : route('admin.courses.store') }}">
@csrf

@if(isset($course))
<input type="hidden" name="course_id" value="{{ encrypt($course->id) }}">
<input type="hidden" name="_method" value="PUT">
@endif

<div class="col-12">

{{-- ================= Course Details ================= --}}
<div class="card">
<div class="card-header">
<h4 class="card-title">Course Details</h4>
<p class="card-title-desc required">
{{ isset($course)?'Edit':'Enter' }} course details
</p>
</div>

<div class="card-body">
<div class="row">

<div class="col-sm-6">

{{-- ================= Category ================= --}}
<div class="mb-3 required">
<label>Category</label>

<select name="category_id" class="form-control select2 @error('category_id') is-invalid @enderror">
<option value="">Select Category</option>

@foreach($categories as $category)
<option value="{{ $category->id }}"
@if( (isset($course) && $course->category_id == $category->id)
    || old('category_id') == $category->id ) selected @endif>

{{ ucfirst($category->name) }}

</option>
@endforeach

</select>

@error('category_id')
<div class="invalid-feedback">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-sm-6">

{{-- ================= Course Name ================= --}}
<div class="mb-3 required">
<label>Course Name</label>
<input type="text"
       name="name"
       class="form-control @error('name') is-invalid @enderror"
       placeholder="Course Name"
       value="{{ old('name', $course->name ?? '') }}">

@error('name')
<div class="invalid-feedback">{{ $message }}</div>
@enderror
</div>


{{-- ================= Course Fee ================= --}}
{{-- <div class="mb-3">
<label>Course Fee</label>
<input type="number"
       step="0.01"
       name="course_fee"
       class="form-control"
       placeholder="0.00"
       value="{{ $course->course_fee ?? old('course_fee') }}">

@error('course_fee')
<p class="text-danger">{{ $message }}</p>
@enderror
</div> --}}

</div>

</div>
</div>
</div>


{{-- ================= Actions ================= --}}
<div class="card">
<div class="card-header">
<div class="d-flex flex-wrap gap-2">
<button type="submit" class="btn btn-primary waves-effect waves-light" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
Save Changes
</button>

<a href="{{ route('admin.courses.index') }}"
   class="btn btn-secondary waves-effect waves-light">
Cancel
</a>
</div>
</div>
</div>

</div>
</form>
</div>

@endsection


@section('script')
<script src="{{ URL::asset('assets/libs/select2/select2.min.js') }}"></script>

<script>
$('.select2').select2();
</script>
@endsection
