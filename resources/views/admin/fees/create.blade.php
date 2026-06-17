@extends('admin.layouts.master')

@section('title') Add Manual Fee @endsection

@section('css')
<link href="{{ URL::asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')

@component('admin.breadcrumbs.breadcrumb')
@slot('li_1') Finance @endslot
@slot('li_2') Fees @endslot
@slot('title') Add Manual Fee @endslot
@endcomponent

<div class="row">
    <div class="col-xl-8 col-lg-10 col-12 mx-auto">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="card-title text-white mb-0"><i class="fas fa-plus-circle me-2"></i>Enter Fee Manually</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.fees.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="student_id" class="form-label fw-bold">Student <span class="text-danger">*</span></label>
                        <select name="student_id" id="student_id" class="form-control @error('student_id') is-invalid @enderror" required></select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="class_room_id" class="form-label fw-bold">Class Room <span class="text-danger">*</span></label>
                        <select name="class_room_id" id="class_room_id" class="form-control @error('class_room_id') is-invalid @enderror" required></select>
                        @error('class_room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="type" class="form-label fw-bold">Fee Type <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-control select2 @error('type') is-invalid @enderror" required>
                                <option value="monthly" {{ old('type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="admission" {{ old('type') == 'admission' ? 'selected' : '' }}>Admission</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="amount" class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" id="amount" 
                                class="form-control @error('amount') is-invalid @enderror" 
                                value="{{ old('amount') }}" 
                                placeholder="0.00" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="date" class="form-label fw-bold">Fee Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" 
                                class="form-control @error('date') is-invalid @enderror" 
                                value="{{ old('date', date('Y-m-d')) }}" required>
                            <small class="text-muted">Due date will be automatically set to 7 days after this date.</small>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4" 
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                            <i class="fas fa-save me-1"></i> Save Fee
                        </button>
                        <a href="{{ route('admin.reports.fee') }}" class="btn btn-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('assets/libs/select2/select2.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        minimumResultsForSearch: Infinity,
        width: '100%'
    });

    $('#student_id').select2({
        placeholder: 'Search student by name, contact, or admission number...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 1,
        ajax: {
            url: "{{ route('admin.fees.students.search') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term || '' };
            },
            processResults: function(data) {
                return { results: data.results };
            },
            cache: true
        }
    });

    $('#class_room_id').select2({
        placeholder: 'Search classroom...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        ajax: {
            url: "{{ route('admin.class_rooms.search') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term || '' };
            },
            processResults: function(data) {
                return { results: data.results };
            },
            cache: true
        }
    });
});
</script>
@endsection
