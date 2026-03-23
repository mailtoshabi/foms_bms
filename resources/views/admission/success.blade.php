@extends('admin.layouts.master-without-nav')

@section('title')
{{ $type=='student' ? 'Admission Completed' : 'Application Submitted' }}
@endsection


@section('content')

<div class="auth-page">
<div class="container-fluid p-0">
<div class="row g-0">


<div class="col-xxl-3 col-lg-4 col-md-5">

<div class="auth-full-page-content d-flex p-sm-5 p-4">
<div class="w-100">

<div class="d-flex flex-column h-100">


<div class="mb-4 text-center">

<h4 class="fw-bold text-success">

<i class="mdi mdi-check-circle-outline"></i>

{{ $type=='student' ? 'Admission Successful' : 'Application Submitted' }}

</h4>

</div>


<div class="auth-content my-auto text-center">

<h5 class="mb-3">Thank You!</h5>


@if($type=='student')

<p class="text-muted">
Your admission details have been submitted successfully.
</p>

@else

<p class="text-muted">
Your teacher application has been submitted successfully.
</p>

@endif



@if($whatsappUrl)

<a href="{{ $whatsappUrl }}"
target="_blank"
class="btn btn-success">

<i class="fab fa-whatsapp"></i>
Send Login via WhatsApp

</a>

@endif



<div class="alert alert-success mt-3">

@if($type=='student')

Our team will contact you soon regarding the next steps.

@else

Our team will review your application and contact you soon.

@endif

</div>



<a href="/"
class="btn btn-zopa w-100 waves-effect waves-light mt-3">

Back to Home

</a>

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




<div class="col-xxl-9 col-lg-8 col-md-7">

<div class="auth-bg pt-md-5 p-4 d-flex">

<div class="bg-overlay"></div>

<ul class="bg-bubbles">
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
</ul>


<div class="row justify-content-center align-items-end">
<div class="col-xl-7">

<div class="p-0 p-sm-4 px-xl-0">

<div class="testi-contain text-center text-white">

<i class="bx {{ $type=='student' ? 'bxs-graduation' : 'bxs-user-check' }} text-success display-6"></i>


@if($type=='student')

<h4 class="mt-4 fw-medium lh-base text-white">
“Welcome to FOMS Academy. Your learning journey begins here.”
</h4>

<p class="text-white-50 mt-3">
We are excited to have you as part of our academy.
</p>

@else

<h4 class="mt-4 fw-medium lh-base text-white">
“Thank you for applying to teach at FOMS Academy.”
</h4>

<p class="text-white-50 mt-3">
Our team will review your application soon.
</p>

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

@endsection
