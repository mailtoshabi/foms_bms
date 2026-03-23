@extends('staff.layouts.master')

@php
$isEdit = isset($teacher);
@endphp

@section('title',$isEdit?'Edit Teacher':'Add Teacher')

@section('content')

<div class="row">

<form method="POST"
action="{{ $isEdit ? route('staff.teachers.update',$teacher->id) : route('staff.teachers.store') }}"
enctype="multipart/form-data">

@csrf

@if($isEdit)
@method('PUT')
@endif


<div class="col-12">


{{-- ================= TEACHER DETAILS ================= --}}
<div class="card">

<div class="card-header">
<h4 class="card-title">Teacher Details</h4>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">
<label>Name</label>
<input type="text"
name="name"
class="form-control"
value="{{ old('name',$teacher->name ?? '') }}">
</div>


<div class="col-md-6 mb-3">
<label>Contact Number</label>
<input type="text"
name="contact_number"
id="contact_number"
class="form-control"
value="{{ old('contact_number',$teacher->contact_number ?? '') }}">
</div>


<div class="col-md-6 mb-3">
<label>WhatsApp Number</label>
<input type="text"
name="whatsapp_number"
class="form-control"
value="{{ old('whatsapp_number',$teacher->whatsapp_number ?? '') }}">
</div>


<div class="col-md-6 mb-3">
<label>Email</label>
<input type="email"
name="email"
class="form-control"
value="{{ old('email',$teacher->email ?? '') }}">
</div>


<div class="col-md-6 mb-3">
<label>Date of Birth</label>
<input type="date"
name="dob"
class="form-control"
value="{{ old('dob', isset($teacher) && $teacher->dob ? $teacher->dob->format('Y-m-d') : '') }}">
</div>


<div class="col-md-6 mb-3">
<label>Qualification</label>
<input type="text"
name="qualification"
class="form-control"
value="{{ old('qualification',$teacher->qualification ?? '') }}">
</div>


<div class="col-md-6 mb-3">
<label>Experience (Years)</label>
<input type="number"
name="experience"
class="form-control"
value="{{ old('experience',$teacher->experience ?? '') }}">
</div>


<div class="col-md-6 mb-3">
<label>UPI Number</label>
<input type="text"
name="upi_number"
class="form-control"
value="{{ old('upi_number',$teacher->upi_number ?? '') }}">
</div>


<div class="col-md-12 mb-3">
<label>Address</label>
<textarea name="address"
class="form-control">{{ old('address',$teacher->address ?? '') }}</textarea>
</div>


<div class="col-md-6 mb-3">
<label>Photo</label>
<input type="file"
name="photo"
class="form-control">
</div>


<div class="col-md-6 mb-3">
<label>ID Proof</label>
<input type="file"
name="id_proof"
class="form-control">
</div>


</div>
</div>
</div>



{{-- ================= LOGIN INFORMATION ================= --}}
<div class="card">

<div class="card-header">
<h4 class="card-title">Login Information</h4>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label>Phone (Login)</label>

<input type="text"
name="phone"
id="phone"
class="form-control"
value="{{ old('phone',$teacher->phone ?? '') }}">

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

<button class="btn btn-primary">
{{ $isEdit?'Update Teacher':'Save Teacher' }}
</button>

<a href="{{ route('staff.teachers.index') }}"
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

@endsection
