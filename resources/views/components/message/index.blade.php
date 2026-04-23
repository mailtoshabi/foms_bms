<div class="card">

<div class="card-header d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h4 class="mb-0">Messages ({{ $messages->total() }})</h4>
    </div>

    <a href="{{ $createRoute }}" class="btn btn-primary">
        New Message
    </a>
</div>

<div class="card-body table-responsive">

<form method="GET" class="row mb-3">

{{-- Sender Filter --}}
<div class="col-md-3">
    <label class="form-label fw-bold">Sender</label>
<select name="sender" class="form-control select2">
<option value="">All Senders</option>

<option value="admin" {{ request('sender')=='admin'?'selected':'' }}>
Admin
</option>

<option value="staff" {{ request('sender')=='staff'?'selected':'' }}>
Staff
</option>

</select>
</div>

{{-- Receiver Filter --}}
<div class="col-md-3">
    <label class="form-label fw-bold">Receiver</label>
<select name="receiver" class="form-control select2">
<option value="">All Receivers</option>

<option value="admin" {{ request('receiver')=='admin'?'selected':'' }}>
Admin
</option>

<option value="staff" {{ request('receiver')=='staff'?'selected':'' }}>
Staff
</option>

</select>
</div>

{{-- Date Filter --}}
<div class="col-md-3">
    <label class="form-label fw-bold">Date</label>
<input type="date"
name="date"
value="{{ request('date') }}"
class="form-control">
</div>

<div class="col-md-3 d-flex align-items-end gap-2">

<button class="btn btn-primary">
Filter
</button>

<a href="{{ $indexRoute }}" class="btn btn-light">
Reset
</a>

</div>

</form>



<table class="table table-bordered align-middle">

<thead>
<tr>
<th>Subject</th>
<th>Sender</th>
<th>Receiver</th>
<th>Date</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

@forelse($messages as $message)

<tr>

<td>{{ $message->subject }}</td>

<td>
@if($message->sender_type == 'admin')
<span class="badge bg-primary">Admin</span>
@else
<span class="badge bg-info">Staff</span>
@endif
</td>


<td>
@if($message->receiver_type == 'admin')
<span class="badge bg-primary">Admin</span>
@else
<span class="badge bg-info">Staff</span>
@endif
</td>


<td>{{ $message->created_at->format('d M Y') }}</td>


<td>

{{-- Main Message Status --}}
@if(!$message->is_read)
<span class="badge bg-warning">New</span>
@endif


{{-- Unread Replies --}}
@if($message->unread_replies_count > 0)

<span class="badge bg-danger">
{{ $message->unread_replies_count }} New Replies
</span>

@endif


@if($message->is_read && $message->unread_replies_count == 0)

<span class="badge bg-success">Read</span>

@endif

</td>


<td>

<div class="d-flex gap-2">

<a href="{{ $showRoute($message->id) }}">
<i class="mdi mdi-eye text-primary"></i>
</a>

</div>

</td>

</tr>

@empty

<tr>
<td colspan="6" class="text-center">
No Messages Found
</td>
</tr>

@endforelse

</tbody>

</table>

{{ $messages->links() }}

</div>
</div>


@push('scripts')
<script>
$('.select2').select2();
</script>
@endpush
