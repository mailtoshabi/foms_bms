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
                action="{{ $isEdit ? route('staff.student-leads.update', encrypt($lead->id)) : route('staff.student-leads.store') }}">
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
                        <select name="source_id" class="form-control select2 @error('source_id') is-invalid @enderror">
                            <option value="">Select Source</option>
                            @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ old('source_id', $lead->source_id ?? '') == $source->id ? 'selected' : '' }}>
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
                            <select name="status" class="form-control @error('status') is-invalid @enderror">
                                <option value="pending" {{ old('status', $lead->status) == 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>



                                <option value="follow_up" {{ old('status', $lead->status) == 'follow_up' ? 'selected' : '' }}>
                                    Follow Up
                                </option>

                                <option value="no_response" {{ old('status', $lead->status) == 'no_response' ? 'selected' : '' }}>
                                    No Response
                                </option>

                                <option value="not_interested" {{ old('status', $lead->status) == 'not_interested' ? 'selected' : '' }}>
                                    Not Interested
                                </option>

                                <option value="interested" {{ old('status', $lead->status) == 'interested' ? 'selected' : '' }}>
                                    Interested
                                </option>

                                <option value="converted" {{ old('status', $lead->status) == 'converted' ? 'selected' : '' }}>
                                    Converted
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                </div>

                <div class="d-flex align-items-center mt-3">

                    <button type="submit" class="btn btn-primary"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        {{ $isEdit ? 'Update' : 'Save' }}
                    </button>

                    @if($isEdit && !$lead->hasStudent())

                        <button type="button" onclick="this.disabled=true; this.innerText='Converting...';"
                            class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#convertStudentModal"
                            onclick="return confirm('Are you sure you want to convert this lead to a student?')">
                            Convert to Student
                        </button>

                    @endif

                    <a href="{{ route('staff.student-leads.index') }}" class="btn btn-light ms-2">
                        Cancel
                    </a>



                </div>

            </form>

        </div>
    </div>

    @if($isEdit && !$lead->hasStudent())
        <div class="card mt-4">
            <div class="card-header">
                <h5>Admission Form Link</h5>
            </div>

            <div class="card-body">

                @php
                    $token = $lead->generateFormToken();
                    $link = route('admission.form', ['student', $token]);
                @endphp

                <div class="mt-2">
                    @php
                        $countryCode = $lead->country ? preg_replace('/[^0-9]/', '', $lead->country->code) : '91';
                        $phone = $countryCode . preg_replace('/[^0-9]/', '', $lead->contact_number);
                        $message = "Dear {$lead->name}, please complete your admission form using the following link: " . $link;
                        $waLink = "https://wa.me/{$phone}?text=" . urlencode($message);
                    @endphp

                    <div class="input-group">

                        <input type="text" class="form-control" value="{{ $link }}" readonly>

                        <button class="btn btn-primary" onclick="navigator.clipboard.writeText('{{ $link }}')">
                            <i class="mdi mdi-content-copy"></i>
                        </button>

                        <a href="{{ $waLink }}" target="_blank" class="btn btn-success">
                            <i class="mdi mdi-whatsapp"></i>
                            Send
                        </a>

                    </div>
                </div>

                @if($lead->form_expires_at && now()->gt($lead->form_expires_at))
                    <span class="badge bg-danger">Link Expired</span>

                    <form action="{{ route('staff.student-leads.regenerate-link', encrypt($lead->id)) }}" method="POST"
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

                {{-- Add Note Form --}}
                <form method="POST" action="{{ route('staff.student-leads.notes.store', $lead->id) }}">
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
                                <option value="converted">Converted</option>

                            </select>

                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <button class="btn btn-sm btn-primary"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
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


    @if($isEdit && !$lead->hasStudent())

        <div class="modal fade" id="convertStudentModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Convert Lead to Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form method="POST" action="{{ route('staff.student-leads.convert', encrypt($lead->id)) }}"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="modal-body">

                            <div class="row">

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
                                    <label>Date of Birth</label>
                                    <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror"
                                        value="{{ old('dob') }}">
                                    @error('dob')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Parent Name</label>
                                    <input type="text" name="parent_name"
                                        class="form-control @error('parent_name') is-invalid @enderror"
                                        value="{{ old('parent_name') }}">
                                    @error('parent_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="active">Active</option>
                                        <option value="passout">Passout</option>
                                        <option value="dropout">Dropout</option>
                                    </select>
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

                                <div class="col-12 mt-3 mb-2">
                                    <h6 class="border-bottom pb-2">Class Schedule</h6>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Classes Per Week</label>
                                    <input type="number" name="classes_per_week" id="convert_classes_per_week" class="form-control"
                                        readonly data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Select days first to calculate classes per week"
                                        value="{{ old('classes_per_week') }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Time Slot</label>
                                    <input type="text" name="time_slot" id="convert_time_slot" class="form-control @error('time_slot') is-invalid @enderror"
                                        value="{{ old('time_slot') }}">
                                    @error('time_slot')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Starting Date</label>
                                    <input type="date" name="starting_date" class="form-control @error('starting_date') is-invalid @enderror"
                                        value="{{ old('starting_date') }}">
                                    @error('starting_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label>Selected Days</label>
                                    @php
                                        $days = ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'];
                                        $selectedConvertDays = old('selected_days', []);
                                    @endphp
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach($days as $key => $day)
                                            <label class="form-check">
                                                <input type="checkbox" name="selected_days[]" value="{{ $key }}"
                                                    class="form-check-input convert-class-day" {{ in_array($key, $selectedConvertDays) ? 'checked' : '' }}>
                                                <span class="form-check-label">{{ $day }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="modal-footer">

                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                                Create Student
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>

    @endif

@endsection


@section('script')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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

        flatpickr("#convert_time_slot", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            altInput: true,
            altFormat: "h:i K"
        });

        function updateConvertClassesPerWeek() {
            let count = $('.convert-class-day:checked').length;
            $('#convert_classes_per_week').val(count);
        }

        $('.convert-class-day').on('change', function () {
            updateConvertClassesPerWeek();
        });

        $(document).ready(function () {
            updateConvertClassesPerWeek();
            
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        $('#convert_classes_per_week').on('click', function () {
            let count = $('.convert-class-day:checked').length;
            if (count === 0) {
                alert('Select days first');
            }
        });
    </script>
@endsection
