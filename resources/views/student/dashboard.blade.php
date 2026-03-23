@extends('student.layouts.master')

@section('title','Student Dashboard')

@section('content')

<div class="row">

{{-- STUDENT PROFILE --}}
<div class="col-md-3">
<div class="card text-center">

<div class="card-body">

@if($student->photo)
<img src="{{ asset('storage/'.$student->photo) }}"
class="rounded-circle mb-2"
width="80">
@endif

<h5>{{ $student->name }}</h5>

<p class="text-muted">{{ $student->contact_number }}</p>

<span class="badge bg-success">
{{ ucfirst($student->status) }}
</span>

</div>
</div>
</div>



{{-- CURRENT CLASS --}}
<div class="col-md-3">
<div class="card">

<div class="card-body">

<h6 class="text-muted">Current Class</h6>

@if($currentClass)

<strong>{{ $currentClass->name }}</strong> |

<span class="mb-0">
{{ $currentClass->course->name }}
</span>
    @if($currentClass->selected_days)

    <p class="text-muted">
    {{ implode(', ', $currentClass->selected_days ?? []) }} |
    {{ \Carbon\Carbon::createFromFormat('H:i', $currentClass->time_slot)->format('h:i A') ?? '' }}
    </p>

    @endif
@else

<span class="text-muted">Not Assigned</span>

@endif

</div>

</div>
</div>



{{-- TEACHER --}}
<div class="col-md-3">
<div class="card">

<div class="card-body">

<h6 class="text-muted">Teacher</h6>

@if($teacher)

<strong>{{ $teacher->name }}</strong>

<p class="text-muted">
{{ $teacher->phone }}
</p>

@else

<span class="text-muted">Not Assigned</span>

@endif

</div>

</div>
</div>



{{-- ATTENDANCE --}}
<div class="col-md-3">
<div class="card">

<div class="card-body">

<h6 class="text-muted">Attendance</h6>

<h3 class="text-success">
{{ $attendancePercent }}%
</h3>

<div class="progress">
<div class="progress-bar bg-success"
style="width: {{ $attendancePercent }}%">
</div>
</div>

</div>

</div>
</div>



{{-- FEE DUE --}}
<div class="col-md-4 mt-4">
<div class="card">

<div class="card-body">

<h6 class="text-muted">Fee Due</h6>

<h4 class="{{ $feeDue > 0 ? 'text-danger' : 'text-success' }}">

₹ {{ number_format($feeDue,2) }}

</h4>

@if($feeDue > 0)

<span class="badge bg-danger">
Pending
</span>

@else

<span class="badge bg-success">
Paid
</span>

@endif

</div>

</div>
</div>



{{-- CLASS NOTES --}}
<div class="col-md-8 mt-4">
<div class="card">

<div class="card-header">
<h5>Latest Class Notes</h5>
</div>

<div class="card-body">

@forelse($latestNotes as $note)

<div class="border rounded p-3 mb-3">

<strong>{{ $note->title }}</strong>

<p class="text-muted small">
{{ $note->created_at->format('d M Y') }}
</p>

<p>{{ Str::limit($note->note,120) }}</p>

@if($note->attachment)

<a href="{{ asset('storage/'.$note->attachment) }}"
target="_blank"
class="btn btn-sm btn-primary">

Download Attachment

</a>

@endif

</div>

@empty

<p class="text-muted">
No notes available
</p>

@endforelse

</div>

</div>
</div>


</div>

@endsection
