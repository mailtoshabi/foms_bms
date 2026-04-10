@extends('admin.layouts.master')
@section('title')
    @if(isset($user)) @lang('translation.Edit_User') @else @lang('translation.Add_User') @endif
@endsection

@section('css')
<link href="{{ URL::asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')

@component('admin.breadcrumbs.breadcrumb')
    @slot('li_1') @lang('translation.Account_Manage') @endslot
    @slot('li_2') @lang('translation.User_Management') @endslot
    @slot('title')
        @if(isset($user)) @lang('translation.Edit_User') @else @lang('translation.Add_User') @endif
    @endslot
@endcomponent

<div class="row">
<form method="POST"
      action="{{ isset($user) ? route('admin.users.update') : route('admin.users.store') }}">
    @csrf

    @if(isset($user))
        <input type="hidden" name="user_id" value="{{ encrypt($user->id) }}">
        <input type="hidden" name="_method" value="PUT">
    @endif

    <div class="col-12">

        {{-- ================= User Details ================= --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">User Details</h4>
                <p class="card-title-desc required">
                    {{ isset($user) ? 'Edit' : 'Enter' }} user details.
                    Fields marked with <label></label> are mandatory.
                </p>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-sm-6">

                        <div class="mb-3 required">
                            <label for="name">Name</label>
                            <input id="name" name="name" type="text" class="form-control"
                                   placeholder="Full Name"
                                   value="{{ isset($user) ? $user->name : old('name') }}">
                            @error('name') <p class="text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-3 required">
                            <label for="phone">Phone</label>
                            <input id="phone" name="phone" type="text" class="form-control"
                                   placeholder="Login Phone"
                                   value="{{ isset($user) ? $user->phone : old('phone') }}">
                            @error('phone') <p class="text-danger">{{ $message }}</p> @enderror
                        </div>

                        {{-- <div class="mb-3">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-control">
                                <option value="super_admin"
                                    {{ (isset($user) && $user->role == 'super_admin') ? 'selected' : '' }}>
                                    Super Admin
                                </option>
                                <option value="employee"
                                    {{ (isset($user) && $user->role == 'employee') ? 'selected' : '' }}>
                                    Employee
                                </option>
                            </select>
                            @error('role') <p class="text-danger">{{ $message }}</p> @enderror
                        </div> --}}

                    </div>

                    <div class="col-sm-6">

                        <div class="mb-3 {{ isset($user) ? '' : 'required' }}">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control"
                                   placeholder="Enter Password">
                            @error('password') <p class="text-danger">{{ $message }}</p> @enderror

                            @if(isset($user))
                                <small class="text-muted">
                                    Leave blank to keep existing password
                                </small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control select2">
                                <option value="1"
                                    {{ (isset($user) && $user->status) || old('status') == 1 ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="0"
                                    {{ (isset($user) && !$user->status) || old('status') == 0 ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        {{-- ================= Actions ================= --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
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
