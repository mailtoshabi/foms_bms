@extends('admin.layouts.master-without-nav')

@section('title')
Welcome
@endsection

@section('content')

<div class="auth-page">
<div class="container-fluid p-0">

<div class="row g-0">

<div class="col-xxl-4 col-lg-5 col-md-6 mx-auto">

<div class="auth-full-page-content d-flex p-sm-5 p-4">
<div class="w-100">

<div class="d-flex flex-column h-100">

<div class="mb-5 text-center">
<h3 class="fw-bold">FOMS Academy</h3>
<p class="text-muted">Choose your portal</p>
</div>

<div class="auth-content my-auto">

<div class="row g-4">

{{-- STUDENT LOGIN --}}
<div class="col-12">
<a href="{{ route('student.login') }}" class="text-decoration-none">

<div class="card shadow-sm border-0 portal-card">

<div class="card-body text-center py-4">

<i class="mdi mdi-school font-size-36 text-primary mb-3"></i>

<h5 class="mb-1">Student Portal</h5>

<p class="text-muted mb-0">
Login to view class_rooms, attendance & fees
</p>

</div>

</div>

</a>
</div>


{{-- TEACHER LOGIN --}}
<div class="col-12">
<a href="{{ route('teacher.login') }}" class="text-decoration-none">

<div class="card shadow-sm border-0 portal-card">

<div class="card-body text-center py-4">

<i class="mdi mdi-account-tie font-size-36 text-success mb-3"></i>

<h5 class="mb-1">Teacher Portal</h5>

<p class="text-muted mb-0">
Manage class_rooms, students & attendance
</p>

</div>

</div>

</a>
</div>

</div>

</div>

<div class="mt-5 text-center">
<p class="mb-0">
© <script>document.write(new Date().getFullYear())</script>
FOMS Academy
</p>
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

<style>

.portal-card{
transition: all .25s ease;
cursor:pointer;
}

.portal-card:hover{
transform: translateY(-5px);
box-shadow:0 10px 25px rgba(0,0,0,.1);
}

</style>

@endsection
