@extends('staff.layouts.master')

@php
$isEdit = isset($student);
@endphp

@section('title',$isEdit?'Edit Student':'Add Student')

@section('content')

<div class="row">

<form method="POST"
action="{{ $isEdit ? route('staff.students.update',encrypt($student->id)) : route('staff.students.store') }}"
enctype="multipart/form-data">

@csrf

@if($isEdit)
@method('PUT')
@endif

<div class="col-12">

{{-- ================= STUDENT DETAILS ================= --}}
<div class="card">

<div class="card-header">
<h4 class="card-title">Student Details</h4>
<p class="card-title-desc">
{{ $isEdit ? 'Edit' : 'Enter' }} student details
</p>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">
<label>Name</label>
<input type="text"
name="name"
class="form-control"
value="{{ old('name',$student->name ?? '') }}">
</div>

<div class="col-md-6 mb-3">
<label>Contact Number</label>
<input type="text"
name="contact_number"
id="contact_number"
class="form-control @error('contact_number') is-invalid @enderror"
maxlength="15"
value="{{ old('contact_number',$student->contact_number ?? '') }}">
@error('contact_number')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
</div>

<div class="col-md-6 mb-3">
<label>WhatsApp Number</label>
<input type="text"
name="whatsapp_number"
class="form-control"
value="{{ old('whatsapp_number',$student->whatsapp_number ?? '') }}">
</div>

<div class="col-md-6 mb-3">
<label>Email</label>
<input type="email"
name="email"
class="form-control"
value="{{ old('email',$student->email ?? '') }}">
</div>

<div class="col-md-6 mb-3">
<label>Date of Birth</label>
<input type="date"
name="dob"
class="form-control"
value="{{ old('dob', isset($student) && $student->dob ? $student->dob->format('Y-m-d') : '') }}">
</div>

<div class="col-md-6 mb-3">
<label>Parent Name</label>
<input type="text"
name="parent_name"
class="form-control"
value="{{ old('parent_name',$student->parent_name ?? '') }}">
</div>

<div class="col-md-6 mb-3">
<label>Status</label>
<select name="status" class="form-control">
<option value="active"
{{ old('status',$student->status ?? '')=='active'?'selected':'' }}>
Active
</option>

<option value="passout"
{{ old('status',$student->status ?? '')=='passout'?'selected':'' }}>
Passout
</option>

<option value="dropout"
{{ old('status',$student->status ?? '')=='dropout'?'selected':'' }}>
Dropout
</option>
</select>
</div>

<div class="col-md-12 mb-3">
<label>Address</label>
<textarea name="address"
class="form-control">{{ old('address',$student->address ?? '') }}</textarea>
</div>

<div class="col-md-6 mb-3">
<label>Photo</label>
<input type="file" name="photo" class="form-control">
</div>

<div class="col-md-6 mb-3">
<label>ID Proof</label>
<input type="file" name="id_proof" class="form-control">
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

<div class="col-md-4 mb-3">
<label>Classes Per Week</label>

<input type="number"
name="classes_per_week"
id="classes_per_week"
class="form-control"
readonly
data-bs-toggle="tooltip"
data-bs-placement="top"
title="Select days first to calculate classes per week"
value="{{ old('classes_per_week',$student->classes_per_week ?? 0) }}">

</div>

<div class="col-md-4 mb-3">
<label>Time Slot</label>

<input type="text"
name="time_slot"
id="time_slot"
class="form-control"
value="{{ old('time_slot',$student->time_slot ?? '') }}">

</div>

<div class="col-md-4 mb-3">
<label>Starting Date</label>
<input type="date"
name="starting_date"
class="form-control"
value="{{ old('starting_date', isset($student) && $student->starting_date ? $student->starting_date->format('Y-m-d') : '') }}">
</div>

</div>


{{-- Selected Days --}}
<div class="row">

<div class="col-md-12 mb-3">
<label>Selected Days</label>

@php
$days = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'];
$selectedDays = old('selected_days',$student->selected_days ?? []);
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

{{-- ================= LOGIN INFORMATION ================= --}}
<div class="card">

<div class="card-header">
<h4 class="card-title">Login Information</h4>
<p class="card-title-desc">Student login credentials</p>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">
<label>Phone (Login)</label>

<input type="text"
name="phone"
id="phone"
class="form-control"
value="{{ old('phone',$student->phone ?? '') }}">

</div>


<div class="col-md-6 mb-3 {{ $isEdit ? '' : 'required' }}">
<label>Password</label>

<input type="password"
name="password"
id="password"
class="form-control">

@if($isEdit)
<small class="text-muted">
Leave blank to keep existing password
</small>
@endif

</div>

</div>

</div>

</div>



{{-- ================= ACTION BUTTONS ================= --}}
<div class="card">

<div class="card-header">

<button class="btn btn-primary" type="submit" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
{{ $isEdit?'Update Student':'Save Student' }}
</button>

<a href="{{ route('staff.students.index') }}"
class="btn btn-secondary">
Cancel
</a>

</div>

</div>

</div>

</form>

</div>

@endsection

@section('script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>

flatpickr("#time_slot", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",   // stored in DB
    altInput: true,
    altFormat: "h:i K"   // shown to user (AM/PM)
});

</script>

@if(!$isEdit)
<script>
$('#contact_number').on('keyup change', function(){

    let number = $(this).val();

    $('#phone').val(number);
    $('#password').val(number);
    $('input[name="whatsapp_number"]').val(number);

});
</script>
@endif

<script>

function updateClassesPerWeek() {

    let count = $('.class-day:checked').length;

    $('#classes_per_week').val(count);

}

$('.class-day').on('change', function () {

    updateClassesPerWeek();

});

$(document).ready(function () {

    updateClassesPerWeek();

});

</script>
<script>

var tooltipTriggerList = [].slice.call(
document.querySelectorAll('[data-bs-toggle="tooltip"]')
);

tooltipTriggerList.map(function (tooltipTriggerEl) {
return new bootstrap.Tooltip(tooltipTriggerEl);
});

</script>

<script>

$('#classes_per_week').on('click', function(){

let count = $('.class-day:checked').length;

if(count === 0){
    alert('Select days first');
}

});

</script>

@endsection
