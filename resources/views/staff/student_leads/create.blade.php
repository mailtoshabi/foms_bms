@extends('staff.layouts.master')

@php
    $isEdit = isset($lead);
@endphp

@section('title', $isEdit ? 'Edit Student Lead' : 'Add Student Lead')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>{{ $isEdit ? 'Edit Student Lead' : 'Add Student Lead' }}</h4>
    </div>

    <div class="card-body">

        <form method="POST"
              action="{{ $isEdit ? route('staff.student-leads.update', $lead->id) : route('staff.student-leads.store') }}">
            @csrf

            @if($isEdit)
                @method('PUT')
            @endif

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Name</label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $lead->name ?? '') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Contact Number</label>
                    <input type="text"
                           name="contact_number"
                           class="form-control @error('contact_number') is-invalid @enderror"
                           value="{{ old('contact_number', $lead->contact_number ?? '') }}">
                    @error('contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $lead->email ?? '') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Source</label>
                    <select name="source_id"
                            class="form-control select2 @error('source_id') is-invalid @enderror">
                        <option value="">Select Source</option>
                        @foreach($sources as $source)
                            <option value="{{ $source->id }}"
                                {{ old('source_id', $lead->source_id ?? '') == $source->id ? 'selected' : '' }}>
                                {{ $source->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('source_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status only in Edit --}}
                @if($isEdit)
                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="status"
                            class="form-control @error('status') is-invalid @enderror">
                        <option value="pending"
                            {{ old('status', $lead->status) == 'pending' ? 'selected' : '' }}>
                            Pending
                        </option>
                        <option value="admitted"
                            {{ old('status', $lead->status) == 'admitted' ? 'selected' : '' }}>
                            Admitted
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                @endif

            </div>

            <div class="d-flex align-items-center mt-3">

    <button type="submit" class="btn btn-primary">
        {{ $isEdit ? 'Update' : 'Save' }}
    </button>

    <a href="{{ route('staff.student-leads.index') }}"
       class="btn btn-light ms-2">
        Cancel
    </a>

    @if($isEdit && !$lead->student)
        <form method="POST"
              action="{{ route('staff.student-leads.convert', $lead->id) }}"
              class="ms-auto"
              onsubmit="return confirm('Are you sure to convert this lead to student?')">
            @csrf
            <button type="submit" class="btn btn-success">
                Convert to Student
            </button>
        </form>
    @endif

</div>

        </form>

    </div>
</div>


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
            action="{{ route('staff.student-leads.notes.store', $lead->id) }}">
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

@endsection


@section('script')
<script>
    $('.select2').select2();
</script>
@endsection
