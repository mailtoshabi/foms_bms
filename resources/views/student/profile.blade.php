@extends('student.layouts.master-layouts-noleft')

@section('title', 'My Profile')

@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <img class="rounded-circle mb-3"
                    src="@if($student->photo){{ URL::asset('images/'.$student->photo) }}@else https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&size=150 @endif"
                    alt="Profile" width="120" height="120">
                <h5>{{ $student->name }}</h5>
                <p class="text-muted mb-1">{{ $student->admission_no }}</p>
                <span class="badge bg-{{ $student->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($student->status) }}</span>
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
                            <td>{{ $student->name }}</td>
                        </tr>
                        <tr>
                            <th>Date of Birth</th>
                            <td>{{ $student->dob ? $student->dob->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $student->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Contact Number</th>
                            <td>{{ $student->contact_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>WhatsApp Number</th>
                            <td>{{ $student->whatsapp_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Parent Name</th>
                            <td>{{ $student->parent_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>{{ $student->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Starting Date</th>
                            <td>{{ $student->starting_date ? $student->starting_date->format('d M Y') : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Enrolled Classes</h5>
            </div>
            <div class="card-body">
                @if($student->class_rooms->count() > 0)
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Class</th>
                                <th>Course</th>
                                <th>Type</th>
                                <th>Schedule</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->class_rooms as $class)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $class->name }}</td>
                                    <td>{{ $class->course->name ?? '-' }}</td>
                                    <td><span class="badge bg-soft-primary text-primary">{{ ucwords($class->classType->name ?? '-') }}</span></td>
                                    <td>
                                        {{ implode(', ', $class->selected_days ?? []) }}
                                        @if($class->time_slot)
                                            <br><small>{{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted mb-0">No classes enrolled.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
