@extends('admin.layouts.master')

@section('title', 'Message Thread')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-comments me-2"></i> Message Thread
        </h4>
        <a href="{{ route('admin.st-messages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card-body">

        {{-- Participants summary --}}
        @php
            $isTeacherSender = $message->sender_type === 'App\Models\Teacher';
        @endphp
        <div class="alert alert-info d-flex gap-3 align-items-center mb-3">
            <div>
                <strong>{{ $isTeacherSender ? 'Teacher' : 'Student' }}:</strong>
                {{ $message->sender->name ?? '-' }}
            </div>
            <div class="text-muted">↔</div>
            <div>
                <strong>{{ $isTeacherSender ? 'Student' : 'Teacher' }}:</strong>
                {{ $message->receiver->name ?? '-' }}
            </div>
        </div>

        {{-- Original Message --}}
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>
                    <span class="badge bg-{{ $isTeacherSender ? 'primary' : 'success' }} me-1">
                        {{ $isTeacherSender ? 'Teacher' : 'Student' }}
                    </span>
                    {{ $message->sender->name ?? '-' }}
                </strong>
                <small class="text-muted">{{ $message->created_at->format('d M Y, h:i A') }}</small>
            </div>
            <p class="mb-0">{{ $message->message }}</p>
        </div>

        {{-- Replies --}}
        @forelse($message->replies as $reply)
            @php
                $replyIsTeacher = $reply->sender_type === 'App\Models\Teacher';
            @endphp
            <div class="border rounded p-3 mb-2 ms-4 {{ $replyIsTeacher ? 'bg-light' : 'bg-soft-success' }}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>
                        <span class="badge bg-{{ $replyIsTeacher ? 'primary' : 'success' }} me-1">
                            {{ $replyIsTeacher ? 'Teacher' : 'Student' }}
                        </span>
                        {{ $reply->sender->name ?? '-' }}
                    </strong>
                    <small class="text-muted">{{ $reply->created_at->format('d M Y, h:i A') }}</small>
                </div>
                <p class="mb-0">{{ $reply->message }}</p>
            </div>
        @empty
            <p class="text-muted ms-4">No replies in this thread.</p>
        @endforelse

    </div>
</div>

@endsection
