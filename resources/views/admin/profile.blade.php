@extends('admin.layouts.master')

@section('title', 'My Profile')

@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <img class="rounded-circle mb-3"
                    src="@if(Auth::user()->photo){{ URL::asset('images/'.Auth::user()->photo) }}@else https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&size=150 @endif"
                    alt="Profile" width="120" height="120">
                <h5>{{ $admin->name }}</h5>
                <span class="badge bg-primary">Administrator</span>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Personal Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th width="200">Name</th>
                            <td>{{ $admin->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $admin->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $admin->phone ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
