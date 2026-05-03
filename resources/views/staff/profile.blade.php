@extends('staff.layouts.master')

@section('title', 'My Profile')

@section('content')

    <div class="row">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <img class="rounded-circle mb-3"
                        src="@if($staff->photo){{ URL::asset('images/' . $staff->photo) }}@else https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&size=150 @endif"
                        alt="Profile" width="120" height="120">
                    <h5>{{ $staff->name }}</h5>
                    @foreach($staff->roles as $role)
                        <span class="badge bg-soft-primary text-primary">{{ ucwords($role->name) }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th width="200">Name</th>
                                    <td>{{ $staff->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $staff->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $staff->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>GPay Number</th>
                                    <td>{{ $staff->gpay_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ $staff->address ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Salary Amount</th>
                                    <td>{{ $staff->salary_amount ? '?' . number_format($staff->salary_amount, 2) : '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
