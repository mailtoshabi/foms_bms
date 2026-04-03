@extends('admin.layouts.master')

@section('title', 'Student-Teacher Messages')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-comments me-2"></i> Student-Teacher Messages
        </h4>
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.st-messages.index') }}" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search by student or teacher name..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="direction" class="form-control">
                        <option value="">All Directions</option>
                        <option value="teacher_to_student" {{ request('direction') == 'teacher_to_student' ? 'selected' : '' }}>
                            Teacher → Student
                        </option>
                        <option value="student_to_teacher" {{ request('direction') == 'student_to_teacher' ? 'selected' : '' }}>
                            Student → Teacher
                        </option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.st-messages.index') }}" class="btn btn-light">Reset</a>
                </div>
            </div>
        </form>

        @if($messages->isEmpty())
            <p class="text-muted text-center py-4">No messages found.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Direction</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Message</th>
                            <th>Replies</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $msg)
                            @php
                                $isTeacherSender = $msg->sender_type === 'App\Models\Teacher';
                                $senderRole  = $isTeacherSender ? 'Teacher' : 'Student';
                                $receiverRole = $isTeacherSender ? 'Student' : 'Teacher';
                            @endphp
                            <tr>
                                <td>{{ $messages->firstItem() + $loop->index }}</td>
                                <td>
                                    @if($isTeacherSender)
                                        <span class="badge bg-primary">Teacher → Student</span>
                                    @else
                                        <span class="badge bg-success">Student → Teacher</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $isTeacherSender ? 'primary' : 'info' }} me-1">
                                        {{ $senderRole }}
                                    </span>
                                    {{ $msg->sender->name ?? '-' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $isTeacherSender ? 'info' : 'primary' }} me-1">
                                        {{ $receiverRole }}
                                    </span>
                                    {{ $msg->receiver->name ?? '-' }}
                                </td>
                                <td>{{ Str::limit($msg->message, 60) }}</td>
                                <td>
                                    @if($msg->replies->count() > 0)
                                        <span class="badge bg-warning text-dark">{{ $msg->replies->count() }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>{{ $msg->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <a href="{{ route('admin.st-messages.show', encrypt($msg->id)) }}"
                                        class="btn btn-sm btn-info" title="View Thread">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $messages->links() }}
            </div>
        @endif

    </div>
</div>

@endsection
