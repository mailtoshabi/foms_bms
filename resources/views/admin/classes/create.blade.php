@extends('admin.layouts.master')

@section('title')
@if(isset($class)) Edit Class @else Add Class @endif
@endsection

@section('css')
<link href="{{ URL::asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')

@component('admin.breadcrumbs.breadcrumb')
@slot('li_1') Academics @endslot
@slot('li_2') Classes @endslot
@slot('title') @if(isset($class)) Edit Class @else Add Class @endif @endslot
@endcomponent

<div class="row">
<form method="POST"
action="{{ isset($class)?route('admin.classes.update'):route('admin.classes.store') }}">
@csrf

@if(isset($class))
<input type="hidden" name="class_id" value="{{ encrypt($class->id) }}">
<input type="hidden" name="_method" value="PUT">
@endif

<div class="col-12">

<div class="card">
<div class="card-header"><h4>Class Details</h4></div>
<div class="card-body">

<div class="row">

<div class="col-sm-6 mb-3">
<label>Course</label>
<select name="course_id" class="form-control select2">
@foreach($courses as $c)
<option value="{{ $c->id }}"
{{ isset($class)&&$class->course_id==$c->id?'selected':'' }}>
{{ $c->name }}
</option>
@endforeach
</select>
</div>

<div class="col-sm-6 mb-3">
<label>Class Type</label>
<select name="class_type_id" class="form-control select2">
@foreach($types as $t)
<option value="{{ $t->id }}"
{{ isset($class)&&$class->class_type_id==$t->id?'selected':'' }}>
{{ ucfirst($t->name) }}
</option>
@endforeach
</select>
</div>

<div class="col-sm-6 mb-3">
<label>Name</label>
<input type="text" name="name" class="form-control"
value="{{ $class->name ?? old('name') }}">
</div>

<div class="col-sm-6 mb-3">
<label>Slots Per Week</label>
<input type="number" name="slots_per_week" class="form-control"
value="{{ $class->slots_per_week ?? 0 }}">
</div>

<div class="col-sm-6 mb-3">
<label>Days</label>
<input type="text" name="days" class="form-control"
placeholder="mon,wed,fri"
value="{{ $class->days ?? '' }}">
</div>

<div class="col-sm-6 mb-3">
<label>Start Time</label>
<input type="time" name="slot_time" class="form-control"
value="{{ $class->slot_time ?? '' }}">
</div>

<div class="col-sm-6 mb-3">
<label>Duration (Minutes)</label>
<input type="number" name="slot_duration" class="form-control"
value="{{ $class->slot_duration ?? '' }}">
</div>

<div class="col-sm-6 mb-3">
<label>Admission Fee</label>
<input type="number" step="0.01" name="admission_fee" class="form-control"
value="{{ $class->admission_fee ?? 0 }}">
</div>

<div class="col-sm-6 mb-3">
<label>Monthly Fee</label>
<input type="number" step="0.01" name="monthly_fee" class="form-control"
value="{{ $class->monthly_fee ?? 0 }}">
</div>

<div class="col-sm-6 mb-3">
<label>Start Date</label>
<input type="date" name="start_date" class="form-control"
value="{{ $class->start_date ?? '' }}">
</div>

</div>
</div>
</div>

<div class="card">
<div class="card-header">
<button class="btn btn-primary">Save</button>
<a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">Cancel</a>
</div>
</div>

</div>
</form>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('assets/libs/select2/select2.min.js') }}"></script>
<script>$('.select2').select2();</script>
@endsection
