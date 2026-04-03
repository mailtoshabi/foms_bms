
@section('title','Student Details')

@section('content')

<div class="row">

{{-- =========================
   STUDENT PROFILE
========================= --}}
<div class="col-md-4">

<div class="card">

<div class="card-header">
<h5>Student Profile</h5>
</div>

<div class="card-body text-center">

@if($student->photo)
<img src="{{ asset('storage/'.$student->photo) }}"
class="rounded-circle mb-3"
width="120">
@endif

<h5>{{ $student->name }}</h5>

<p class="text-muted mb-1">
{{ $student->contact_number }}
</p>

<p class="text-muted mb-1">
{{ $student->email ?? '-' }}
</p>

<span class="badge bg-success">
{{ ucfirst($student->status) }}
</span>

<hr>

<p><strong>Parent:</strong> {{ $student->parent_name ?? '-' }}</p>

<p><strong>DOB:</strong>
{{ $student->dob ? $student->dob->format('d M Y') : '-' }}
</p>

<p><strong>Address:</strong></p>
<p class="text-muted">
{{ $student->address ?? '-' }}
</p>

</div>

</div>

</div>



{{-- =========================
   CLASS / COURSE DETAILS
========================= --}}
<div class="col-md-8">

<div class="card">

<div class="card-header d-flex justify-content-between align-items-center">

<h5 class="mb-0">Course & Class Details</h5>
@if($showButtons=='true')
    <button class="btn btn-sm btn-primary"
    data-bs-toggle="modal"
    data-bs-target="#assignClassModal">

    <i class="fas fa-plus"></i> Assign Class

    </button>
@endif

</div>

<div class="card-body">

<table class="table table-bordered">

<thead>
<tr>
<th>Course</th>
<th>Class</th>
<th>Type</th>
<th>Days</th>
<th>Time</th>
</tr>
</thead>

<tbody>

@foreach($student->class_rooms as $class)

<tr>

<td>{{ $class->course->name ?? '-' }}</td>

<td>{{ $class->name }}</td>

<td>{{ ucfirst($class->classType->name ?? '-') }}</td>

<td>
    @if($class->selected_days)

    <small>

    {{ implode(', ', $class->selected_days ?? []) }}


    </small>

    @endif
</td>

<td>{{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

</div>


{{-- =========================
   TEACHER DETAILS
========================= --}}
<div class="col-md-6">

<div class="card">

<div class="card-header">
<h5>Teacher Details</h5>
</div>

<div class="card-body">

<table class="table table-bordered">

<thead>
<tr>
<th>Name</th>
<th>Phone</th>
<th>Email</th>
</tr>
</thead>

<tbody>

@foreach($teachers as $teacher)

<tr>

<td>{{ $teacher->name }}</td>
<td>{{ $teacher->phone }}</td>
<td>{{ $teacher->email }}</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

</div>



{{-- =========================
   ATTENDANCE DETAILS
========================= --}}
<div class="col-md-6">

<div class="card">

<div class="card-header">
<h5>Attendance Summary</h5>
</div>

<div class="card-body">

<p><strong>Total Classes:</strong> {{ $attendance['total'] }}</p>

<p><strong>Present:</strong>
<span class="text-success">
{{ $attendance['present'] }}
</span>
</p>

<p><strong>Absent:</strong>
<span class="text-danger">
{{ $attendance['absent'] }}
</span>
</p>

</div>

</div>

</div>



{{-- =========================
   FEE PAYMENT DETAILS
========================= --}}
<div class="col-md-12">

<div class="card">

<div class="card-header">
<h5>Fee Payment History</h5>
</div>

<div class="card-body">

<table class="table table-bordered">

<thead>
<tr>
<th>Date</th>
<th>Class</th>
<th>Type</th>
<th>Amount</th>
<th>Status</th>
</tr>
</thead>

<tbody>

@foreach($student->fees as $fee)
@php
$paid = $fee->paid_amount ?? 0;
$remaining = $fee->amount - $paid;
@endphp
<tr>

<td>{{ $fee->created_at->format('d M Y') }}</td>
<td>{{ $fee->classRoom->name }}</td>
<td>{{ ucfirst($fee->type) . ' Fee' }}</td>

<td>
    <strong>₹ {{ number_format($fee->amount,2) }}</strong><br>

    <small class="text-success">
        Paid: ₹ {{ number_format($paid,2) }}
    </small><br>

    <small class="text-danger">
        Remaining: ₹ {{ number_format($remaining,2) }}
    </small>
    @php
        $percentage = $fee->amount > 0 ? ($paid / $fee->amount) * 100 : 0;
    @endphp

    <div class="progress mt-1" style="height:6px;" title="{{ round($percentage) }}% paid">
        <div class="progress-bar bg-success"
            style="width: {{ $percentage }}%">
        </div>
    </div>
</td>

<td>
    @php
        $badgeClasses = [
            'paid' => 'bg-success',
            'partial' => 'bg-warning',
            'unpaid' => 'bg-danger',
        ];
    @endphp
<span class="badge {{ $badgeClasses[$fee->status] ?? 'bg-secondary' }}">
{{ ucfirst($fee->status ?? '-') }}
</span>
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

</div>



{{-- =========================
   CLASS NOTES
========================= --}}
<div class="col-md-12">

<div class="card">

<div class="card-header">
<h5>Class Notes</h5>
</div>

<div class="card-body">

@forelse($notes as $note)

<div class="border rounded p-3 mb-3">

<strong>{{ $note->title }}</strong>

<p class="text-muted small">
{{ $note->created_at->format('d M Y') }}
</p>

<p>{{ $note->content }}</p>

</div>

@empty

<p class="text-muted">No notes available.</p>

@endforelse

</div>

</div>

</div>


{{-- =========================
   Whasapp link
========================= --}}
<div class="col-md-12">

<div class="card">

<div class="card-header">
<h5>Send Login Credentials</h5>
</div>

<div class="card-body">

<a href="{{ studentWhatsappMessage($student,$student->phone) }}"
class="btn btn-success">

<i class="fab fa-whatsapp"></i> Send Credentials

</a>

</div>

</div>

</div>


{{-- =========================
   Fee exemption
========================= --}}
<div class="col-md-12 mb-5">

    <div class="card-header d-flex justify-content-between align-items-center">

    <h5 class="mb-0">Fee Exemption</h5>
    @if($showButtons=='true')
        <button class="btn btn-sm btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#feeExemptionModal">

        <i class="fas fa-plus"></i> Add Fee Exemption

        </button>
    @endif
    </div>

    <div class="card-body">

    <div class="row">

        <div class="col-md-6 mb-2">

        <strong>Admission Fee:</strong>

        @if($student->is_admission_fee_exempted)
            <span class="text-success ms-2">
                <i class="fas fa-check-circle"></i> Exempted
            </span>
        @else
            <span class="text-muted ms-2">
                <i class="fas fa-times-circle"></i> Not Exempted
            </span>
        @endif

        </div>

        <div class="col-md-6 mb-2">

        <strong>Monthly Fee:</strong>

        {{-- @if($student->is_monthly_fee_exempted)
            <span class="badge bg-success ms-2">Exempted</span>
        @else
            <span class="badge bg-secondary ms-2">Not Exempted</span>
        @endif --}}

        @if($student->is_monthly_fee_exempted)
            <span class="text-success ms-2">
                <i class="fas fa-check-circle"></i> Exempted
            </span>
        @else
            <span class="text-muted ms-2">
                <i class="fas fa-times-circle"></i> Not Exempted
            </span>
        @endif

        </div>

    </div>

    </div>

</div>


</div>

{{-- Assign Class Modal --}}

<div class="modal fade" id="assignClassModal">

<div class="modal-dialog">
<div class="modal-content">

<form method="POST"
action="{{ route('staff.students.assign.class') }}">

@csrf

<input type="hidden" name="student_id" value="{{ $student->id }}">

<div class="modal-header">

<h5 class="modal-title">Assign Class</h5>

<button type="button"
class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="mb-3">

<label class="form-label">Select Class</label>

<select name="class_room_id"
class="form-control select2"
required>

<option value="">Select Class</option>

@foreach($classRooms as $class)

<option value="{{ $class->id }}">
{{ $class->name }} ({{ $class->course->name ?? '' }})
</option>

@endforeach

</select>

</div>

</div>

<div class="modal-footer">

<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancel

</button>

<button class="btn btn-primary">

Assign Class

</button>

</div>

</form>

</div>
</div>

</div>

{{-- Fee exemption Modal --}}

<div class="modal fade" id="feeExemptionModal">

<div class="modal-dialog">
<div class="modal-content">

<form method="POST"
action="{{ route('staff.students.fee.exemption') }}">

@csrf

<input type="hidden" name="student_id" value="{{ $student->id }}">

<div class="modal-header">
<h5 class="modal-title">Fee Exemption</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="form-check mb-3">

<input type="checkbox"
name="is_admission_fee_exempted"
value="1"
class="form-check-input"
{{ $student->is_admission_fee_exempted ? 'checked' : '' }}>

<label class="form-check-label">
Admission Fee Exemption
</label>

</div>

<div class="form-check">

<input type="checkbox"
name="is_monthly_fee_exempted"
value="1"
class="form-check-input"
{{ $student->is_monthly_fee_exempted ? 'checked' : '' }}>

<label class="form-check-label">
Monthly Fee Exemption
</label>

</div>

</div>

<div class="modal-footer">

<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancel

</button>

<button class="btn btn-primary">

Save

</button>

</div>

</form>

</div>
</div>

</div>

@endsection
