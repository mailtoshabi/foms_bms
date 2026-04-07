@extends('staff.layouts.master')

@php
$isEdit = isset($lead);
@endphp

@section('title', $isEdit ? 'Edit Teacher Lead' : 'Add Teacher Lead')

@section('content')

<div class="card">

<div class="card-header">
<h4>{{ $isEdit ? 'Edit Teacher Lead' : 'Add Teacher Lead' }}</h4>
</div>

<div class="card-body">

<form method="POST"
action="{{ $isEdit ? route('staff.teacher-leads.update',encrypt($lead->id)) : route('staff.teacher-leads.store') }}">

@csrf

@if($isEdit)
@method('PUT')
@endif

<div class="row">

<div class="col-md-6 mb-3">
<label>Name</label>
<input type="text"
name="name"
class="form-control"
value="{{ old('name',$lead->name ?? '') }}">
</div>


<div class="col-md-6 mb-3">
<label>Contact Number</label>
<input type="text"
name="contact_number"
class="form-control"
value="{{ old('contact_number',$lead->contact_number ?? '') }}">
</div>


<div class="col-md-6 mb-3">
<label>Email</label>
<input type="email"
name="email"
class="form-control"
value="{{ old('email',$lead->email ?? '') }}">
</div>


<div class="col-md-6 mb-3">
<label>Source</label>
<select name="source_id" class="form-control select2">
    <option value="">Select Source</option>
    @foreach($sources as $source)
        <option value="{{ $source->id }}"
            {{ old('source_id', $lead->source_id ?? '') == $source->id ? 'selected' : '' }}>
            {{ $source->name }}
        </option>
    @endforeach
</select>
</div>


@if($isEdit)

<div class="col-md-6 mb-3">
<label>Status</label>

<select name="status" class="form-control">

<option value="pending"
{{ old('status',$lead->status)=='pending'?'selected':'' }}>
Pending
</option>

<option value="approved"
{{ old('status',$lead->status)=='approved'?'selected':'' }}>
Approved
</option>

<option value="not_interested"
{{ old('status',$lead->status)=='not_interested'?'selected':'' }}>
Not Interested
</option>

</select>

</div>

@endif

</div>


<div class="d-flex align-items-center mt-3">

<button class="btn btn-primary">
{{ $isEdit?'Update':'Save' }}
</button>


@if($isEdit && !$lead->teacher)

<button type="button"
class="btn btn-success ms-2"
data-bs-toggle="modal"
data-bs-target="#convertTeacherModal">

Convert to Teacher

</button>

@endif

<a href="{{ route('staff.teacher-leads.index') }}"
class="btn btn-light ms-2">
Cancel
</a>

</div>

</form>

</div>
</div>


@if($isEdit && !$lead->teacher)

<div class="card mt-4">

    <div class="card-header">
        <h5>Teacher Application Link</h5>
    </div>

    <div class="card-body">

        @php
        $token = $lead->generateFormToken();
        $link = route('admission.form',['teacher',$token]);

        $phone = preg_replace('/[^0-9]/','',$lead->contact_number);

        $message = "Dear {$lead->name}, please complete your teacher application using the following link: ".$link;

        $waLink = "https://wa.me/91{$phone}?text=".urlencode($message);
        @endphp


        <div class="input-group">

            <input type="text"
            class="form-control"
            value="{{ $link }}"
            readonly>

            <button class="btn btn-primary"
            onclick="navigator.clipboard.writeText('{{ $link }}')">

            <i class="mdi mdi-content-copy"></i>

            </button>

            <a href="{{ $waLink }}"
            target="_blank"
            class="btn btn-success">

            <i class="mdi mdi-whatsapp"></i>

            Send

            </a>

        </div>


        @if($lead->form_expires_at && now()->gt($lead->form_expires_at))

        <span class="badge bg-danger">
        Link Expired
        </span>

        @else

        <span class="badge bg-success">
        Expires {{ $lead->form_expires_at->diffForHumans() }}
        </span>

        @endif


        @if($lead->form_opened_at)

        <span class="badge bg-info">
        Opened {{ $lead->form_opened_at->diffForHumans() }}
        </span>

        @endif

    </div>
</div>

@endif

{{-- =========================
     LEAD NOTES SECTION
========================= --}}
@if($isEdit)

<div class="card mt-4">
    <div class="card-header">
        <h5>Lead Notes</h5>
    </div>

    <div class="card-body">

        {{-- Add Note Form --}}
        <form method="POST"
            action="{{ route('staff.teacher-leads.notes.store', $lead->id) }}">
            @csrf

            <div class="row">

                <div class="col-md-8 mb-3">
                    <textarea name="note"
                            rows="3"
                            class="form-control @error('note') is-invalid @enderror"
                            placeholder="Write a note..."></textarea>

                    @error('note')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <select name="status"
                            class="form-control @error('status') is-invalid @enderror">

                        <option value="pending">Pending</option>
                        <option value="follow_up">Follow Up</option>
                        <option value="no_response">No Response</option>
                        <option value="not_interested">Not Interested</option>
                        <option value="interested">Interested</option>
                        <option value="converted">Converted</option>

                    </select>

                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <button class="btn btn-sm btn-primary">
                Add Note
            </button>
        </form>

        <hr>

        {{-- Notes Timeline --}}
       @forelse($lead->notes as $note)
            <div class="border rounded p-3 mb-3">

                <div class="d-flex justify-content-between align-items-center">
                    <strong>{{ $note->staff->name ?? 'Staff' }}</strong>

                    <div>
                        @php
                            $badgeClasses = [
                                'pending' => 'bg-secondary',
                                'follow_up' => 'bg-warning',
                                'no_response' => 'bg-dark',
                                'not_interested' => 'bg-danger',
                                'interested' => 'bg-info',
                                'converted' => 'bg-success',
                            ];
                        @endphp

                        <span class="badge {{ $badgeClasses[$note->status] ?? 'bg-secondary' }}">
                            {{ ucwords(str_replace('_',' ',$note->status)) }}
                        </span>

                        <small class="text-muted ms-2">
                            {{ $note->created_at->format('d M Y h:i A') }}
                        </small>
                    </div>
                </div>

                <div class="mt-2">
                    {{ $note->note }}
                </div>

            </div>
        @empty
            <p class="text-muted">No notes added yet.</p>
        @endforelse

    </div>
</div>

@endif



@if($isEdit && !$lead->teacher)

<div class="modal fade" id="convertTeacherModal">

<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">

<h5 class="modal-title">
Convert Lead to Teacher
</h5>

<button type="button"
class="btn-close"
data-bs-dismiss="modal"></button>

</div>


<form method="POST"
action="{{ route('staff.teacher-leads.convert',encrypt($lead->id)) }}"
enctype="multipart/form-data">

@csrf

<div class="modal-body">

<div class="row">

<div class="col-md-6 mb-3">
<label>Name</label>
<input type="text"
name="name"
class="form-control"
value="{{ $lead->name }}">
</div>


<div class="col-md-6 mb-3">
<label>Contact Number</label>
<input type="text"
name="contact_number"
class="form-control"
value="{{ $lead->contact_number }}">
</div>


<div class="col-md-6 mb-3">
<label>Email</label>
<input type="email"
name="email"
class="form-control"
value="{{ $lead->email }}">
</div>


<div class="col-md-6 mb-3">
<label>Qualification</label>
<input type="text"
name="qualification"
class="form-control">
</div>


<div class="col-md-6 mb-3">
<label>Experience</label>
<input type="number"
name="experience"
class="form-control">
</div>


<div class="col-md-6 mb-3">
<label>UPI Number</label>
<input type="text"
name="upi_number"
class="form-control">
</div>


<div class="col-md-12 mb-3">
<label>Address</label>
<textarea name="address"
class="form-control"></textarea>
</div>


<div class="col-md-6 mb-3">
<label>Photo</label>
<input type="file"
name="photo"
class="form-control">
</div>


<div class="col-md-6 mb-3">
<label>ID Proof</label>
<input type="file"
name="id_proof"
class="form-control">
</div>

</div>

</div>


<div class="modal-footer">

<button type="button"
class="btn btn-light"
data-bs-dismiss="modal">

Cancel

</button>

<button class="btn btn-success">

Create Teacher

</button>

</div>

</form>

</div>
</div>
</div>

@endif

@endsection

@section('script')
<script>
$('.select2').select2();
</script>
@endsection
