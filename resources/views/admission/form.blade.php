@extends('admin.layouts.master-without-nav')

@section('title')
    @if($type == 'student')
        Student Admission
    @else
        Teacher Application
    @endif
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .auth-page {
            overflow-y: auto !important;
            height: 100vh;
        }

        @media (min-width: 768px) {
            .auth-page {
                overflow: visible !important;
            }
        }

        #pwa-install-banner,
        #pwa-ios-banner {
            display: none !important;
        }
    </style>
@endsection

@section('content')

    @php
        $expired = $lead->form_expires_at && now()->gt($lead->form_expires_at);
    @endphp

    <div class="auth-page">
        <div class="container-fluid p-0">
            <div class="row g-0">

                <div class="col-xxl-3 col-lg-4 col-md-5">

                    <div class="auth-full-page-content d-flex p-sm-5 p-4">
                        <div class="w-100">

                            <div class="d-flex flex-column h-100">

                                <div class="mb-4 text-center">
                                    <h4 class="fw-bold">
                                        @if($type == 'student')
                                            Student Admission
                                        @else
                                            Teacher Application
                                        @endif
                                    </h4>
                                </div>

                                <div class="auth-content my-auto">

                                    <div class="text-center mb-4">

                                        @if($type == 'student')
                                            <h5>Complete Your Admission</h5>
                                        @else
                                            <h5>Complete Your Application</h5>
                                        @endif

                                        @if($expired)
                                            <p class="text-danger mt-2">
                                                This link has expired.
                                            </p>
                                        @elseif($lead->form_opened_at)
                                            <p class="text-success mt-2">
                                                Form opened successfully.
                                            </p>
                                        @endif

                                    </div>

                                    @if(!$expired)

                                        <form class="mt-4 pt-2" method="POST"
                                            action="{{ route('admission.submit', [$type, $lead->form_token]) }}"
                                            enctype="multipart/form-data">

                                            @csrf

                                            {{-- ================= BASIC DETAILS ================= --}}

                                            @if($type == 'student')
                                                <div class="mb-4">
                                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                                    <select name="country_id" class="form-control" disabled>
                                                        @foreach($countries as $country)
                                                            <option value="{{ $country->id }}" {{ ($lead->country_id ?? '') == $country->id ? 'selected' : '' }}>
                                                                {{ $country->name }} ({{ $country->code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="country_id" value="{{ $lead->country_id }}">
                                                    <small class="text-muted">Country is pre-filled based on your
                                                        application.</small>
                                                </div>
                                            @endif

                                            <div class="form-floating form-floating-custom mb-4">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                    name="name" value="{{ old('name', $lead->name) }}" required>
                                                <label>Name</label>
                                                <div class="form-floating-icon">
                                                    <i data-feather="user"></i>
                                                </div>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>


                                            <div class="form-floating form-floating-custom mb-4">
                                                <input type="text"
                                                    class="form-control @error('contact_number') is-invalid @enderror"
                                                    name="contact_number"
                                                    value="{{ old('contact_number', $lead->contact_number) }}" required>
                                                <label>Contact Number</label>
                                                <div class="form-floating-icon">
                                                    <i data-feather="phone"></i>
                                                </div>
                                                @error('contact_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>


                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="whatsapp_different"
                                                    name="is_whatsapp_different" value="1" {{ old('is_whatsapp_different') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="whatsapp_different">WhatsApp number is
                                                    different?</label>
                                            </div>

                                            <div id="whatsapp_field_group" class="form-floating form-floating-custom mb-4"
                                                style="{{ old('is_whatsapp_different') ? '' : 'display: none;' }}">
                                                <input type="text"
                                                    class="form-control @error('whatsapp_number') is-invalid @enderror"
                                                    name="whatsapp_number" id="whatsapp_number"
                                                    value="{{ old('whatsapp_number') }}"
                                                    placeholder="Include country code (e.g. 919876543210)">
                                                <label>WhatsApp Number (with country code)</label>
                                                <div class="form-floating-icon">
                                                    <i data-feather="message-circle"></i>
                                                </div>
                                                @error('whatsapp_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>


                                            <div class="form-floating form-floating-custom mb-4">
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                                    value="{{ old('email', $lead->email) }}">
                                                <label>Email</label>
                                                <div class="form-floating-icon">
                                                    <i data-feather="mail"></i>
                                                </div>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>


                                            <div class="form-floating form-floating-custom mb-4">
                                                <input type="date" class="form-control @error('dob') is-invalid @enderror" name="dob" value="{{ old('dob') }}">
                                                <label>Date of Birth</label>
                                                @error('dob')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>


                                            @if($type == 'student')

                                                <div class="form-floating form-floating-custom mb-4">
                                                    <input type="text" class="form-control @error('parent_name') is-invalid @enderror" name="parent_name" value="{{ old('parent_name') }}">
                                                    <label>Parent Name</label>
                                                    @error('parent_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                            @endif


                                            <div class="mb-4">
                                                <label class="form-label">Address</label>
                                                <textarea name="address" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                                                @error('address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>



                                            {{-- ================= STUDENT CLASS DETAILS ================= --}}
                                            @if($type == 'student')



                                                <div class="mb-4">
                                                    <label class="form-label">Preferred Days @error('selected_days') <span class="text-danger">({{ $message }})</span> @enderror</label>

                                                    <div class="d-flex flex-wrap gap-2">

                                                        @php
                                                            $days = ['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'];
                                                            $selectedDays = old('selected_days', []);
                                                        @endphp

                                                        @foreach($days as $key => $day)

                                                            <label class="form-check">
                                                                <input type="checkbox" name="selected_days[]" value="{{ $key }}"
                                                                    class="form-check-input class-day" {{ in_array($key, $selectedDays) ? 'checked' : '' }}>

                                                                <span class="form-check-label">
                                                                    {{ $day }}
                                                                </span>

                                                            </label>

                                                        @endforeach

                                                    </div>
                                                </div>

                                                <div class="form-floating form-floating-custom mb-4">
                                                    <input type="number" class="form-control" name="classes_per_week"
                                                        id="classes_per_week" readonly>
                                                    <label>Classes Per Week</label>
                                                </div>

                                                <div class="form-floating form-floating-custom mb-4">
                                                    <input type="text" class="form-control @error('time_slot') is-invalid @enderror" name="time_slot" id="time_slot" value="{{ old('time_slot') }}">
                                                    <label>Preferred Time Slot</label>
                                                    @error('time_slot')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-floating form-floating-custom mb-4">
                                                    <input type="date" class="form-control @error('starting_date') is-invalid @enderror" name="starting_date" value="{{ old('starting_date') }}">
                                                    <label>Preferred Starting Date</label>
                                                    @error('starting_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                            @endif



                                            {{-- ================= TEACHER DETAILS ================= --}}
                                            @if($type == 'teacher')

                                                <div class="form-floating form-floating-custom mb-4">
                                                    <input type="text" class="form-control @error('qualification') is-invalid @enderror" name="qualification" value="{{ old('qualification') }}">
                                                    <label>Qualification</label>
                                                    @error('qualification')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-floating form-floating-custom mb-4">
                                                    <input type="number" class="form-control @error('experience') is-invalid @enderror" name="experience" value="{{ old('experience') }}">
                                                    <label>Experience (Years)</label>
                                                    @error('experience')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-floating form-floating-custom mb-4">
                                                    <input type="text" class="form-control @error('upi_number') is-invalid @enderror" name="upi_number" value="{{ old('upi_number') }}">
                                                    <label>UPI Number</label>
                                                    @error('upi_number')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                            @endif



                                            {{-- ================= FILE UPLOAD ================= --}}

                                            <div class="mb-4">
                                                <label class="form-label">Photo</label>
                                                <input type="file" class="form-control @error('photo') is-invalid @enderror" name="photo">
                                                @error('photo')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label">ID Proof</label>
                                                <input type="file" class="form-control @error('id_proof') is-invalid @enderror" name="id_proof">
                                                @error('id_proof')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>


                                            <div class="mb-3">

                                                <button class="btn btn-zopa w-100 waves-effect waves-light" type="submit"
                                                    onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">

                                                    @if($type == 'student')
                                                        Submit Admission Form
                                                    @else
                                                        Submit Application
                                                    @endif

                                                </button>

                                            </div>

                                        </form>

                                    @else

                                        <div class="alert alert-danger text-center">
                                            This link is no longer valid.<br>
                                            Please contact the institute.
                                        </div>

                                    @endif

                                </div>

                                <div class="mt-4 mt-md-5 text-center">
                                    <p class="mb-0">
                                        ©
                                        <script>document.write(new Date().getFullYear())</script>
                                        FOMS ACADEMY
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>



                {{-- RIGHT SIDE DESIGN --}}
                <div class="col-xxl-9 col-lg-8 col-md-7 d-none d-md-block">
                    <div class="auth-bg pt-md-5 p-4 d-flex sticky-top vh-100">

                        <div class="bg-overlay"></div>

                        <ul class="bg-bubbles">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>

                        <div class="row justify-content-center align-items-end">
                            <div class="col-xl-7">

                                <div class="p-0 p-sm-4 px-xl-0">

                                    <div class="carousel-inner">

                                        <div class="carousel-item active">

                                            <div class="testi-contain text-center text-white">

                                                <i class="bx bxs-quote-alt-left text-success display-6"></i>

                                                @if($type == 'student')

                                                    <h4 class="mt-4 fw-medium lh-base text-white">
                                                        “Welcome to FOMS Academy. Complete your admission details to begin your
                                                        learning journey.”
                                                    </h4>

                                                @else

                                                    <h4 class="mt-4 fw-medium lh-base text-white">
                                                        “Join FOMS Academy and help shape the future of students.”
                                                    </h4>

                                                @endif

                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection


@section('script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>

        flatpickr("#time_slot", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            altInput: true,
            altFormat: "h:i K"
        });

        $('#whatsapp_different').on('change', function () {
            if ($(this).is(':checked')) {
                $('#whatsapp_field_group').show();
                $('#whatsapp_number').attr('required', true);
            } else {
                $('#whatsapp_field_group').hide();
                $('#whatsapp_number').attr('required', false);
            }
        });

        function updateClassesPerWeek() {
            let count = $('.class-day:checked').length;
            $('#classes_per_week').val(count);
        }

        $('.class-day').on('change', function () {
            updateClassesPerWeek();
        });

    </script>

@endsection