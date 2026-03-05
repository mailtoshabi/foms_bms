@extends('admin.layouts.master')
@section('title') @lang('translation.UserList') @endsection

@section('css')
<link href="{{ URL::asset('/assets/libs/datatables.net-bs4/datatables.net-bs4.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('assets/libs/datatables.net-responsive-bs4/datatables.net-responsive-bs4.min.css') }}" rel="stylesheet">
@endsection

@section('content')

@component('admin.breadcrumbs.breadcrumb')
    @slot('li_1') @lang('translation.Account_Manage') @endslot
    @slot('li_2') @lang('translation.User_Management') @endslot
    @slot('title') @lang('translation.UserList') @endslot
@endcomponent

@if(session()->has('success'))
<div class="alert alert-success alert-top-border alert-dismissible fade show">
    <strong>Success</strong> - {{ session('success') }}
</div>
@endif

<div class="row">
<div class="col-lg-12">
<div class="card">
<div class="card-body">

<h5 class="card-title mb-4">
    User List
    <span class="text-muted fw-normal ms-2">({{ $users->total() }})</span>
</h5>

<div class="table-responsive">
<table class="table align-middle dt-responsive nowrap">

    <thead>
    <tr>
        <th>Name</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Status</th>
        <th>Created</th>
        <th style="width:80px;">Action</th>
    </tr>
    </thead>

    <tbody>
    @forelse($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->phone }}</td>
            <td>
                <span class="badge badge-soft-primary">
                    {{ ucfirst(str_replace('_',' ', $user->role)) }}
                </span>
            </td>
            <td>
                <span class="badge badge-soft-{{ $user->status ? 'success' : 'danger' }}">
                    {{ $user->status ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td>{{ $user->created_at->format('d M Y') }}</td>

            <td>
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item"
                               href="{{ route('admin.users.edit', encrypt($user->id)) }}">
                                <i class="mdi mdi-pencil text-success me-1"></i> Edit
                            </a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item"
                               data-plugin="delete-data"
                               data-target-form="#form_delete_{{ $loop->iteration }}">
                                <i class="mdi mdi-trash-can text-danger me-1"></i> Delete
                            </a>
                        </li>
                        <form id="form_delete_{{ $loop->iteration }}"
                              method="POST"
                              action="{{ route('admin.users.destroy', encrypt($user->id)) }}">
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                        </form>
                    </ul>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center text-muted">
                No users found
            </td>
        </tr>
    @endforelse
    </tbody>

</table>

<div class="pagination justify-content-center">
    {{ $users->links() }}
</div>

</div>
</div>
</div>
</div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('assets/libs/datatables.net/datatables.net.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/datatables.net-bs4/datatables.net-bs4.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/datatables.net-responsive/datatables.net-responsive.min.js') }}"></script>
@endsection
