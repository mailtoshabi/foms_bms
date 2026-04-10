<div class="row">
<div class="col-12">

<div class="card">

<div class="card-header">
<h4 class="card-title mb-0">New Message</h4>
</div>

<div class="card-body">

<form action="{{ $storeRoute }}" method="POST">
@csrf

<div class="row">

{{-- Receiver --}}
<div class="col-md-6 mb-3">

<label class="form-label">Send To</label>

<select name="receiver_id"
class="form-control select2"
required>

<option value="">-- Select User --</option>

@foreach($users as $user)

@if($user->id != $currentUserId)

<option value="{{ $user->id }}"
{{ old('receiver_id')==$user->id ? 'selected':'' }}>

{{ $user->name }}

</option>

@endif

@endforeach

</select>

@error('receiver_id')
<small class="text-danger">{{ $message }}</small>
@enderror

</div>


{{-- Subject --}}
<div class="col-md-12 mb-3">

<label class="form-label">Subject</label>

<input type="text"
name="subject"
class="form-control"
placeholder="Enter subject"
value="{{ old('subject') }}"
required>

@error('subject')
<small class="text-danger">{{ $message }}</small>
@enderror

</div>


{{-- Message --}}
<div class="col-md-12 mb-3">

<label class="form-label">Message</label>

<textarea name="message"
class="form-control"
rows="6"
placeholder="Type your message here..."
required>{{ old('message') }}</textarea>

@error('message')
<small class="text-danger">{{ $message }}</small>
@enderror

</div>

</div>


<div class="mt-3">

<button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerText='Sending...'; this.form.submit();">
<i class="fas fa-paper-plane"></i> Send Message
</button>

<a href="{{ $backRoute }}" class="btn btn-secondary">
Cancel
</a>

</div>

</form>

</div>
</div>

</div>
</div>


@push('scripts')
<script>
$('.select2').select2();
</script>
@endpush
