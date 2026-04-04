@extends('student.layouts.master-layouts-noleft')

@section('title', 'Messages - Inbox')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Inbox</h4>
        <a href="{{ route('student.messages.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Message
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($messages->isEmpty())
            <p class="text-muted text-center">No messages found.</p>
        @else
            <div class="table-responsive">
            <table class="table table-bordered">
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
                                @else
                                    <span class="badge bg-success">Received</span>
                                @endif
                            </td>
                            <td>
                                @if($msg->sender_type == 'App\Models\Student')
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
