@extends('admin.layouts.master')

@section('title') Department Manager @endsection

@section('content')

@component('admin.breadcrumbs.breadcrumb')
@slot('li_1') Account Manage @endslot
@slot('li_2') Department Manager @endslot
@slot('title') Departments @endslot
@endcomponent

<div class="card">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="card-title mb-0">
        Departments
        <span class="text-muted fw-normal ms-2">({{ $roles->count() }})</span>
    </h5>

    <button class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#createRoleModal">
        <i class="fas fa-plus"></i> Create Department
    </button>
</div>

<div class="table-responsive">
<table class="table align-middle table-bordered">
<thead>
<tr>
    <th width="70">Icon</th>
    <th>Department Name</th>
    <th width="150">Staff Count</th>
    <th width="120">Created</th>
    <th width="120">Action</th>
</tr>
</thead>

<tbody>

@foreach($roles as $role)

@php
$icons = [
    utility('id_enrolment_dept')      => 'fa-user-plus text-primary',      // Add user
    utility('id_administrator_dept')  => 'fa-user-shield text-dark',       // Admin control
    utility('id_finance_dept')        => 'fa-coins text-success',          // Finance / money
    utility('id_hr_dept')             => 'fa-users-cog text-warning',      // HR management
    utility('id_operation_dept')      => 'fa-cogs text-info',              // Operations / system
];

$icon = $icons[$role->id] ?? 'fa-briefcase text-secondary';
@endphp

<tr>

<td class="text-center">
<i class="fas {{ $icon }} fa-lg"></i>
</td>

<td>
<input type="text"
       class="form-control form-control-sm border-0 role-edit-input"
       data-id="{{ encrypt($role->id) }}"
       value="{{ ucfirst($role->name) }}">
</td>

<td>
<span class="badge bg-soft-primary text-primary">
{{ $role->staffs_count ?? 0 }} Staff
</span>
</td>

<td>{{ $role->created_at->format('d M Y') }}</td>

<td>

<a href="#"
   class="text-danger delete-role"
   data-id="{{ encrypt($role->id) }}"
   data-bs-toggle="tooltip"
   title="Delete Department">
    <i class="mdi mdi-trash-can"></i>
</a>

</td>

</tr>

@endforeach

</tbody>
</table>
</div>

</div>
</div>

{{-- ================= CREATE ROLE MODAL ================= --}}
<div class="modal fade" id="createRoleModal">
<div class="modal-dialog">
<form id="createRoleForm">
@csrf
<div class="modal-content">

<div class="modal-header">
<h5>Create Department</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="text" name="name" class="form-control"
placeholder="eg: admission, coordinator, finance" required>
</div>

<div class="modal-footer">
<button class="btn btn-primary">Save</button>
</div>

</div>
</form>
</div>
</div>

@endsection

@section('script')

<script>
document.addEventListener('DOMContentLoaded', function () {

    // TOOLTIP INIT
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el=>{
        new bootstrap.Tooltip(el)
    });

    // CREATE ROLE AJAX
    $('#createRoleForm').on('submit', function(e){
        e.preventDefault();

        $.post("{{ route('admin.roles.store') }}", $(this).serialize(), function(){
            location.reload();
        });
    });

    // INLINE EDIT ROLE NAME
    $('.role-edit-input').on('change', function(){

        let id = $(this).data('id');
        let name = $(this).val();

        $.post("{{ route('admin.roles.updateName') }}", {
            _token:"{{ csrf_token() }}",
            id:id,
            name:name
        });
    });

    // DELETE ROLE AJAX
    $('.delete-role').on('click', function(e){
        e.preventDefault();

        if(!confirm('Delete this department?')) return;

        let id = $(this).data('id');

        $.post("{{ route('admin.roles.destroyAjax') }}",{
            _token:"{{ csrf_token() }}",
            id:id
        },function(){
            location.reload();
        });
    });

});
</script>

@endsection
