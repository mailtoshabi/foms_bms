
@section('title','Teacher Details')

@section('content')

<div class="row">

{{-- =========================
   TEACHER PROFILE
========================= --}}
<div class="col-md-4">

<div class="card">

<div class="card-header">
<h5>Teacher Profile</h5>
</div>

<div class="card-body text-center">

@if($teacher->photo)
<img src="{{ asset('storage/'.$teacher->photo) }}"
class="rounded-circle mb-3"
width="120">
@endif

<h5>{{ $teacher->name }}</h5>

<p class="text-muted mb-1">
{{ $teacher->phone }}
</p>

<p class="text-muted mb-1">
{{ $teacher->email ?? '-' }}
</p>

<span class="badge bg-success">
{{ ucfirst($teacher->status) }}
</span>

@php $rank = teacherRankData($teacher->id); @endphp

<div class="mt-2 mb-1">
    <span class="badge bg-{{ $rank['color'] }} fs-6 px-3 py-2">{{ $rank['label'] }}</span>
</div>
<div class="mb-1">
    @for($s = 1; $s <= 5; $s++)
        <span style="font-size:1.2rem; color: {{ $s <= $rank['stars'] ? '#f1c40f' : '#ccc' }}">&#9733;</span>
    @endfor
</div>
<small class="text-muted">Score: {{ $rank['score'] }}</small>

<hr>

<p><strong>Qualification:</strong></p>
<p class="text-muted">
{{ $teacher->qualification ?? '-' }}
</p>

<p><strong>Experience:</strong></p>
<p class="text-muted">
{{ $teacher->experience ?? '-' }}
</p>

<p><strong>Address:</strong></p>
<p class="text-muted">
{{ $teacher->address ?? '-' }}
</p>

</div>

</div>

</div>


{{-- =========================
   SALARY PAYMENT HISTORY
========================= --}}
<div class="col-md-8">

<div class="card">

<div class="card-header d-flex justify-content-between align-items-center">

<h5 class="mb-0">Latest Salary Details</h5>

{{-- <button class="btn btn-sm btn-primary"
data-bs-toggle="modal"
data-bs-target="#salaryModal">

<i class="fas fa-plus"></i> Add Salary

</button> --}}

{{-- <a class="btn btn-sm btn-primary" href="{{ route('staff.process.teacher.salary',encrypt($teacher->id)) }}"><i class="fas fa-plus"></i> Add Salary</a> --}}

</div>

<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered table-sm">

<thead>
<tr>
<th>Due Date</th>
<th>Total Amount</th>
<th>Method</th>
<th>status</th>
<th width="120">Action</th>
</tr>
</thead>

<tbody>

@forelse($teacher->salaries as $salary)

<tr>

<td>{{ $teacher->SalaryCreditDate }}</td>

<td>&#8377; {{ number_format($salary->total_amount,2) }}</td>

<td>{{ ucfirst($salary->payment_method ?? '-') }}</td>

<td>{{ $salary->status }}</td>

<td>

<button class="btn btn-sm  editSalary {{ $salary->status == 'paid' ? 'disabled' : '' }}" title="Make Payment"
data-id="{{ $salary->id }}"
data-total_amount="{{ $salary->total_amount }}"
data-method="{{ $salary->payment_method }}"
data-date="{{ optional($salary->payment_date)->format('d M Y') }}"
data-notes="{{ $salary->notes }}">

<i class="fas fa-money-bill-wave text-success"></i>

</button>

</td>

</tr>

@empty

<tr>
<td colspan="5" class="text-center text-muted">
No salary payments yet
</td>
</tr>

@endforelse

</tbody>

</table>
</div>

</div>

</div>

</div>

{{-- =========================
   CLASS LIST
========================= --}}
<div class="col-md-12">

<div class="card">

<div class="card-header d-flex justify-content-between align-items-center">

<h5 class="mb-0">Assigned Classes</h5>
@if($showButtons=='true')
<button class="btn btn-primary btn-sm"
data-bs-toggle="modal"
data-bs-target="#assignClassModal">

<i class="fas fa-plus"></i> Assign Classes

</button>
@endif
</div>

<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered">

<thead>
<tr>
<th>Course</th>
<th>Class</th>
<th>Type</th>
<th>Days</th>
<th>Hourly Wage</th>
<th width="100">Action</th>
</tr>
</thead>

<tbody>

@forelse($teacher->classRooms as $class)

<tr>

<td>{{ $class->course->name ?? '-' }}</td>

<td>{{ $class->name }}</td>

<td>{{ ucfirst($class->classType->name ?? '-') }}</td>

<td>
    @if($class->selected_days)

    <small>

    {{ implode(', ', $class->selected_days ?? []) }}

    <br>

    {{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}

    </small>

    @endif
</td>

<td>

<form method="POST"
action="{{ route('staff.teachers.update.wage') }}"
class="d-flex">

@csrf
@method('PUT')

<input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
<input type="hidden" name="class_room_id" value="{{ $class->id }}">

<input type="number"
step="0.01"
name="hourly_wage"
value="{{ $class->pivot->hourly_wage }}"
class="form-control form-control-sm me-2"
style="width:120px">

<button class="btn btn-sm btn-success" type="submit" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
<i class="fas fa-save"></i>
</button>

</form>

</td>

<td>

<form method="POST"
action="{{ route('staff.teachers.classrooms.destroy',[$teacher->id,$class->id]) }}" onsubmit="return confirm('Remove Teacher From the class?')">

@csrf
@method('DELETE')

<button class="btn btn-sm btn-danger">
<i class="fas fa-times"></i>
</button>

</form>

</td>

</tr>

@empty

<tr>
<td colspan="5" class="text-center text-muted">
No classes assigned
</td>
</tr>

@endforelse

</tbody>

</table>
</div>

</div>

</div>

</div>



{{-- =========================
   CLASS NOTES CREATED
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
   WHATSAPP CREDENTIALS
========================= --}}
<div class="col-md-12">

<div class="card">

<div class="card-header">
<h5>Send Login Credentials</h5>
</div>

<div class="card-body">

<a href="{{ teacherWhatsappMessage($teacher,$teacher->phone) }}"
class="btn btn-success">

<i class="fab fa-whatsapp"></i> Send Credentials

</a>

</div>

</div>

</div>


</div>


{{-- Modal for assigne class to teacher --}}
<div class="modal fade" id="assignClassModal">

<div class="modal-dialog">
<div class="modal-content">

<form method="POST" action="{{ route('staff.teachers.assign.classrooms') }}">

@csrf

<input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

<div class="modal-header">
<h5 class="modal-title">Assign Class</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

{{-- Class Selection --}}
<div class="mb-3">

<label class="form-label">Class Room</label>

<select name="class_room_id" class="form-control select2-class-ajax"
    data-ajax-url="{{ route('staff.class_rooms.search') }}"
    required>

<option value="">Search class...</option>

</select>

</div>

{{-- Hourly Wage --}}
<div class="mb-3">

<label class="form-label">Wage Per Hour (&#8377;)</label>

<input type="number"
step="0.01"
name="hourly_wage"
class="form-control"
placeholder="Enter hourly wage">

</div>

</div>

<div class="modal-footer">

<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancel

</button>

<button class="btn btn-primary" type="submit" onclick="this.disabled=true; this.innerText='Assigning...'; this.form.submit();">

Assign Class

</button>

</div>

</form>

</div>
</div>

</div>


{{-- salary modal --}}

<div class="modal fade" id="salaryModal">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST" id="salaryForm"
action="">
{{-- {{ route('staff.teacher-salaries.store',$teacher->id) }} --}}
<input type="hidden" name="status" >
@csrf
@method('PUT')

<div class="modal-header">

<h5 class="salary_modal modal-title">Add Salary Payment</h5>

<button type="button" class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="mb-3">

<label class="form-label">Amount</label>

<input readonly type="number"
name="total_amount"
class="form-control"
required>

</div>


<div class="mb-3">

<label class="form-label">Payment Date</label>

<input type="date"
name="payment_date"
class="form-control"
required>

</div>


<div class="mb-3">

<label class="form-label">Payment Method</label>

<select name="payment_method"
class="form-control">

<option value="cash">Cash</option>
<option value="card">Card</option>
<option value="upi">UPI</option>
<option value="bank">Bank Transfer</option>
</select>

</div>


<div class="mb-3">

<label class="form-label">Notes</label>

<textarea name="notes"
class="form-control"
rows="2"></textarea>

</div>

</div>


<div class="modal-footer">

<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancel

</button>

<button class="btn btn-primary" type="submit" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">

Save Payment

</button>

</div>

</form>

</div>

</div>

</div>

{{-- Teacher attendance modal --}}

<div class="modal fade" id="attendanceModal">

<div class="modal-dialog">
<div class="modal-content">

<form method="POST" action="">
    {{-- {{ route('staff.teacher-attendance.store') }} --}}

@csrf

<div class="modal-header">
<h5 class="modal-title">Mark Teacher Attendance</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

{{-- Class --}}
<div class="mb-3">

<label class="form-label">Class Room</label>

<select name="class_room_id" class="form-control select2" required>

<option value="">Select Class</option>

@foreach($teacher->classRooms as $class)

<option value="{{ $class->id }}">
{{ $class->name }}
</option>

@endforeach

</select>

</div>


{{-- Attendance Date --}}
<div class="mb-3">

<label class="form-label">Attendance Date</label>

<input type="date"
name="attendance_date"
class="form-control"
required>

</div>


{{-- Session Link --}}
<div class="mb-3">

<label class="form-label">Session Link</label>

<input type="url"
name="google_meet_link"
class="form-control"
placeholder="https://meet.google.com/...">

<small class="text-muted">
Optional â€“ for online classes
</small>

</div>


{{-- Attendance --}}
<div class="mb-3">

<label class="form-label">Attendance</label>

<select name="is_present" class="form-control">

<option value="1">Present</option>
<option value="0">Absent</option>

</select>

</div>

</div>

<div class="modal-footer">

<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">
Cancel
</button>

<button class="btn btn-primary" type="submit" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
Save Attendance
</button>

</div>

</form>

</div>
</div>

</div>

@endsection



@section('script')

{{-- <script>

$('.select2').select2({
placeholder:'Select Class Rooms',
width:'100%'
});

</script> --}}

<script>

// $('.select2').select2({
// dropdownParent: $('#assignClassModal'),
// width:'100%'
// });

</script>

<script>

$('.editSalary').click(function(){

let id = $(this).data('id');
let total_amount = $(this).data('total_amount');
let method = $(this).data('method');
let date = $(this).data('date');
let notes = $(this).data('notes');

$('#salaryForm').attr('action','/departments/teachers/salaries/'+id);

$('input[name=total_amount]').val(total_amount);
$('input[name=status]').val('paid');
$('input[name=payment_date]').val(date);
$('select[name=payment_method]').val(method);
$('textarea[name=notes]').val(notes);

$('.salary_modal.modal-title').text('Pay Salary');

$('#salaryModal').modal('show');

});

</script>

@endsection
