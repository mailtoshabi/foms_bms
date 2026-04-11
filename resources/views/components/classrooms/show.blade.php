@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="row">

<div class="col-12">

{{-- ================= CLASS DETAILS ================= --}}
<div class="card">

<div class="card-header">
<h4>{{ $class->name }}</h4>
</div>

<div class="card-body">

<p><strong>Course:</strong> {{ $class->course->name ?? '-' }}</p>
<p><strong>Type:</strong> {{ ucfirst($class->classType->name ?? '-') }}</p>

<p><strong>{{ $class->classType->id == 1 ? 'First Month Fee' : 'Admission Fee' }}:</strong> &#8377; {{ number_format($class->admission_fee,2) }}</p>
<p><strong>Monthly Fee:</strong> &#8377; {{ number_format($class->monthly_fee,2) }}</p>

<p><strong>Days:</strong> {{ implode(', ', $class->selected_days ?? []) }}</p>

<p><strong>Time:</strong>
{{ $class->time_slot ? \Carbon\Carbon::parse($class->time_slot)->format('h:i A') : '-' }}
</p>

<p><strong>Duration:</strong> {{ $class->slot_duration }} mins</p>

</div>

</div>


{{-- ================= TEACHERS ================= --}}
<div class="card">

<div class="card-header d-flex justify-content-between">

<h5>Teachers</h5>

<button class="btn btn-sm btn-primary"
data-bs-toggle="modal"
data-bs-target="#assignTeacherModal"
{{ $class->teachers->count() ? 'disabled' : '' }} >

<i class="fas fa-plus"></i> Assign Teacher

</button>

</div>

<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered">

<thead>
<tr>
<th>Name</th>
<th>Phone</th>
<th>Wage/Hour</th>
<th>Action</th>
</tr>
</thead>

<tbody>

@forelse($class->teachers as $teacher)

<tr>

<td>{{ $teacher->name }}</td>
<td>{{ $teacher->phone }}</td>
<td>
&#8377; {{ number_format($teacher->pivot->hourly_wage,2) }}
</td>

<td>

<form method="POST"
action="{{ route('staff.class_rooms.remove.teacher') }}"
onsubmit="return confirm('Remove this teacher?')">

@csrf

<input type="hidden" name="class_room_id" value="{{ encrypt($class->id) }}">
<input type="hidden" name="teacher_id" value="{{ encrypt($teacher->id) }}">

<button class="btn btn-sm btn-danger">
<i class="fas fa-trash"></i>
</button>

</form>

</td>

</tr>

@empty

<tr>
<td colspan="2" class="text-center text-muted">
No teachers assigned
</td>
</tr>

@endforelse

</tbody>

</table>
</div>

</div>

</div>


{{-- ================= STUDENTS ================= --}}
<div class="card">

<div class="card-header d-flex justify-content-between">

<h5>Students</h5>

<button class="btn btn-sm btn-primary"
data-bs-toggle="modal"
data-bs-target="#assignStudentModal">

<i class="fas fa-user-plus"></i> Add

</button>

</div>

<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered">

<thead>
<tr>
<th>Name</th>
<th>Contact</th>
</tr>
</thead>

<tbody>

@forelse($class->students as $student)

<tr>

<td>{{ $student->name }}</td>
<td>{{ $student->contact_number }}</td>

<td>

<form method="POST"
action="{{ route('staff.class_rooms.remove.student') }}"
onsubmit="return confirm('Remove this student?')">

@csrf

<input type="hidden" name="class_room_id" value="{{ encrypt($class->id) }}">
<input type="hidden" name="student_id" value="{{ encrypt($student->id) }}">

<button class="btn btn-sm btn-danger">
<i class="fas fa-trash"></i>
</button>

</form>

</td>

</tr>

@empty

<tr>
<td colspan="2" class="text-center text-muted">
No students added
</td>
</tr>

@endforelse

</tbody>

</table>
</div>

</div>

</div>

</div>

</div>


{{-- ================= ASSIGN TEACHER MODAL ================= --}}
<div class="modal fade" id="assignTeacherModal">

<div class="modal-dialog">
<div class="modal-content">

<form method="POST" action="{{ $assignTeacherRoute }}">
@csrf

<input type="hidden" name="class_room_id" value="{{ encrypt($class->id) }}">

<div class="modal-header">
<h5>Assign Teacher</h5>
</div>

<div class="modal-body">

<div class="mb-3">

<label>Teacher</label>

<select name="teacher_id" class="form-control select2" required>
@foreach($teachers as $teacher)
<option value="{{ encrypt($teacher->id) }}">
{{ $teacher->name }}
</option>
@endforeach
</select>

</div>

<div class="mb-3">

<label>Wage Per Hour (&#8377;)</label>

<input type="number"
step="0.01"
name="hourly_wage"
class="form-control"
required>

</div>

</div>

<div class="modal-footer">
<button class="btn btn-primary" type="submit" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">Save</button>
</div>

</form>

</div>
</div>

</div>


{{-- ================= ASSIGN STUDENTS MODAL ================= --}}
<div class="modal fade" id="assignStudentModal">

<div class="modal-dialog modal-lg">
<div class="modal-content">

<form method="POST" action="{{ $assignStudentRoute }}">
@csrf

<input type="hidden" name="class_room_id" value="{{ encrypt($class->id) }}">

<div class="modal-header">
<h5>Add Students</h5>
</div>

<div class="modal-body">

<div class="row">

    @if(($class->classType->name ?? '') === 'individual')
        <small class="text-danger">
            Only one student allowed for this class
        </small>
    @endif

@foreach($allStudents as $student)

<div class="col-md-6 mb-2">

<label class="d-flex align-items-center border p-2 rounded">

@if(!$class->students->contains($student->id))
    <input type="checkbox"
    name="student_ids[]"
    value="{{ $student->id }}"
    class="form-check-input me-2">
@endif

<span>{{ $student->name }}</span>

</label>

</div>

@endforeach

</div>

</div>

<div class="modal-footer">
<button class="btn btn-primary" type="submit" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">Save</button>
</div>

</form>

</div>
</div>

</div>


@section('script')
    <script>
        @if(($class->classType->name ?? '') === 'individual')

        $('input[name="student_ids[]"]').on('change', function() {
            $('input[name="student_ids[]"]').not(this).prop('checked', false);
        });

        @endif
    </script>
@endsection
