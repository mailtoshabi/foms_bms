@extends('teacher.layouts.master')

@section('title','Class Details')

@section('content')

<div class="card">

<div class="card-header d-flex justify-content-between">

<h4>{{ $class->name }}</h4>

<button
class="btn btn-success"
data-bs-toggle="modal"
data-bs-target="#startClassModal">

Create New Session

</button>

</div>

<div class="card-body">

<p><strong>Course:</strong> {{ $class->course->name ?? '-' }}</p>

<p><strong>Type:</strong> {{ ucfirst($class->classType->name ?? '-') . ' Class' }}</p>

<p><strong>Days:</strong> {{ implode(', ',$class->selected_days ?? []) }}</p>

<p><strong>Time:</strong> {{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}</p>

<p><strong>Duration:</strong> {{ $class->slot_duration }} minutes</p>
<p><strong>Monthly Sessions:</strong> {{ $class->classes_per_week * 4 }} </p>
<p><strong>Hourly Wage:</strong>  &#8377;{{ $class->teachers()->find(Auth::guard('teacher')->user()->id)->pivot->hourly_wage }} </p>


<hr>

<h5>Students</h5>

<div class="table-responsive">
<table class="table table-bordered">

<thead>
<tr>
<th>Name</th>
<th>Contact</th>
<th>Attendance %</th>
</tr>
</thead>

<tbody>

@foreach($class->students as $student)

@php
$stat = $attendanceStats[$student->id] ?? null;

$present = $stat->present ?? 0;
$total = $totalClasses ?: 1; // avoid divide by zero

$percentage = round(($present / $total) * 100);
@endphp

<tr>

<td>{{ $student->name }}</td>

<td>{{ $student->contact_number }}</td>

<td>

<span class="badge
{{ $percentage >= 75 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}">

{{ $present }}/{{ $total }} ({{ $percentage }}%)

</span>

</td>

</tr>

@endforeach

</tbody>

</table>
</div>

<hr>

<h5>Sessions</h5>

<div class="table-responsive">
<table class="table table-bordered">

<thead>
<tr>
<th>Date</th>
<th>Session Link</th>
<th>Status</th>
<th width="150">Action</th>
</tr>
</thead>

<tbody>

@forelse($class->classHours as $hour)

<tr>

<td>{{ $hour->created_at->format('d M Y h:i A') }}</td>

<td>
@if($hour->status == 'pending')
{{-- <a href="" target="_blank">
Join
</a> --}}
{{ $hour->google_meet_link }}
<button class="btn btn-primary"
onclick="navigator.clipboard.writeText('{{ $hour->google_meet_link }}')">
<i class="mdi mdi-content-copy"></i>
</button>
@else
Expired
@endif
</td>

<td>
@if($hour->status == 'completed')
    <span class="badge bg-success">Completed</span>
@else
    <span class="badge bg-warning">Pending</span>
@endif
</td>

<td>

{{-- Edit Button --}}
@if($hour->status == 'pending')
<button class="btn btn-sm btn-warning editClassHour"
data-id="{{ $hour->id }}"
data-link="{{ $hour->google_meet_link }}">
<i class="fas fa-edit"></i>
</button>
@else

<button class="btn btn-sm btn-secondary" disabled>
    <i class="fas fa-lock"></i>
</button>

@endif

{{-- Mark as Completed --}}
@if($hour->status == 'pending')

<button class="btn btn-sm btn-success openAttendanceModal"
data-id="{{ $hour->id }}">
<i class="fas fa-check"></i>
</button>

@endif

</td>

</tr>

@empty

<tr>
<td colspan="3" class="text-center text-muted">
No Sessions created
</td>
</tr>

@endforelse

</tbody>

</table>
</div>

<hr>
<h5>Class Notes</h5>
<div id="classNotesList">
    @forelse($class->notes as $note)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>
                            {{ $note->title }}
                            <span class="badge badge-{{ $note->visibility === 'public' ? 'info' : 'warning' }}">
                                {{ ucfirst($note->visibility) }}
                            </span>
                        </h6>
                        <small class="text-muted">
                            By {{ $note->teacher->name ?? '-' }} • {{ $note->created_at->format('M d, Y H:i') }}
                        </small>
                        @if($note->content)
                            <p class="text-muted mt-2 mb-0">{{ Str::limit($note->content, 100) }}</p>
                        @endif
                    </div>
                    @if($note->teacher_id === Auth::guard('teacher')->user()->id)
                    <div class="dropdown ms-2">
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                            <i class="mdi mdi-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item text-danger delete-note" href="#" data-note-id="{{ encrypt($note->id) }}">
                                    <i class="mdi mdi-trash-can"></i> Delete Note
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif
                </div>

                {{-- Files List --}}
                @if($note->files->count() > 0)
                <div class="mt-3">
                    <small class="text-muted d-block mb-2">
                        <i class="mdi mdi-file"></i> {{ $note->files->count() }} file(s)
                    </small>
                    <div class="list-group list-group-sm">
                        @foreach($note->files as $file)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="mdi mdi-file text-muted me-2"></i>
                                <strong>{{ $file->name }}</strong>
                                <small class="text-muted d-block">{{ $file->size }}</small>
                            </div>
                            <a href="{{ route('teacher.notes.show', encrypt($file->id)) }}" class="btn btn-sm btn-primary">
                                <i class="mdi mdi-eye"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    @empty
        <p class="text-muted">No notes added for this class yet.</p>
    @endforelse
</div>

</div>

</div>


{{-- Create Session MODAL --}}

<div class="modal fade" id="startClassModal">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST"
action="{{ route('teacher.classes.start') }}">

@csrf

<input type="hidden"
name="class_room_id"
value="{{ $class->id }}">

<div class="modal-header">
<h5>Create A Session</h5>
</div>

<div class="modal-body">

<div class="mb-3">

<label>Session Link</label>

<input type="url"
name="google_meet_link"
class="form-control"
required>

</div>

</div>

<div class="modal-footer">

<button class="btn btn-success">

Create

</button>

</div>

</form>

</div>

</div>

</div>

{{-- Eidt Class modal --}}

<div class="modal fade" id="editClassHourModal">

<div class="modal-dialog">
<div class="modal-content">

<form method="POST" id="editClassHourForm">
@csrf
@method('PUT')

<div class="modal-header">
<h5>Edit Class Hour</h5>
</div>

<div class="modal-body">

<div class="mb-3">

<label>Session Link</label>

<input type="url"
name="google_meet_link"
id="edit_meet_link"
class="form-control"
required>

</div>

</div>

<div class="modal-footer">

<button class="btn btn-primary">
Update
</button>

</div>

</form>

</div>
</div>

</div>

{{-- attendance modal --}}

<div class="modal fade" id="attendanceModal">

<div class="modal-dialog modal-lg">
<div class="modal-content">

<form method="POST" id="attendanceForm">
@csrf

<div class="modal-header">
<h5>Mark Attendance</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    <div class="mb-2">
        <button type="button" class="btn btn-sm btn-success" id="checkAll">All</button>
        <button type="button" class="btn btn-sm btn-secondary" id="uncheckAll">None</button>
    </div>

<div class="row" id="attendanceList">

{{-- Filled via JS --}}

</div>

</div>

<div class="modal-footer">

<button class="btn btn-success">
Save & Complete Class
</button>

</div>

</form>

</div>
</div>

</div>

@endsection

@section('script')
    <script>

    $('.editClassHour').click(function(){

    let id = $(this).data('id');
    let link = $(this).data('link');

    $('#edit_meet_link').val(link);

    // set dynamic action
    $('#editClassHourForm').attr(
        'action',
        '/teacher/class-hours/'+id
    );

    $('#editClassHourModal').modal('show');

    });

    </script>

    <script>

        $('.openAttendanceModal').click(function(){

            let classHourId = $(this).data('id');

            $('#attendanceForm').attr(
                'action',
                '/teacher/class-hours/'+classHourId+'/complete'
            );

            $.get('/teacher/class-hours/'+classHourId+'/students', function(res){

                let html = '';

                res.students.forEach(student => {

                    html += `
                        <div class="col-md-6 mb-2">

                            <label class="d-flex align-items-center border rounded p-2">

                                <input type="checkbox"
                                name="attendance[${student.id}]"
                                value="1"
                                class="form-check-input me-2"
                                checked>

                                <span>${student.name}</span>

                            </label>

                        </div>
                    `;

                });

                $('#attendanceList').html(html);

                $('#attendanceModal').modal('show');

            });

        });

    </script>

    <script>
        $('#checkAll').click(function(){
            $('input[name^="attendance"]').prop('checked', true);
        });

        $('#uncheckAll').click(function(){
            $('input[name^="attendance"]').prop('checked', false);
        });
    </script>
@endsection

@section('css')
    <style>
        input:not(:checked) + span {
            color: #dc3545;
        }
    </style>
@endsection
