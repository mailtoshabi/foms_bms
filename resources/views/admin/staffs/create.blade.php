@extends('admin.layouts.master')

@section('title')
@if(isset($staff)) Edit Staff @else Add Staff @endif
@endsection

@section('css')
<link href="{{ URL::asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')

@component('admin.breadcrumbs.breadcrumb')
@slot('li_1') Account Manage @endslot
@slot('li_2') Staff Manage @endslot
@slot('title')
@if(isset($staff)) Edit Staff @else Add Staff @endif
@endslot
@endcomponent

<div class="row">
<form method="POST"
      enctype="multipart/form-data"
      action="{{ isset($staff)?route('admin.staffs.update'):route('admin.staffs.store') }}">
@csrf

@if(isset($staff))
<input type="hidden" name="staff_id" value="{{ encrypt($staff->id) }}">
<input type="hidden" name="_method" value="PUT">
@endif

<div class="col-12">

{{-- ================= Staff Details ================= --}}
<div class="card">
<div class="card-header">
<h4 class="card-title">Staff Details</h4>
<p class="card-title-desc required">
{{ isset($staff)?'Edit':'Enter' }} staff details.
</p>
</div>

<div class="card-body">
<div class="row">

<div class="col-sm-6">

<div class="mb-3 required">
<label>Name</label>
<input name="name" class="form-control"
value="{{ $staff->name ?? old('name') }}">
</div>

<div class="mb-3">
<label>Email (Optional)</label>
<input name="email" class="form-control"
value="{{ $staff->email ?? old('email') }}">
</div>

<div class="mb-3">
<label>Address</label>
<textarea rows="5" name="address" class="form-control">{{ $staff->address ?? old('address') }}</textarea>
</div>

</div>

<div class="col-sm-6">

<div class="mb-3">
<label>GPay Number</label>
<input name="gpay_number" class="form-control"
value="{{ $staff->gpay_number ?? old('gpay_number') }}">
</div>

<div class="mb-3">
<label>ID Proof</label>
<input type="file" name="id_proof" class="form-control">
</div>

<div class="mb-3">
<label>Photo</label>
<input type="file" name="photo" class="form-control">
</div>

<div class="mb-3">
<label>Departments / Roles</label>
<select name="roles[]" multiple class="form-control select2">
@foreach($roles as $role)
<option value="{{ $role->id }}"
@if(isset($staff) && $staff->roles->pluck('id')->contains($role->id)) selected @endif>
{{ ucfirst($role->name) }}
</option>
@endforeach
</select>
</div>

</div>

</div>
</div>
</div>

{{-- ================= Login Information ================= --}}
<div class="card">
<div class="card-header">
<h4 class="card-title">Login Information</h4>
<p class="card-title-desc">Fill login details</p>
</div>

<div class="card-body">
<div class="row">

<div class="col-sm-6 required">
<label>Phone (Login)</label>
<input name="phone" class="form-control"
value="{{ $staff->phone ?? old('phone') }}">
</div>

<div class="col-sm-6 {{ isset($staff)?'':'required' }}">
<label>Password</label>
<input type="password" name="password" class="form-control">
@if(isset($staff))
<small class="text-muted">Leave blank to keep existing password</small>
@endif
</div>

</div>
</div>
</div>

{{-- ================= Actions ================= --}}
<div class="card">
<div class="card-header">
<button class="btn btn-primary" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">Save Changes</button>
<a href="{{ route('admin.staffs.index') }}" class="btn btn-secondary">Cancel</a>
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
