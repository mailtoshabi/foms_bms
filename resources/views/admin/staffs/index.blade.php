@extends('admin.layouts.master')

@section('title') Staff List @endsection

@section('content')

    @component('admin.breadcrumbs.breadcrumb')
    @slot('li_1') Account Manage @endslot
    @slot('li_2') Staff Manage @endslot
    @slot('title') Staff List @endslot
    @endcomponent

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">

            {{-- ================= Header + Add Button ================= --}}
            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    Staff List
                    <span class="text-muted">({{ $staffs->total() }})</span>
                </h5>

                <a href="{{ route('admin.staffs.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Staff
                </a>

            </div>


            {{-- ================= Filters ================= --}}
            <form method="GET" class="card p-3 mb-3">
                <div class="row">

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Name</label>
                        <input type="text" name="name" value="{{ request('name') }}" class="form-control"
                            placeholder="Search by Name">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Contact</label>
                        <input type="text" name="phone" value="{{ request('phone') }}" class="form-control"
                            placeholder="Search by Contact">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Department</label>
                        <select name="role" class="form-control">
                            <option value="">All Departments</option>

                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @if(request('role') == $role->id) selected @endif>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>

                    <div class="col-md-1 d-grid align-items-end">
                        <button class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered  align-middle table-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Departments</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($staffs as $staff)
                            <tr>

                                <td>
                                    @if($staff->photo)
                                        <img src="{{ asset('storage/' . $staff->photo) }}" width="30">
                                    @endif
                                    <a href="{{ route('admin.staffs.show', encrypt($staff->id)) }}">{{ $staff->name }}</a>
                                </td>

                                <td>{{ $staff->email }}</td>

                                <td>{{ $staff->phone }}</td>

                                <td>
                                    @foreach($staff->roles as $role)
                                        <span class="badge bg-info">{{ $role->name }}</span>
                                    @endforeach
                                </td>

                                <td>
                                    <span class="badge {{ $staff->is_blocked ? 'bg-danger' : 'bg-success' }}">
                                        {{ $staff->is_blocked ? 'Blocked' : 'Active' }}
                                    </span>
                                </td>

                                <td class="text-nowrap">

                                    <div class="d-flex align-items-center gap-2">

                                        <a href="{{ route('admin.staffs.show', encrypt($staff->id)) }}" data-bs-toggle="tooltip"
                                            title="View Details">
                                            <i class="mdi mdi-eye text-info"></i>
                                        </a>

                                        <a href="{{ route('admin.staffs.edit', encrypt($staff->id)) }}" data-bs-toggle="tooltip"
                                            title="Edit">
                                            <i class="mdi mdi-pencil text-success"></i>
                                        </a>

                                        <a href="#" data-plugin="delete-data" data-target-form="#delete_{{ $staff->id }}"
                                            data-bs-toggle="tooltip" title="Delete">
                                            <i class="mdi mdi-trash-can text-danger"></i>
                                        </a>

                                        <a href="{{ route('admin.staffs.toggleBlock', encrypt($staff->id)) }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ $staff->is_blocked ? 'Unblock Staff' : 'Block Staff' }}">
                                            <i
                                                class="fas fa-ban {{ $staff->is_blocked ? 'text-warning' : 'text-secondary' }}"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            {{ $staffs->links() }}

        </div>
    </div>

@endsection