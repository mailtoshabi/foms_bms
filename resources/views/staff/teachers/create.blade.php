@extends('staff.layouts.master')

@php
    $isEdit = isset($teacher);
@endphp

@section('title', $isEdit ? 'Edit Teacher' : 'Add Teacher')

@section('content')

    <div class="row">

        <form method="POST"
            action="{{ $isEdit ? route('staff.teachers.update', encrypt($teacher->id)) : route('staff.teachers.store') }}"
            enctype="multipart/form-data">

            @csrf

            @if($isEdit)
                @method('PUT')
            @endif


            <div class="col-12">


                {{-- ================= TEACHER DETAILS ================= --}}
                <div class="card">

                    <div class="card-header d-flex align-items-center">
                        <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <h4 class="card-title mb-0">Teacher Details</h4>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-12 mb-3">
                                <label>Country <span class="text-danger">*</span></label>
                                <select name="country_id" class="form-control @error('country_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id', $teacher->country_id ?? '') == $country->id || (!old('country_id') && !isset($teacher) && $country->name == 'India') ? 'selected' : '' }}>
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
                                    value="{{ old('name', $teacher->name ?? '') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-6 mb-3">
                                <label>Contact Number</label>
                                <input type="text" name="contact_number" id="contact_number"
                                    class="form-control @error('contact_number') is-invalid @enderror @error('phone') is-invalid @enderror" maxlength="15"
                                    value="{{ old('contact_number', $teacher->contact_number ?? '') }}">
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="whatsapp_different" name="is_whatsapp_different" value="1" {{ old('is_whatsapp_different', $teacher->is_whatsapp_different ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="whatsapp_different">WhatsApp number is different?</label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3" id="whatsapp_field_group" style="{{ old('is_whatsapp_different', $teacher->is_whatsapp_different ?? false) ? '' : 'display: none;' }}">
                                <label>WhatsApp Number (with country code)</label>
                                <input type="text" name="whatsapp_number" id="whatsapp_number" class="form-control"
                                    value="{{ old('whatsapp_number', $teacher->whatsapp_number ?? '') }}"
                                    placeholder="e.g. 919876543210">
                            </div>


                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $teacher->email ?? '') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-6 mb-3">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror"
                                    value="{{ old('dob', isset($teacher) && $teacher->dob ? $teacher->dob->format('Y-m-d') : '') }}">
                                @error('dob')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-6 mb-3">
                                <label>Qualification</label>
                                <input type="text" name="qualification" class="form-control @error('qualification') is-invalid @enderror"
                                    value="{{ old('qualification', $teacher->qualification ?? '') }}">
                                @error('qualification')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-6 mb-3">
                                <label>Experience (Years)</label>
                                <input type="number" name="experience" class="form-control @error('experience') is-invalid @enderror"
                                    value="{{ old('experience', $teacher->experience ?? '') }}">
                                @error('experience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-6 mb-3">
                                <label>UPI Number</label>
                                <input type="text" name="upi_number" class="form-control @error('upi_number') is-invalid @enderror"
                                    value="{{ old('upi_number', $teacher->upi_number ?? '') }}">
                                @error('upi_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-12 mb-3">
                                <label>Address</label>
                                <textarea name="address"
                                    class="form-control @error('address') is-invalid @enderror">{{ old('address', $teacher->address ?? '') }}</textarea>
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



                {{-- ================= LOGIN INFORMATION ================= --}}
                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title">Login Information</h4>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label>Phone (Login)</label>

                                <input type="text" name="phone" id="phone" class="form-control" readonly
                                    value="{{ old('phone', $teacher->phone ?? '') }}">

                            </div>


                            <div class="col-md-6 mb-3 {{ $isEdit ? '' : 'required' }}">

                                <label>Password</label>

                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
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
                            {{ $isEdit ? 'Update Teacher' : 'Save Teacher' }}
                        </button>

                        <a href="{{ route('staff.teachers.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>

                    </div>

                </div>


            </div>
        </form>
    </div>

@endsection


@section('script')

    @if(!$isEdit)

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
                $('#whatsapp_number').val('');
            }
        });
    </script>

@endsection