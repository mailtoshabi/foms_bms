@extends('teacher.layouts.master')

@section('title', 'Message Thread')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Message Thread</h4>
        <a href="{{ route('teacher.messages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Inbox
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Original Message --}}
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>
                    @if($message->sender_type == 'App\Models\Teacher' && $message->sender_id == $teacher->id)
                        You
                    @else
                        {{ $message->sender->name ?? '-' }}
                    @endif
                </strong>
                <div class="d-flex align-items-center gap-2">
                    @if($isClassMessage)
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-users"></i> Class: {{ $message->receiver->name ?? '-' }}
                        </span>
                    @endif
                    <small class="text-muted">{{ $message->created_at->format('d M Y, h:i A') }}</small>
                </div>
            </div>
            <p class="mb-0">{{ $message->message }}</p>
        </div>

        {{-- Replies --}}
        @foreach($message->replies as $reply)
            <div class="border rounded p-3 mb-2 ms-4 {{ $reply->sender_type == 'App\Models\Teacher' && $reply->sender_id == $teacher->id ? 'bg-light' : 'bg-soft-success' }}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>
                        @if($reply->sender_type == 'App\Models\Teacher' && $reply->sender_id == $teacher->id)
                            You
                        @else
                            {{ $reply->sender->name ?? '-' }}
                        @endif
                    </strong>
                    <small class="text-muted">{{ $reply->created_at->format('d M Y, h:i A') }}</small>
                </div>
                <p class="mb-0">{{ $reply->message }}</p>
            </div>
        @endforeach

        {{-- Reply Form --}}
        <hr>
        <form method="POST" action="{{ route('teacher.messages.reply', encrypt($message->id)) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Reply</label>
                <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="3" required placeholder="Type your reply..."></textarea>
                @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Sending...'; this.form.submit();">
                <i class="fas fa-reply"></i> Send Reply
            </button>
        </form>
    </div>
</div>

@endsection
