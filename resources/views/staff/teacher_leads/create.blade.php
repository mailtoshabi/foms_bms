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

            @if($isEdit && $lead->status === 'converted')
                <div class="alert alert-warning">
                    This lead has been converted. Details cannot be modified.
                </div>
            @endif

            <form method="POST"
                action="{{ $isEdit ? route('staff.teacher-leads.update', encrypt($lead->id)) : route('staff.teacher-leads.store') }}">

                @csrf

                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row">

                    <div class="col-md-12 mb-3">
                        <label>Country <span class="text-danger">*</span></label>
                        <select name="country_id" class="form-control @error('country_id') is-invalid @enderror" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ old('country_id', $lead->country_id ?? '') == $country->id || (!old('country_id') && !isset($lead) && $country->name == 'India') ? 'selected' : '' }}>
                                    {{ $country->name }} ({{ $country->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('country_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $lead->name ?? '') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="col-md-6 mb-3">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" id="contact_number"
                            class="form-control @error('contact_number') is-invalid @enderror" maxlength="15"
                            value="{{ old('contact_number', $lead->contact_number ?? '') }}">
                        @error('contact_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="whatsapp_different"
                                name="is_whatsapp_different" value="1" {{ old('is_whatsapp_different', $lead->is_whatsapp_different ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="whatsapp_different">WhatsApp number is different?</label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3" id="whatsapp_field_group"
                        style="{{ old('is_whatsapp_different', $lead->is_whatsapp_different ?? false) ? '' : 'display: none;' }}">
                        <label>WhatsApp Number (with country code)</label>
                        <input type="text" name="whatsapp_number" id="whatsapp_number"
                            class="form-control @error('whatsapp_number') is-invalid @enderror"
                            value="{{ old('whatsapp_number', $lead->whatsapp_number ?? '') }}"
                            placeholder="e.g. 919876543210">
                        @error('whatsapp_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $lead->email ?? '') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="col-md-6 mb-3">
                        <label>Source</label>
                        <select name="source_id" class="form-control select2">
                            <option value="">Select Source</option>
                            @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ old('source_id', $lead->source_id ?? '') == $source->id ? 'selected' : '' }}>
                                    {{ $source->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    @if($isEdit)

                        {{-- <div class="col-md-6 mb-3">
                            <label>Status</label>

                            <select name="status" class="form-control">

                                <option value="pending" {{ old('status',$lead->status)=='pending'?'selected':'' }}>
                                    Pending
                                </option>

                                <option value="approved" {{ old('status',$lead->status)=='approved'?'selected':'' }}>
                                    Approved
                                </option>

                                <option value="not_interested" {{ old('status',$lead->status)=='not_interested'?'selected':''
                                    }}>
                                    Not Interested
                                </option>

                            </select>

                        </div> --}}

                    @endif

                </div>


                <div class="d-flex align-items-center mt-3">

                    @if(!($isEdit && $lead->status === 'converted'))
                        <button class="btn btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                            {{ $isEdit ? 'Update' : 'Save' }}
                        </button>
                    @endif


                    @if($isEdit && !$lead->teacher)

                        <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal"
                            data-bs-target="#convertTeacherModal">

                            Convert to Teacher

                        </button>

                    @endif
                    @if(!($isEdit && $lead->status === 'converted'))
                        <a href="{{ route('staff.teacher-leads.index') }}" class="btn btn-light ms-2">
                            Cancel
                        </a>
                    @endif
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
                    $link = route('admission.form', ['teacher', $token]);

                    $phone = preg_replace('/[^0-9]/', '', $lead->contact_number);

                    $message = "Dear {$lead->name}, please complete your teacher application using the following link: " . $link;

                    $waLink = "https://wa.me/91{$phone}?text=" . urlencode($message);
                @endphp


                <div class="input-group mb-2">

                    <input type="text" class="form-control mb-2" value="{{ $link }}" readonly>

                    <button class="btn btn-primary" onclick="navigator.clipboard.writeText('{{ $link }}')">

                        <i class="mdi mdi-content-copy"></i>

                    </button>

                    <a href="{{ $waLink }}" target="_blank" class="btn btn-success ms-2">

                        <i class="mdi mdi-whatsapp"></i>

                        Send

                    </a>

                </div>


                @if($lead->form_expires_at && now()->gt($lead->form_expires_at))

                    <span class="badge bg-danger">
                        Link Expired
                    </span>

                    <form action="{{ route('staff.teacher-leads.regenerate-link', encrypt($lead->id)) }}" method="POST"
                        class="d-inline ms-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger py-0"
                            onclick="return confirm('Generate new link? Existing link will stop working.')">
                            <i class="mdi mdi-refresh"></i> Regenerate
                        </button>
                    </form>

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

                @if($lead->status !== 'converted')
                    {{-- Add Note Form --}}
                    <form method="POST" action="{{ route('staff.teacher-leads.notes.store', $lead->id) }}">
                        @csrf

                        <div class="row">

                            <div class="col-md-8 mb-3">
                                <textarea name="note" rows="3" class="form-control @error('note') is-invalid @enderror"
                                    placeholder="Write a note..."></textarea>

                                @error('note')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <select name="status" class="form-control @error('status') is-invalid @enderror">

                                    <option value="pending">Pending</option>
                                    <option value="follow_up">Follow Up</option>
                                    <option value="no_response">No Response</option>
                                    <option value="not_interested">Not Interested</option>
                                    <option value="interested">Interested</option>

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
                @else
                    <div class="alert alert-info mb-3">
                        This lead has been converted. You can no longer add notes.
                    </div>
                @endif

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
                                    ];
                                @endphp

                                <span class="badge {{ $badgeClasses[$note->status] ?? 'bg-secondary' }}">
                                    {{ ucwords(str_replace('_', ' ', $note->status)) }}
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

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>


                    <form method="POST" action="{{ route('staff.teacher-leads.convert', encrypt($lead->id)) }}"
                        enctype="multipart/form-data">

                        @csrf

                        <div class="modal-body">

                            <div class="row">

                                <div class="col-md-12 mb-3">
                                    <label>Country <span class="text-danger">*</span></label>
                                    <select name="country_id" class="form-control @error('country_id') is-invalid @enderror"
                                        required>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" {{ old('country_id', $lead->country_id ?? '') == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }} ({{ $country->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $lead->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-md-6 mb-3">
                                    <label>Contact Number</label>
                                    <input type="text" name="contact_number"
                                        class="form-control @error('contact_number') is-invalid @enderror"
                                        value="{{ old('contact_number', $lead->contact_number) }}" required>
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $lead->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="convert_whatsapp_different"
                                            name="is_whatsapp_different" value="1" {{ $lead->is_whatsapp_different ? 'checked' : '' }}>
                                        <label class="form-check-label" for="convert_whatsapp_different">WhatsApp number is
                                            different?</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3" id="convert_whatsapp_field_group"
                                    style="{{ old('is_whatsapp_different', $lead->is_whatsapp_different) ? '' : 'display: none;' }}">
                                    <label>WhatsApp Number (with country code)</label>
                                    <input type="text" name="whatsapp_number" id="convert_whatsapp_number"
                                        class="form-control @error('whatsapp_number') is-invalid @enderror"
                                        value="{{ old('whatsapp_number', $lead->whatsapp_number) }}"
                                        placeholder="e.g. 919876543210">
                                    @error('whatsapp_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-md-6 mb-3">
                                    <label>Qualification</label>
                                    <input type="text" name="qualification"
                                        class="form-control @error('qualification') is-invalid @enderror"
                                        value="{{ old('qualification') }}">
                                    @error('qualification')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-md-6 mb-3">
                                    <label>Experience</label>
                                    <input type="number" name="experience"
                                        class="form-control @error('experience') is-invalid @enderror"
                                        value="{{ old('experience') }}">
                                    @error('experience')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-md-6 mb-3">
                                    <label>UPI Number</label>
                                    <input type="text" name="upi_number"
                                        class="form-control @error('upi_number') is-invalid @enderror"
                                        value="{{ old('upi_number') }}">
                                    @error('upi_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-md-12 mb-3">
                                    <label>Address</label>
                                    <textarea name="address"
                                        class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-md-6 mb-3">
                                    <label>Photo</label>
                                    <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror">
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-md-6 mb-3">
                                    <label>ID Proof</label>
                                    <input type="file" name="id_proof"
                                        class="form-control @error('id_proof') is-invalid @enderror">
                                    @error('id_proof')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                        </div>


                        <div class="modal-footer">

                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">

                                Cancel

                            </button>

                            <button class="btn btn-success" type="submit"
                                onclick="this.disabled=true; this.innerText='Converting...'; this.form.submit();">

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

        $('#whatsapp_different').on('change', function () {
            if ($(this).is(':checked')) {
                $('#whatsapp_field_group').show();
            } else {
                $('#whatsapp_field_group').hide();
                $('#whatsapp_number').val('');
            }
        });

        $('#convert_whatsapp_different').on('change', function () {
            if ($(this).is(':checked')) {
                $('#convert_whatsapp_field_group').show();
            } else {
                $('#convert_whatsapp_field_group').hide();
                $('#convert_whatsapp_number').val('');
            }
        });
    </script>
    @include('components.image-compressor')
@endsection