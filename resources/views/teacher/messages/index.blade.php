@extends('teacher.layouts.master-layouts-noleft')

@section('title', 'Messages - Inbox')

@section('content')

    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle"
                title="Go Back"
                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">Inbox</h4>
        </div>
        <a href="{{ route('teacher.messages.create') }}" class="portal-btn portal-btn-primary">
            <i class="fas fa-plus"></i> New Message
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3">{{ session('error') }}</div>
    @endif

    <div class="portal-card">

        <div class="portal-card-header">
            <h4>My Messages</h4>
            <span class="portal-badge portal-badge-primary">Total: {{ count($messages) }}</span>
        </div>

        <div class="portal-card-body table-responsive p-0">

            @if($messages->isEmpty())
                <div class="portal-empty-state">
                    <i class="fas fa-comments portal-empty-state-icon"></i>
                    <div class="portal-empty-state-title">No Messages Found</div>
                    <p class="text-muted small mb-3">Your inbox is currently empty.</p>
                    <a href="{{ route('teacher.messages.create') }}" class="portal-btn portal-btn-primary">
                        <i class="fas fa-plus"></i> Send your first message
                    </a>
                </div>
            @else

                <table class="portal-table">

                    <thead>
                        <tr>
                            <th width="80">#</th>
                            <th width="120">Direction</th>
                            <th width="200">Recipient</th>
                            <th>Message</th>
                            <th width="100">Replies</th>
                            <th width="180">Date</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($messages as $msg)

                            <tr>

                                <td class="text-muted small fw-semibold">{{ $loop->iteration }}</td>

                                <td>
                                    @if($msg->sender_type == 'App\Models\Teacher' && $msg->sender_id == $teacher->id)
                                        <span class="portal-badge portal-badge-primary">Sent</span>
                                    @else
                                        <span class="portal-badge portal-badge-success">Received</span>
                                    @endif
                                </td>

                                <td>
                                    @if($msg->receiver_type == 'App\Models\ClassRoom')
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="portal-badge portal-badge-warning">
                                                <i class="fas fa-users"></i> Class
                                            </span>
                                            <span class="fw-semibold text-dark">{{ $msg->receiver->name ?? '-' }}</span>
                                        </div>
                                    @elseif($msg->sender_type == 'App\Models\Teacher')
                                        <span class="fw-semibold text-dark">{{ $msg->receiver->name ?? '-' }}</span>
                                    @else
                                        <span class="fw-semibold text-dark">{{ $msg->sender->name ?? '-' }}</span>
                                    @endif
                                </td>

                                <td class="text-dark">
                                    @if (!$msg->is_read && $msg->receiver_type == 'App\Models\Teacher' && $msg->receiver_id == $teacher->id)
                                        <span class="badge bg-danger rounded-pill me-1" style="font-size: 10px;">New</span>
                                    @endif
                                    {{ Str::limit($msg->message, 50) }}
                                </td>

                                <td>
                                    @if($msg->replies->count() > 0)
                                        <span class="portal-badge portal-badge-info">{{ $msg->replies->count() }}</span>
                                    @else
                                        <span class="text-muted small">0</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-muted">{{ $msg->created_at->format('d M Y') }}</span>
                                        <small class="text-primary fw-bold mt-1">
                                            <i class="far fa-clock me-1"></i>
                                            {{ $msg->created_at->format('h:i A') }}
                                        </small>
                                    </div>
                                </td>

                                <td>
                                    <a href="{{ route('teacher.messages.show', encrypt($msg->id)) }}" class="portal-btn"
                                        style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 6px 12px;"
                                        title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            @endif

        </div>

    </div>

@endsection