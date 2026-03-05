@extends('admin.layouts.master')
@section('title','Conversation')

@section('content')

<div class="card">

<div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0">
        Conversation : {{ $conversation->subject }}
    </h4>

    <a href="{{ route('admin.messages.index') }}" class="btn btn-light btn-sm">
        Back
    </a>
</div>

<div class="card-body">

<div class="chat-container mb-4">

    {{-- First Message --}}
    @php
        $isMe =
            (auth('admin')->check() && $conversation->sender_type=='admin' && $conversation->sender_id==auth('admin')->id())
            ||
            (auth('staff')->check() && $conversation->sender_type=='staff' && $conversation->sender_id==auth('staff')->id());
    @endphp

    <div class="d-flex mb-3 {{ $isMe ? 'justify-content-end' : 'justify-content-start' }}">
        <div class="chat-bubble {{ $isMe ? 'bg-primary text-white' : 'bg-light' }}">
            <div class="small mb-1">
                <strong>{{ ucfirst($conversation->sender_type) }}</strong>
                <span class="text-muted small">
                    • {{ $conversation->created_at->format('d M Y h:i A') }}
                </span>
            </div>
            {!! nl2br(e($conversation->message)) !!}
        </div>
    </div>

    {{-- Replies --}}
    @foreach($replies as $reply)

        @php
            $isMe =
                (auth('admin')->check() && $reply->sender_type=='admin' && $reply->sender_id==auth('admin')->id())
                ||
                (auth('staff')->check() && $reply->sender_type=='staff' && $reply->sender_id==auth('staff')->id());
        @endphp

        <div class="d-flex mb-3 {{ $isMe ? 'justify-content-end' : 'justify-content-start' }}">
            <div class="chat-bubble {{ $isMe ? 'bg-primary text-white' : 'bg-light' }}">
                <div class="small mb-1">
                    <strong>{{ ucfirst($reply->sender_type) }}</strong>
                    <span class="text-muted small">
                        • {{ $reply->created_at->format('d M Y h:i A') }}
                    </span>
                </div>
                {!! nl2br(e($reply->message)) !!}
            </div>
        </div>

    @endforeach

</div>

{{-- Reply Form --}}
<form method="POST" action="{{ route('admin.messages.reply', encrypt($conversation->id)) }}">
    @csrf

    <div class="row">
        <div class="col-md-10">
            <textarea name="message"
                      class="form-control"
                      rows="2"
                      placeholder="Type your reply..."
                      required></textarea>
        </div>

        <div class="col-md-2 d-grid">
            <button class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Send
            </button>
        </div>
    </div>
</form>

</div>
</div>

@endsection


@section('style')
<style>
.chat-container {
    max-height: 500px;
    overflow-y: auto;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.chat-bubble {
    max-width: 70%;
    padding: 12px 15px;
    border-radius: 12px;
    font-size: 14px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
</style>
@endsection
