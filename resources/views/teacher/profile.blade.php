@extends('teacher.layouts.master')

@section('title', 'My Profile')

@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <img class="rounded-circle mb-3"
                    src="@if($teacher->photo){{ URL::asset('images/'.$teacher->photo) }}@else https://ui-avatars.com/api/?name={{ urlencode($teacher->name) }}&size=150 @endif"
                    alt="Profile" width="120" height="120">
                <h5>{{ $teacher->name }}</h5>
                <p class="text-muted mb-1">{{ $teacher->admission_no }}</p>
                <span class="badge bg-{{ $teacher->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($teacher->status) }}</span>
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
                            <td>{{ $teacher->name }}</td>
                        </tr>
                        <tr>
                            <th>Date of Birth</th>
                            <td>{{ $teacher->dob ? $teacher->dob->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $teacher->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Contact Number</th>
                            <td>{{ $teacher->contact_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>WhatsApp Number</th>
                            <td>{{ $teacher->whatsapp_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Qualification</th>
                            <td>{{ $teacher->qualification ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Experience</th>
                            <td>{{ $teacher->experience ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>{{ $teacher->address ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Assigned Classes</h5>
            </div>
            <div class="card-body">
                @if($classes->count() > 0)
                    <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Class</th>
                                <th>Course</th>
                                <th>Type</th>
                                <th>Hourly Wage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classes as $class)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $class->name }}</td>
                                    <td>{{ $class->course->name ?? '-' }}</td>
                                    <td><span class="badge bg-soft-primary text-primary">{{ ucwords($class->classType->name ?? '-') }}</span></td>
                                    <td>{{ $class->pivot->hourly_wage ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No classes assigned.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
