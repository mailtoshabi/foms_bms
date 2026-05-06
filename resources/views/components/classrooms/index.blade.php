@props([
    'class_rooms',
    'courses',
    'types',
    'createRoute',
    'indexRoute',
    'editRoute',
    'deleteRoute',
    'showRoute',
    'classRoomSearchUrl',
    'selectedClassName'
])

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">

<div class="card-header d-flex justify-content-between">

<h4>Classes ({{ $class_rooms->total() }})</h4>

<a href="{{ $createRoute }}" class="btn btn-primary">
Add Class
</a>

</div>


<div class="card-body table-responsive">

{{-- ================= FILTER ================= --}}
<form method="GET" class="row mb-3">

<div class="col-md-2">
    <label class="form-label fw-bold">Course</label>
    <select name="course_id" class="form-control select2">
        <option value="">All Courses</option>
        @foreach($courses as $course)
            <option value="{{ $course->id }}"
                {{ request('course_id')==$course->id?'selected':'' }}>
                {{ $course->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-2">
    <label class="form-label fw-bold">Type</label>
    <select name="class_type_id" class="form-control select2">
        <option value="">All Types</option>
        @foreach($types as $type)
            <option value="{{ $type->id }}"
                {{ request('class_type_id')==$type->id?'selected':'' }}>
                {{ ucfirst($type->name) }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-2">
    <label class="form-label fw-bold">Status</label>
    <select name="status" class="form-control">
        <option value="">All Status</option>
        <option value="active"
            {{ request('status')=='active'?'selected':'' }}>
            Active
        </option>
        <option value="completed"
            {{ request('status')=='completed'?'selected':'' }}>
            Completed
        </option>
    </select>
</div>

<div class="col-md-3">
    <label class="form-label fw-bold">Class Room</label>
    <select name="class_room_id" class="form-control select2-class-ajax"
        data-ajax-url="{{ $classRoomSearchUrl }}"
        data-selected-id="{{ request('class_room_id') }}"
        data-selected-text="{{ $selectedClassName ?? '' }}">
        @if(request('class_room_id') && isset($selectedClassName))
            <option value="{{ request('class_room_id') }}" selected>{{ $selectedClassName }}
            </option>
        @endif
    </select>
</div>

<div class="col-md-3 d-flex align-items-end gap-2">
    <button class="btn btn-primary px-3">
        Filter
    </button>
    <a href="{{ $indexRoute }}" class="btn btn-light px-3">
        Reset
    </a>
</div>

</form>



{{-- ================= TABLE ================= --}}

<table class="table table-bordered align-middle">

<thead>

<tr>
<th>Course</th>
<th>Class</th>
<th>Type</th>
<th>Schedule</th>
<th>Fees</th>
<th>Status</th>
<th>Action</th>
</tr>

</thead>


<tbody>

@forelse($class_rooms as $class)

<tr>

<td>{{ $class->course->name ?? '-' }}</td>

<td>{{ $class->name }}</td>

<td>{{ ucfirst($class->classType->name ?? '-') }}</td>

<td>

@if($class->selected_days)



{{ implode(', ', $class->selected_days ?? []) }}
<small>
<br>

{{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}

</small>

@endif

</td>


<td>

{{ $class->classType->id == 1 ? 'First Month Fee' : 'Admission Fee' }}: ₹{{ number_format($class->admission_fee,2) }} <br>

Monthly: ₹{{ number_format($class->monthly_fee,2) }}

</td>


<td>

<span class="badge {{ $class->is_completed ? 'bg-secondary' : 'bg-success' }}">

{{ $class->is_completed ? 'Completed' : 'Active' }}

</span>

</td>


<td>

<div class="d-flex gap-2">

{{-- <a href="{{ route('staff.class_rooms.show', encrypt($class->id)) }}">
<i class="fas fa-eye"></i>
</a> --}}

<a href="{{ $showRoute(encrypt($class->id)) }}">
<i class="fas fa-eye"></i>
</a>

<a href="#" data-bs-toggle="modal" data-bs-target="#attendanceModal_{{ $class->id }}">
<i class="fas fa-clipboard-list text-info"></i>
</a>

<a href="{{ $editRoute(encrypt($class->id)) }}">
<i class="mdi mdi-pencil text-success"></i>
</a>

@php
    $canDelete = false;
    if (auth('admin')->check()) {  
        $canDelete = true;
    } elseif (auth('staff')->check()) {
        $staff = auth('staff')->user();
        if ($staff->hasRoleId(utility('id_operation_dept'))) {
            $canDelete = true;
        }
    }
@endphp

@if($canDelete)
<a href="#"
data-plugin="delete-data"
data-target-form="#delete_{{ $class->id }}">
<i class="mdi mdi-trash-can text-danger"></i>
</a>

<form id="delete_{{ $class->id }}"
method="POST"
action="{{ $deleteRoute(encrypt($class->id)) }}">

@csrf
@method('DELETE')

</form>
@endif

</div>

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal_{{ $class->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Attendance - {{ $class->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                @php
                    $completedClassHours = \App\Models\ClassHour::where('class_room_id', $class->id)->where('status', 'completed')->pluck('id');
                    $totalClasses = $completedClassHours->count() ?: 1;
                    
                    $attendanceStats = \Illuminate\Support\Facades\DB::table('student_attendance')
                            ->select('student_id', \Illuminate\Support\Facades\DB::raw('SUM(is_present) as present'))
                            ->whereIn('class_hour_id', $completedClassHours)
                            ->groupBy('student_id')
                            ->get()
                            ->keyBy('student_id');
                @endphp
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($class->students as $student)
                                @php
                                    $stat = $attendanceStats->get($student->id);
                                    $present = $stat ? $stat->present : 0;
                                    $percentage = round(($present / $totalClasses) * 100);
                                @endphp
                                <tr>
                                    <td>{{ $student->name }}</td>
                                    <td>
                                        <span class="badge {{ $percentage >= 75 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $present }}/{{ $completedClassHours->count() ?: 1 }} ({{ $percentage }}%)
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->

</td>

</tr>

@empty

<tr>
<td colspan="7" class="text-center">
No Classes Found
</td>
</tr>

@endforelse

</tbody>

</table>


{{ $class_rooms->links() }}

</div>

</div>
