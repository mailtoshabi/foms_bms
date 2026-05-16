@extends('teacher.layouts.master-layouts-noleft')

@section('title', 'Message Thread')

@section('content')

    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle"
                title="Go Back"
                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">Message Thread</h4>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="portal-card">

                <div class="portal-card-header">
                    <h4>Conversation Thread</h4>
                </div>

                <div class="portal-card-body">

                    {{-- Original Message --}}
                    <div class="p-3 mb-4 rounded-3 border"
                        style="@if($message->sender_type == 'App\Models\Teacher' && $message->sender_id == $teacher->id) background: #f8fafc; border-color: #cbd5e1 !important; @else background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.15) !important; @endif shadow-sm;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-dark">
                                @if($message->sender_type == 'App\Models\Teacher' && $message->sender_id == $teacher->id)
                                    You
                                @else
                                    {{ $message->sender->name ?? '-' }}
                                @endif
                            </strong>
                            <div class="d-flex align-items-center gap-2">
                                @if($isClassMessage)
                                    <span class="portal-badge portal-badge-warning" style="padding: 4px 10px !important;">
                                        <i class="fas fa-users me-1"></i> Class: {{ $message->receiver->name ?? '-' }}
                                    </span>
                                @endif
                                <small class="text-muted small">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $message->created_at->format('d M Y, h:i A') }}
                                </small>
                            </div>
                        </div>
                        <p class="mb-0 text-dark-50" style="white-space: pre-line;">{{ $message->message }}</p>
                    </div>

                    {{-- Replies Feed --}}
                    @if($message->replies->count() > 0)
                        <div class="ps-4 border-start border-2 border-light mb-4">
                            @foreach($message->replies as $reply)
                                        <div class="p-3 mb-3 rounded-3 border"
                                            style="{{ $reply->sender_type == 'App\Models\Teacher' && $reply->sender_id == $teacher->id
                                ? 'background: #f8fafc; border-color: #e2e8f0 !important;'
                                : 'background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.15) !important;' }} shadow-sm;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-dark">
                                                    @if($reply->sender_type == 'App\Models\Teacher' && $reply->sender_id == $teacher->id)
                                                        You
                                                    @else
                                                        {{ $reply->sender->name ?? '-' }}
                                                    @endif
                                                </strong>
                                                <small class="text-muted small">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ $reply->created_at->format('d M Y, h:i A') }}
                                                </small>
                                            </div>
                                            <p class="mb-0 text-dark-50" style="white-space: pre-line;">{{ $reply->message }}</p>
                                        </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Reply Form --}}
                    <div class="mt-4 pt-3 border-top border-light">
                        <form method="POST" action="{{ route('teacher.messages.reply', encrypt($message->id)) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="portal-label">Type Reply</label>
                                <textarea name="message" class="portal-input @error('message') is-invalid @enderror"
                                    rows="4" required placeholder="Type your reply here..."></textarea>
                                @error('message')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="portal-btn"
                                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff;"
                                onclick="this.disabled=true; this.innerText='Sending...'; this.form.submit();">
                                <i class="fas fa-reply me-1"></i> Send Reply
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection