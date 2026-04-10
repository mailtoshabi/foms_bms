@extends('student.layouts.master-layouts-noleft')

@section('title', 'Message Thread')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Message Thread</h4>
        <a href="{{ route('student.messages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="card-body">
        {{-- Original Message --}}
        <div class="p-3 rounded mb-3 bg-soft-success">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <strong>
                    @if($message->sender_type == 'App\Models\Student' && $message->sender_id == $student->id)
                        You
                    @else
                        {{ $message->sender->name ?? 'Unknown' }}
                    @endif
                </strong>
                <div class="d-flex align-items-center gap-2">
                    @if($isClassMessage)
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-users"></i> Class: {{ $message->receiver->name ?? '-' }}
                        </span>
                    @endif
                    <span class="text-muted">{{ $message->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
            <p class="mt-2 mb-0">{{ $message->message }}</p>
        </div>

        {{-- Replies --}}
        @foreach($replies as $reply)
            <div class="ms-4 p-3 rounded mb-2 {{ $reply->sender_type == 'App\Models\Student' && $reply->sender_id == $student->id ? 'bg-light' : 'bg-soft-success' }}">
                <strong>
                    @if($reply->sender_type == 'App\Models\Student' && $reply->sender_id == $student->id)
                        You
                    @else
                        {{ $reply->sender->name ?? 'Unknown' }}
                    @endif
                </strong>
                <span class="text-muted float-end">{{ $reply->created_at->format('d M Y, h:i A') }}</span>
                <p class="mt-2 mb-0">{{ $reply->message }}</p>
            </div>
        @endforeach

        {{-- Reply Form --}}
        <hr>
        <form action="{{ route('student.messages.reply', encrypt($message->id)) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Reply</label>
                <textarea name="message" class="form-control" rows="3" required></textarea>
                @error('message')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerText='Sending...'; this.form.submit();">Send Reply</button>
        </form>
    </div>
</div>

@endsection
