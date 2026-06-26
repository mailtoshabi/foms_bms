@extends('staff.layouts.master')

@php
    $isEdit = isset($student);
@endphp

@section('title', $isEdit ? 'Edit Student' : 'Add Student')

@section('content')

    <div class="row">

        <form method="POST"
            action="{{ $isEdit ? route('staff.students.update', encrypt($student->id)) : route('staff.students.store') }}"
            enctype="multipart/form-data">

            @csrf

            @if(isset($relativeOfStudent))
                <input type="hidden" name="relative_of" value="{{ encrypt($relativeOfStudent->id) }}">
            @endif

            @if($isEdit)
                @method('PUT')
            @endif

            <div class="col-12">

                {{-- ================= STUDENT DETAILS ================= --}}
                <div class="card">

                    <div class="card-header d-flex align-items-center">
                        <a href="javascript:window.history.back();"
                            class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <div>
                            <h4 class="card-title mb-0">Student Details</h4>
                            <p class="card-title-desc mb-0">
                                {{ $isEdit ? 'Edit' : 'Enter' }} student details
                            </p>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-12 mb-3">
                                <label>Country <span class="text-danger">*</span></label>
                                @if(isset($relativeOfStudent))
                                    <select class="form-control" disabled>
                                        <option value="{{ $relativeOfStudent->country_id }}">
                                            {{ $relativeOfStudent->country->name ?? '' }} ({{ $relativeOfStudent->country->code ?? '' }})
                                        </option>
                                    </select>
                                    <input type="hidden" name="country_id" value="{{ $relativeOfStudent->country_id }}">
                                @else
                                    <select name="country_id" class="form-control @error('country_id') is-invalid @enderror"
                                        required>
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" {{ old('country_id', $student->country_id ?? '') == $country->id || (!old('country_id') && !isset($student) && $country->name == 'India') ? 'selected' : '' }}>
                                                {{ $country->name }} ({{ $country->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $student->name ?? '') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Contact Number</label>
                                <input type="text" name="contact_number" id="contact_number"
                                    class="form-control @error('contact_number') is-invalid @enderror @error('phone') is-invalid @enderror"
                                    maxlength="15" value="{{ old('contact_number', isset($relativeOfStudent) ? $relativeOfStudent->contact_number : ($student->contact_number ?? '')) }}"
                                    {{ isset($relativeOfStudent) ? 'readonly' : '' }}>
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="whatsapp_different"
                                        name="is_whatsapp_different" value="1" {{ old('is_whatsapp_different', $student->is_whatsapp_different ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="whatsapp_different">WhatsApp number is
                                        different?</label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3" id="whatsapp_field_group"
                                style="{{ old('is_whatsapp_different', $student->is_whatsapp_different ?? false) ? '' : 'display: none;' }}">
                                <label>WhatsApp Number (with country code)</label>
                                <input type="text" name="whatsapp_number" id="whatsapp_number" class="form-control"
                                    value="{{ old('whatsapp_number', $student->whatsapp_number ?? '') }}"
                                    placeholder="e.g. 919876543210">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $student->email ?? '') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror"
                                    value="{{ old('dob', isset($student) && $student->dob ? $student->dob->format('Y-m-d') : '') }}">
                                @error('dob')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Parent Name</label>
                                <input type="text" name="parent_name"
                                    class="form-control @error('parent_name') is-invalid @enderror"
                                    value="{{ old('parent_name', isset($relativeOfStudent) ? $relativeOfStudent->parent_name : ($student->parent_name ?? '')) }}">
                                @error('parent_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror">
                                    <option value="active" {{ old('status', $student->status ?? '') == 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>

                                    <option value="passout" {{ old('status', $student->status ?? '') == 'passout' ? 'selected' : '' }}>
                                        Passout
                                    </option>

                                    <option value="dropout" {{ old('status', $student->status ?? '') == 'dropout' ? 'selected' : '' }}>
                                        Dropout
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Address</label>
                                <textarea name="address"
                                    class="form-control @error('address') is-invalid @enderror">{{ old('address', isset($relativeOfStudent) ? $relativeOfStudent->address : ($student->address ?? '')) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Photo</label>
                                <input type="file" name="photo" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>ID Proof</label>
                                <input type="file" name="id_proof" class="form-control">
                            </div>

                        </div>

                    </div>
                </div>

                {{-- ================= CLASS SCHEDULE ================= --}}
                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title">Class Schedule</h4>
                        <p class="card-title-desc">Student class timing and schedule</p>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label>Classes Per Week</label>

                                <input type="number" name="classes_per_week" id="classes_per_week" class="form-control"
                                    readonly data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Select days first to calculate classes per week"
                                    value="{{ old('classes_per_week', $student->classes_per_week ?? 0) }}">

                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Time Slot</label>

                                <input type="text" name="time_slot" id="time_slot"
                                    class="form-control @error('time_slot') is-invalid @enderror"
                                    value="{{ old('time_slot', $student->time_slot ?? '') }}">
                                @error('time_slot')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Starting Date</label>
                                <input type="date" name="starting_date"
                                    class="form-control @error('starting_date') is-invalid @enderror"
                                    value="{{ old('starting_date', isset($student) && $student->starting_date ? $student->starting_date->format('Y-m-d') : '') }}">
                                @error('starting_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>


                        {{-- Selected Days --}}
                        <div class="row">

                            <div class="col-md-12 mb-3">
                                <label>Selected Days</label>

                                @php
                                    $days = ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'];
                                    $selectedDays = old('selected_days', $student->selected_days ?? []);
                                @endphp

                                <div class="d-flex flex-wrap gap-3">

                                    @foreach($days as $key => $day)

                                        <label class="form-check">
                                            <input type="checkbox" name="selected_days[]" value="{{ $key }}"
                                                class="form-check-input class-day" {{ in_array($key, $selectedDays ?? []) ? 'checked' : '' }}>

                                            <span class="form-check-label">
                                                {{ $day }}
                                            </span>
                                        </label>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                    </div>
                </div>

                {{-- ================= LOGIN INFORMATION ================= --}}
                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title">Login Information</h4>
                        <p class="card-title-desc">Student login credentials</p>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label>Phone (Login)</label>

                                <input type="text" name="phone" id="phone" class="form-control" readonly
                                    value="{{ old('phone', isset($relativeOfStudent) ? $relativeOfStudent->phone : ($student->phone ?? '')) }}">

                            </div>


                            <div class="col-md-6 mb-3 {{ $isEdit ? '' : 'required' }}">
                                <label>Password</label>

                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                @if($isEdit)
                                    <small class="text-muted">
                                        Leave blank to keep existing password
                                    </small>
                                @endif

                            </div>

                        </div>

                    </div>

                </div>



                {{-- ================= ACTION BUTTONS ================= --}}
                <div class="card">

                    <div class="card-header">

                        <button class="btn btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                            {{ $isEdit ? 'Update Student' : 'Save Student' }}
                        </button>

                        <a href="{{ route('staff.students.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>

                    </div>

                </div>

            </div>

        </form>

    </div>

@endsection

@section('script')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>

        flatpickr("#time_slot", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",   // stored in DB
            altInput: true,
            altFormat: "h:i K"   // shown to user (AM/PM)
        });

    </script>

    @if(!$isEdit && !isset($relativeOfStudent))
        <script>
            $('#contact_number').on('keyup change', function () {

                let number = $(this).val();

                $('#phone').val(number);
                $('#password').val(number);

            });
        </script>
    @endif

    <script>
        $('#whatsapp_different').on('change', function () {
            if ($(this).is(':checked')) {
                $('#whatsapp_field_group').show();
            } else {
                $('#whatsapp_field_group').hide();
                $('#whatsapp_number').val(''); // Clear it to avoid confusion, though logic handles it on save
            }
        });
    </script>

    <script>

        function updateClassesPerWeek() {

            let count = $('.class-day:checked').length;

            $('#classes_per_week').val(count);

        }

        $('.class-day').on('change', function () {

            updateClassesPerWeek();

        });

        $(document).ready(function () {

            updateClassesPerWeek();

        });

    </script>
    <script>

        var tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );

        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

    </script>

    <script>

        $('#classes_per_week').on('click', function () {

            let count = $('.class-day:checked').length;

            if (count === 0) {
                alert('Select days first');
            }

        });

    </script>
    @include('components.image-compressor')

@endsection