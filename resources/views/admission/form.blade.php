@extends('admin.layouts.master-without-nav')

@section('title')
@if($type=='student')
Student Admission
@else
Teacher Application
@endif
@endsection

@section('content')

@php
$expired = $lead->form_expires_at && now()->gt($lead->form_expires_at);
@endphp

<div class="auth-page">
<div class="container-fluid p-0">
<div class="row g-0">

<div class="col-xxl-3 col-lg-4 col-md-5">

<div class="auth-full-page-content d-flex p-sm-5 p-4">
<div class="w-100">

<div class="d-flex flex-column h-100">

<div class="mb-4 text-center">
<h4 class="fw-bold">
@if($type=='student')
Student Admission
@else
Teacher Application
@endif
</h4>
</div>

<div class="auth-content my-auto">

<div class="text-center mb-4">

@if($type=='student')
<h5>Complete Your Admission</h5>
@else
<h5>Complete Your Application</h5>
@endif

@if($expired)
<p class="text-danger mt-2">
This link has expired.
</p>
@elseif($lead->form_opened_at)
<p class="text-success mt-2">
Form opened successfully.
</p>
@endif

</div>

@if(!$expired)

<form class="mt-4 pt-2"
method="POST"
action="{{ route('admission.submit',[$type,$lead->form_token]) }}"
enctype="multipart/form-data">

@csrf

{{-- ================= BASIC DETAILS ================= --}}

<div class="form-floating form-floating-custom mb-4">
<input type="text"
class="form-control"
name="name"
value="{{ $lead->name }}"
required>
<label>Name</label>
<div class="form-floating-icon">
<i data-feather="user"></i>
</div>
</div>


<div class="form-floating form-floating-custom mb-4">
<input type="text"
class="form-control"
name="contact_number"
value="{{ $lead->contact_number }}"
required>
<label>Contact Number</label>
<div class="form-floating-icon">
<i data-feather="phone"></i>
</div>
</div>


<div class="form-floating form-floating-custom mb-4">
<input type="text"
class="form-control"
name="whatsapp_number"
value="{{ $lead->contact_number }}">
<label>WhatsApp Number</label>
<div class="form-floating-icon">
<i data-feather="message-circle"></i>
</div>
</div>


<div class="form-floating form-floating-custom mb-4">
<input type="email"
class="form-control"
name="email"
value="{{ $lead->email }}">
<label>Email</label>
<div class="form-floating-icon">
<i data-feather="mail"></i>
</div>
</div>


<div class="form-floating form-floating-custom mb-4">
<input type="date"
class="form-control"
name="dob">
<label>Date of Birth</label>
</div>


@if($type=='student')

<div class="form-floating form-floating-custom mb-4">
<input type="text"
class="form-control"
name="parent_name">
<label>Parent Name</label>
</div>

@endif


<div class="mb-4">
<label class="form-label">Address</label>
<textarea name="address"
class="form-control"></textarea>
</div>



{{-- ================= STUDENT CLASS DETAILS ================= --}}
@if($type=='student')

<div class="form-floating form-floating-custom mb-4">
<input type="number"
class="form-control"
name="classes_per_week">
<label>Classes Per Week</label>
</div>

<div class="form-floating form-floating-custom mb-4">
<input type="time"
class="form-control"
name="time_slot">
<label>Preferred Time Slot</label>
</div>

<div class="form-floating form-floating-custom mb-4">
<input type="date"
class="form-control"
name="starting_date">
<label>Preferred Starting Date</label>
</div>

<div class="mb-4">
<label class="form-label">Preferred Days</label>

<div class="d-flex flex-wrap gap-2">

@php
$days = ['mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun'];
@endphp

@foreach($days as $key=>$day)

<label class="form-check">
<input type="checkbox"
name="selected_days[]"
value="{{ $key }}"
class="form-check-input">

<span class="form-check-label">
{{ $day }}
</span>

</label>

@endforeach

</div>
</div>

@endif



{{-- ================= TEACHER DETAILS ================= --}}
@if($type=='teacher')

<div class="form-floating form-floating-custom mb-4">
<input type="text"
class="form-control"
name="qualification">
<label>Qualification</label>
</div>

<div class="form-floating form-floating-custom mb-4">
<input type="number"
class="form-control"
name="experience">
<label>Experience (Years)</label>
</div>

<div class="form-floating form-floating-custom mb-4">
<input type="text"
class="form-control"
name="upi_number">
<label>UPI Number</label>
</div>

@endif



{{-- ================= FILE UPLOAD ================= --}}

<div class="mb-4">
<label class="form-label">Photo</label>
<input type="file"
class="form-control"
name="photo">
</div>

<div class="mb-4">
<label class="form-label">ID Proof</label>
<input type="file"
class="form-control"
name="id_proof">
</div>


<div class="mb-3">

<button class="btn btn-zopa w-100 waves-effect waves-light"
type="submit">

@if($type=='student')
Submit Admission Form
@else
Submit Application
@endif

</button>

</div>

</form>

@else

<div class="alert alert-danger text-center">
This link is no longer valid.<br>
Please contact the institute.
</div>

@endif

</div>

<div class="mt-4 mt-md-5 text-center">
<p class="mb-0">
© <script>document.write(new Date().getFullYear())</script>
FOMS ACADEMY
</p>
</div>

</div>
</div>
</div>

</div>



{{-- RIGHT SIDE DESIGN --}}
<div class="col-xxl-9 col-lg-8 col-md-7">
<div class="auth-bg pt-md-5 p-4 d-flex">

<div class="bg-overlay"></div>

<ul class="bg-bubbles">
<li></li><li></li><li></li><li></li><li></li>
<li></li><li></li><li></li><li></li><li></li>
</ul>

<div class="row justify-content-center align-items-end">
<div class="col-xl-7">

<div class="p-0 p-sm-4 px-xl-0">

<div class="carousel-inner">

<div class="carousel-item active">

<div class="testi-contain text-center text-white">

<i class="bx bxs-quote-alt-left text-success display-6"></i>

@if($type=='student')

<h4 class="mt-4 fw-medium lh-base text-white">
“Welcome to FOMS Academy. Complete your admission details to begin your learning journey.”
</h4>

@else

<h4 class="mt-4 fw-medium lh-base text-white">
“Join FOMS Academy and help shape the future of students.”
</h4>

@endif

</div>

</div>

</div>

</div>
</div>
</div>

</div>

</div>

</div>
</div>
</div>

@endsection


@section('script')

<script>

$('input[name="contact_number"]').on('keyup change', function(){

let number = $(this).val();

$('input[name="whatsapp_number"]').val(number);

});

</script>

@endsection
