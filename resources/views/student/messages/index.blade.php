@extends('student.layouts.master-layouts-noleft')

@section('title', 'Messages - Inbox')

@section('content')

    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle" title="Go Back" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">Inbox</h4>
        </div>
        <a href="{{ route('student.messages.create') }}" class="portal-btn portal-btn-primary">
            <i class="fas fa-plus"></i> New Message
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($messages->isEmpty())
                <p class="text-muted text-center">No messages found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered  align-middle table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Direction</th>
                                <th>Teacher</th>
                                <th>Message</th>
                                <th>Replies</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $msg)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if($msg->sender_type == 'App\Models\Student' && $msg->sender_id == $student->id)
                                            <span class="badge bg-primary">Sent</span>
                                        @elseif($msg->receiver_type == 'App\Models\ClassRoom')
                                            <span class="badge bg-warning text-dark"><i class="fas fa-users"></i> Class</span>
                                        @else
                                            <span class="badge bg-success">Received</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($msg->receiver_type == 'App\Models\ClassRoom')
                                            {{ $msg->sender->name ?? '-' }}
                                            <small class="text-muted d-block">to: {{ $msg->receiver->name ?? '-' }}</small>
                                        @elseif($msg->sender_type == 'App\Models\Student')
                                            {{ $msg->receiver->name ?? '-' }}
                                        @else
                                            {{ $msg->sender->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($msg->message, 50) }}</td>
                                    <td>
                                        @if($msg->replies->count() > 0)
                                            <span class="badge bg-info">{{ $msg->replies->count() }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>{{ $msg->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('student.messages.show', encrypt($msg->id)) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection