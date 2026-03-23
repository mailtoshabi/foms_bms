@extends('staff.layouts.master')

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
action="{{ isset($class)?route('staff.class_rooms.update'):route('staff.class_rooms.store') }}">
@csrf

@if(isset($class))
<input type="hidden" name="class_room_id" value="{{ encrypt($class->id) }}">
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

{{-- <div class="col-sm-6 mb-3">
<label>Classes Per Week</label>
<input type="number" name="classes_per_week" class="form-control"
value="{{ $class->classes_per_week ?? 0 }}">
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
</div> --}}

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

</div>
</div>
</div>

{{-- ================= CLASS SCHEDULE ================= --}}
<div class="card">

<div class="card-header">
<h4 class="card-title">Class Schedule</h4>
<p class="card-title-desc">Student class timing and schedule</p>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-3 mb-3">
<label>Classes Per Week</label>

<input type="number"
name="classes_per_week"
id="classes_per_week"
class="form-control"
readonly
data-bs-toggle="tooltip"
data-bs-placement="top"
title="Select days first to calculate classes per week"
value="{{ old('classes_per_week',$class->classes_per_week ?? 0) }}">

</div>

<div class="col-md-3 mb-3">
<label>Time Slot</label>

<input type="text"
name="time_slot"
id="time_slot"
class="form-control"
value="{{ old('time_slot',$class->time_slot ?? '') }}">

</div>



<div class="col-md-3 mb-3">
<label>Duration (Minutes)</label>
<input type="number" name="slot_duration" class="form-control"
value="{{ $class->slot_duration ?? '' }}">
</div>

<div class="col-md-3 mb-3">
<label>Starting Date</label>
<input type="date"
name="starting_date"
class="form-control"
value="{{ old('starting_date', isset($class) && $class->starting_date ? $class->starting_date->format('Y-m-d') : '') }}">
</div>

</div>


{{-- Selected Days --}}
<div class="row">

<div class="col-md-12 mb-3">
<label>Selected Days</label>

@php
$days = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'];
$selectedDays = old('selected_days',$class->selected_days ?? []);
@endphp

<div class="d-flex flex-wrap gap-3">

@foreach($days as $key => $day)

<label class="form-check">
<input type="checkbox"
name="selected_days[]"
value="{{ $key }}"
class="form-check-input class-day"
{{ in_array($key,$selectedDays ?? []) ? 'checked' : '' }}>

<span class="form-check-label">
{{ $day }}
</span>
</label>

@endforeach

</div>

</div>

</div>

</div>
</div>

<div class="card">
<div class="card-header">
<button class="btn btn-primary">Save</button>
<a href="{{ route('staff.class_rooms.index') }}" class="btn btn-secondary">Cancel</a>
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
