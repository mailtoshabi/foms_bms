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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --admission-primary: #4f46e5;
            --admission-primary-hover: #4338ca;
            --admission-success: #10b981;
            --admission-bg: #f8fafc;
            --admission-card: #ffffff;
            --admission-text: #0f172a;
            --admission-muted: #64748b;
            --admission-border: #e2e8f0;
            --admission-ring: rgba(79, 70, 229, 0.12);
        }

        body {
            background-color: var(--admission-bg) !important;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        }

        .admission-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            background: radial-gradient(circle at 10% 20%, rgba(79, 70, 229, 0.05) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(59, 130, 246, 0.05) 0%, transparent 40%),
                        #f8fafc;
        }

        .admission-card {
            background: var(--admission-card);
            border-radius: 24px;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.06), 0 1px 3px rgba(15, 23, 42, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.8);
            width: 100%;
            max-width: 860px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .admission-header {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            padding: 48px 32px;
            color: #ffffff;
            position: relative;
            text-align: center;
        }

        .admission-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 320px;
            height: 320px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            pointer-events: none;
        }

        .admission-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            pointer-events: none;
        }

        .logo-container {
            width: 80px;
            height: 80px;
            background: #ffffff;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .admission-title {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
        }

        .admission-subtitle {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }

        .section-title-premium {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--admission-primary);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-top: 24px;
            margin-bottom: 24px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(79, 70, 229, 0.08);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title-premium::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 16px;
            background: var(--admission-primary);
            border-radius: 4px;
        }

        .form-label-premium {
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            display: block;
        }

        .form-control-premium {
            background-color: #f8fafc !important;
            border: 1px solid var(--admission-border) !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            color: var(--admission-text) !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            height: auto !important;
        }

        .form-control-premium:focus {
            background-color: #ffffff !important;
            border-color: var(--admission-primary) !important;
            box-shadow: 0 0 0 4px var(--admission-ring) !important;
            outline: none !important;
        }

        .form-control-premium:disabled {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: var(--admission-muted) !important;
            cursor: not-allowed;
        }

        .form-control-premium::placeholder {
            color: #94a3b8 !important;
            font-weight: 400 !important;
        }

        /* Day Chips Selection UI */
        .day-chips-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .day-chip {
            cursor: pointer;
            margin: 0;
            user-select: none;
        }

        .day-chip input {
            display: none !important;
        }

        .day-chip span {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid var(--admission-border);
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
        }

        .day-chip:hover span {
            border-color: #cbd5e1;
            background: #f1f5f9;
        }

        .day-chip input:checked + span {
            background: var(--admission-primary);
            color: #ffffff;
            border-color: var(--admission-primary);
            box-shadow: 0 6px 15px -4px rgba(79, 70, 229, 0.3);
        }

        /* File inputs */
        .file-upload-premium {
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            padding: 18px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-upload-premium:hover {
            border-color: var(--admission-primary);
            background: rgba(79, 70, 229, 0.02);
        }

        .file-upload-premium input[type="file"] {
            border-radius: 8px !important;
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            font-size: 0.85rem !important;
            color: var(--admission-muted) !important;
            width: 100%;
        }

        /* Submit Button */
        .btn-submit-premium {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%) !important;
            border: none !important;
            border-radius: 14px !important;
            padding: 14px 28px !important;
            font-weight: 700 !important;
            font-size: 0.98rem !important;
            letter-spacing: 0.01em !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.35) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .btn-submit-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.45) !important;
        }

        .btn-submit-premium:active {
            transform: translateY(0);
        }

        /* Switch Custom styling */
        .form-switch .form-check-input {
            width: 2.4em !important;
            height: 1.25em !important;
            cursor: pointer;
        }
        .form-switch .form-check-input:checked {
            background-color: var(--admission-primary) !important;
            border-color: var(--admission-primary) !important;
        }

        /* Responsive spacing */
        .admission-body {
            padding: 40px 32px;
        }

        @media (max-width: 576px) {
            .admission-header {
                padding: 36px 20px;
            }
            .admission-body {
                padding: 28px 20px;
            }
            .admission-title {
                font-size: 1.5rem;
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

    <div class="admission-wrapper">
        <div class="admission-card">
            
            {{-- HEADER BANNER --}}
            <div class="admission-header">
                <div class="logo-container">
                    <img src="{{ asset('images/logo.png') }}" alt="FOMS Academy Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                <h1 class="admission-title" style="color:black">
                    @if($type == 'student')
                        Student Admission
                    @else
                        Teacher Application
                    @endif
                </h1>
                <p class="admission-subtitle mb-0">
                    @if($type == 'student')
                        Welcome to FOMS Academy. Complete your admission details to begin.
                    @else
                        Join FOMS Academy and help shape the future of learning.
                    @endif
                </p>
            </div>

            {{-- BODY CONTAINER --}}
            <div class="admission-body">
                @if($expired)
                    <div class="text-center py-5">
                        <div class="mb-3 text-danger" style="font-size: 3.5rem;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Link Expired</h4>
                        <p class="text-muted">This form link is no longer valid. Please contact the administrator at FOMS Academy for assistance.</p>
                    </div>
                @else
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="border-left: 4px solid #dc3545 !important;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2" style="font-size: 1.15rem;"></i>
                                <div>
                                    {{ session('error') }}
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="border-left: 4px solid #dc3545 !important;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-circle me-2 mt-1" style="font-size: 1.15rem;"></i>
                                <div>
                                    <strong class="d-block mb-1">Please correct the following errors:</strong>
                                    <ul class="mb-0 ps-3">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admission.submit', [$type, $lead->form_token]) }}" enctype="multipart/form-data">
                        @csrf

                        {{-- ================= SECTION 1: PERSONAL DETAILS ================= --}}
                        <div class="section-title-premium">
                            Personal Details
                        </div>

                        <div class="row g-3 mb-4">
                            @if($type == 'student')
                                <div class="col-md-6">
                                    <label class="form-label-premium">Country <span class="text-danger">*</span></label>
                                    <select name="country_id" class="form-control form-control-premium" disabled>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" {{ ($lead->country_id ?? '') == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }} ({{ $country->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="country_id" value="{{ $lead->country_id }}">
                                    <small class="text-muted mt-1 d-block" style="font-size: 0.72rem;">Pre-filled based on registration country.</small>
                                </div>
                            @endif

                            <div class="col-md-{{ $type == 'student' ? '6' : '12' }}">
                                <label class="form-label-premium">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-premium @error('name') is-invalid @enderror"
                                    name="name" value="{{ old('name', $lead->name) }}" required placeholder="Enter full name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-premium">Contact Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-premium @error('contact_number') is-invalid @enderror"
                                    name="contact_number" value="{{ old('contact_number', $lead->contact_number) }}" required placeholder="Enter phone number">
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-premium">Email Address</label>
                                <input type="email" class="form-control form-control-premium @error('email') is-invalid @enderror" 
                                    name="email" value="{{ old('email', $lead->email) }}" placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-premium">Date of Birth</label>
                                <input type="date" class="form-control form-control-premium @error('dob') is-invalid @enderror" 
                                    name="dob" value="{{ old('dob') }}">
                                @error('dob')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($type == 'student')
                                <div class="col-md-6">
                                    <label class="form-label-premium">Parent Name</label>
                                    <input type="text" class="form-control form-control-premium @error('parent_name') is-invalid @enderror" 
                                        name="parent_name" value="{{ old('parent_name') }}" placeholder="Enter parent / guardian name">
                                    @error('parent_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="col-12">
                                <div class="form-check form-switch my-2">
                                    <input class="form-check-input" type="checkbox" id="whatsapp_different"
                                        name="is_whatsapp_different" value="1" {{ old('is_whatsapp_different') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="whatsapp_different" style="font-size: 0.85rem; cursor: pointer;">
                                        WhatsApp number is different?
                                    </label>
                                </div>
                            </div>

                            <div class="col-12" id="whatsapp_field_group" style="{{ old('is_whatsapp_different') ? '' : 'display: none;' }}">
                                <label class="form-label-premium">WhatsApp Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-premium @error('whatsapp_number') is-invalid @enderror"
                                    name="whatsapp_number" id="whatsapp_number" value="{{ old('whatsapp_number') }}"
                                    placeholder="Include country code (e.g. 919876543210)">
                                @error('whatsapp_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label-premium">Complete Address</label>
                                <textarea name="address" rows="3" class="form-control form-control-premium @error('address') is-invalid @enderror" 
                                    placeholder="Enter physical residential address">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        {{-- ================= SECTION 2: ACADEMIC DETAILS / PREFERENCES ================= --}}
                        @if($type == 'student')
                            <div class="section-title-premium">
                                Preferred Class Schedule
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label-premium">Preferred Class Days <span class="text-muted">(Select all that apply)</span></label>
                                    <div class="day-chips-grid mt-2">
                                        @php
                                            $days = ['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'];
                                            $selectedDays = old('selected_days', []);
                                        @endphp
                                        @foreach($days as $key => $day)
                                            <label class="day-chip">
                                                <input type="checkbox" name="selected_days[]" value="{{ $key }}"
                                                    class="class-day" {{ in_array($key, $selectedDays) ? 'checked' : '' }}>
                                                <span>{{ $day }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('selected_days')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label-premium">Classes Per Week</label>
                                    <input type="number" class="form-control form-control-premium" name="classes_per_week"
                                        id="classes_per_week" readonly value="0">
                                    <small class="text-muted mt-1 d-block" style="font-size: 0.72rem;">Calculated based on selected days.</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label-premium">Preferred Time Slot</label>
                                    <input type="text" class="form-control form-control-premium @error('time_slot') is-invalid @enderror" 
                                        name="time_slot" id="time_slot" value="{{ old('time_slot') }}" placeholder="Select time">
                                    @error('time_slot')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label-premium">Preferred Starting Date</label>
                                    <input type="date" class="form-control form-control-premium @error('starting_date') is-invalid @enderror" 
                                        name="starting_date" value="{{ old('starting_date') }}">
                                    @error('starting_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        @if($type == 'teacher')
                            <div class="section-title-premium">
                                Professional Background
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-premium">Highest Qualification</label>
                                    <input type="text" class="form-control form-control-premium @error('qualification') is-invalid @enderror" 
                                        name="qualification" value="{{ old('qualification') }}" placeholder="e.g. Master of Arts, PhD">
                                    @error('qualification')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-premium">Teaching Experience (Years)</label>
                                    <input type="number" class="form-control form-control-premium @error('experience') is-invalid @enderror" 
                                        name="experience" value="{{ old('experience') }}" placeholder="e.g. 5">
                                    @error('experience')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label-premium">UPI Number (For Salary Payments)</label>
                                    <input type="text" class="form-control form-control-premium @error('upi_number') is-invalid @enderror" 
                                        name="upi_number" value="{{ old('upi_number') }}" placeholder="Enter your UPI ID (e.g. name@upi)">
                                    @error('upi_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif


                        {{-- ================= SECTION 3: DOCUMENTS UPLOAD ================= --}}
                        <div class="section-title-premium">
                            Verification Documents
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label-premium">Passport Size Photo</label>
                                <div class="file-upload-premium position-relative">
                                    <input type="hidden" name="old_photo" value="{{ old('old_photo') }}">
                                    @if(old('old_photo'))
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . old('old_photo')) }}" class="img-thumbnail" style="max-height: 80px; border-radius: 8px;">
                                            <div class="text-success small fw-semibold mt-1">
                                                <i class="fas fa-check-circle"></i> Photo uploaded (select new file to replace)
                                            </div>
                                        </div>
                                    @else
                                        <i class="fas fa-image text-muted mb-2 d-block" style="font-size: 1.5rem;"></i>
                                    @endif
                                    <input type="file" class="form-control @error('photo') is-invalid @enderror" name="photo">
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-premium">Government ID Proof</label>
                                <div class="file-upload-premium position-relative">
                                    <input type="hidden" name="old_id_proof" value="{{ old('old_id_proof') }}">
                                    @if(old('old_id_proof'))
                                        <div class="mb-2">
                                            <div class="d-inline-block bg-light p-2 rounded mb-1" style="border: 1px solid #e2e8f0;">
                                                <i class="fas fa-file-pdf text-danger" style="font-size: 1.5rem;"></i>
                                                <span class="small text-muted ms-1">{{ basename(old('old_id_proof')) }}</span>
                                            </div>
                                            <div class="text-success small fw-semibold mt-1">
                                                <i class="fas fa-check-circle"></i> Document uploaded (select new file to replace)
                                            </div>
                                        </div>
                                    @else
                                        <i class="fas fa-file text-muted mb-2 d-block" style="font-size: 1.5rem;"></i>
                                    @endif
                                    <input type="file" class="form-control @error('id_proof') is-invalid @enderror" name="id_proof">
                                    @error('id_proof')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if($type == 'teacher')
                            <div class="mt-4">
                                <div class="form-check">
                                    <input class="form-check-input @error('agreed_rules') is-invalid @enderror" type="checkbox" id="agreed_rules"
                                        name="agreed_rules" value="1" {{ old('agreed_rules') ? 'checked' : '' }} required>
                                    <label class="form-check-label text-dark fw-semibold" for="agreed_rules" style="font-size: 0.9rem; cursor: pointer;">
                                        I have read and agree to the <a href="{{ asset('agreement/tutor_agreement.pdf') }}" target="_blank" class="text-primary text-decoration-underline">Rules & Regulations</a> <span class="text-danger">*</span>
                                    </label>
                                    @error('agreed_rules')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        {{-- SUBMIT BUTTON --}}
                        <div class="mt-5 text-center">
                            <button class="btn btn-submit-premium w-100 waves-effect waves-light" type="submit"
                                onclick="this.disabled=true; this.innerHTML='<span class=&quot;spinner-border spinner-border-sm me-2&quot;></span>Submitting...'; this.form.submit();">
                                @if($type == 'student')
                                    Submit Admission Form
                                @else
                                    Submit Application
                                @endif
                            </button>
                        </div>

                    </form>
                @endif
            </div>

            {{-- FOOTER BRANDING --}}
            <div class="text-center py-4 border-top" style="background: #fafafb;">
                <p class="mb-0 text-muted small fw-semibold">
                    &copy; <script>document.write(new Date().getFullYear())</script> FOMS ACADEMY. All rights reserved.
                </p>
            </div>

        </div>
    </div>

@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        // Init clock picker
        flatpickr("#time_slot", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            altInput: true,
            altFormat: "h:i K"
        });

        // WhatsApp fields toggling
        $('#whatsapp_different').on('change', function () {
            if ($(this).is(':checked')) {
                $('#whatsapp_field_group').slideDown(250);
                $('#whatsapp_number').attr('required', true);
            } else {
                $('#whatsapp_field_group').slideUp(250);
                $('#whatsapp_number').attr('required', false);
            }
        });

        // Calculate and update classes count
        function updateClassesPerWeek() {
            let count = $('.class-day:checked').length;
            $('#classes_per_week').val(count);
        }

        $('.class-day').on('change', function () {
            updateClassesPerWeek();
        });

        // Initial compute on render
        $(document).ready(function() {
            updateClassesPerWeek();
        });
    </script>
    @include('components.image-compressor')
@endsection
