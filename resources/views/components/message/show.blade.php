<div class="card">

<div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0">
        Conversation : {{ $conversation->subject }}
    </h4>

    <a href="{{ $backRoute }}" class="btn btn-light btn-sm">
        Back
    </a>
</div>

<div class="card-body">

<div class="chat-container mb-4">

    {{-- First Message --}}
    @php
    $isAdmin = $conversation->sender_type == 'admin';
    @endphp

    <div class="d-flex mb-3 {{ $isAdmin ? 'justify-content-end' : 'justify-content-start' }}">
        <div class="chat-bubble {{ $isAdmin ? 'bg-primary text-white' : 'bg-light' }}">

            <div class="small mb-1">
                <strong>{{ $conversation->senderName() }}</strong>
                <span class="{{ $isAdmin ? 'text-white' : 'text-muted' }} small">
                    • {{ $conversation->created_at->format('d M Y h:i A') }}
                </span>
            </div>

            {!! nl2br(e($conversation->message)) !!}

        </div>
    </div>

    {{-- Replies --}}
    @foreach($replies as $reply)

    @php
    $isAdmin = $reply->sender_type == 'admin';
    @endphp

    <div class="d-flex mb-3 {{ $isAdmin ? 'justify-content-end' : 'justify-content-start' }}">
        <div class="chat-bubble {{ $isAdmin ? 'bg-primary text-white' : 'bg-light' }}">

            <div class="small mb-1">
                <strong>{{ $reply->senderName() }}</strong>
                <span class="{{ $isAdmin ? 'text-white' : 'text-muted' }} small">
                    • {{ $reply->created_at->format('d M Y h:i A') }}
                </span>
            </div>

            {!! nl2br(e($reply->message)) !!}

        </div>
    </div>

    @endforeach

</div>


{{-- Reply Form --}}
<form method="POST" action="{{ $replyRoute }}">
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
<button class="btn btn-primary" type="submit" onclick="this.disabled=true; this.innerText='Sending...'; this.form.submit();">
<i class="fas fa-paper-plane"></i> Send
</button>
</div>

</div>

</form>

</div>
</div>


<style>

.chat-container{
max-height:500px;
overflow-y:auto;
padding:10px;
background:#f8f9fa;
border-radius:8px;
}

/* .chat-bubble{
max-width:70%;
padding:12px 15px;
border-radius:12px;
font-size:14px;
box-shadow:0 2px 5px rgba(0,0,0,0.05);
} */

.chat-bubble{
max-width:70%;
padding:12px 15px;
border-radius:14px;
font-size:14px;
position:relative;
}

.chat-bubble.bg-primary{
border-bottom-right-radius:4px;
}

.chat-bubble.bg-light{
border-bottom-left-radius:4px;
}

</style>
