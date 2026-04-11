<div class="row">

<form method="POST" action="{{ $formAction }}">
@csrf

@if(isset($class))
<input type="hidden" name="class_room_id" value="{{ encrypt($class->id) }}">
<input type="hidden" name="_method" value="PUT">
@endif

<div class="col-12">

{{-- ================= CLASS DETAILS ================= --}}
<div class="card">

<div class="card-header">
<h4>Class Details</h4>
</div>

<div class="card-body">

<div class="row">

<div class="col-sm-6 mb-3">
<label>Course</label>

<select name="course_id" class="form-control select2">
<option value="">Select Course</option>
@foreach($courses as $c)

<option value="{{ $c->id }}"
{{ isset($class) && $class->course_id==$c->id ? 'selected':'' }}>

{{ $c->name }}

</option>

@endforeach

</select>

</div>


<div class="col-sm-6 mb-3">

<label>Class Type</label>

<select name="class_type_id" class="form-control select2">
<option value="">Select Class Type</option>
@foreach($types as $t)

<option value="{{ $t->id }}"
data-name="{{ strtolower($t->name) }}"
{{ isset($class) && $class->class_type_id==$t->id ? 'selected':'' }}>

{{ ucfirst($t->name) }}

</option>

@endforeach

</select>

</div>


<div class="col-sm-6 mb-3">

<label>Name</label>

<input type="text"
name="name"
class="form-control"
value="{{ $class->name ?? old('name') }}">

</div>


<div class="col-sm-6 mb-3">

<label id="admission-fee-label">
{{ (isset($class) && $class->class_type_id == 1) ? 'First Month Fee' : 'Admission Fee' }}
</label>

<input type="number"
step="0.01"
name="admission_fee"
class="form-control"
value="{{ $class->admission_fee ?? 0 }}">

</div>


<div class="col-sm-6 mb-3">

<label>Monthly Fee</label>

<input type="number"
step="0.01"
name="monthly_fee"
class="form-control"
value="{{ $class->monthly_fee ?? 0 }}">

</div>

</div>

</div>

</div>



{{-- ================= CLASS SCHEDULE ================= --}}
<div class="card">

<div class="card-header">

<h4 class="card-title">Class Schedule</h4>
<p class="card-title-desc">
Student class timing and schedule
</p>

</div>

<div class="card-body">

{{-- Selected Days --}}

<div class="row">

<div class="col-md-12 mb-5">

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

<input type="number"
name="slot_duration"
class="form-control"
value="{{ $class->slot_duration ?? '' }}">

</div>



</div>





</div>

</div>



{{-- ================= ACTIONS ================= --}}

<div class="card">

<div class="card-header">

<button class="btn btn-primary" type="submit" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">

{{ isset($class) ? 'Update' : 'Save' }}

</button>

<a href="{{ $indexRoute }}" class="btn btn-secondary">
Cancel
</a>

</div>

</div>


</div>

</form>

</div>


