@extends('teacher.layouts.master')

@section('title','Teacher Dashboard')

@section('content')

<div class="row">

{{-- SUMMARY CARDS --}}

<div class="col-md-4">
<div class="card text-center">
<div class="card-body">
<h5>Assigned Classes</h5>
<h2>{{ $classes->count() }}</h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card text-center">
<div class="card-body">
<h5>Attendance</h5>
<h2>{{ $attendancePercent }}%</h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card text-center">
<div class="card-body">
<h5>Latest Salary</h5>
<h2>
@if($salaries->first())
₹ {{ number_format($salaries->first()->amount,2) }}
@else
-
@endif
</h2>
</div>
</div>
</div>


{{-- ASSIGNED CLASSES --}}

<div class="col-md-12">
<div class="card">

<div class="card-header">
<h5>Assigned Classes</h5>
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

@foreach($classes as $class)

<tr>

<td>{{ $class->course->name ?? '-' }}</td>
<td>{{ $class->name }}</td>
<td>{{ $class->classType->name ?? '-' }}</td>
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


{{-- LATEST CLASS NOTES --}}

<div class="col-md-6">

<div class="card">

<div class="card-header">
<h5>Latest Class Notes</h5>
</div>

<div class="card-body">

@forelse($notes as $note)

<div class="border p-3 mb-2">

<strong>{{ $note->title }}</strong>

<p class="text-muted small">
{{ $note->created_at->format('d M Y') }}
</p>

<p>{{ Str::limit($note->content,100) }}</p>

</div>

@empty

<p class="text-muted">No notes available</p>

@endforelse

</div>
</div>

</div>


{{-- SALARY HISTORY --}}

<div class="col-md-6">

<div class="card">

<div class="card-header">
<h5>Salary History</h5>
</div>

<div class="card-body">

<table class="table table-bordered">

<thead>
<tr>
<th>Month</th>
<th>Amount</th>
<th>Paid Date</th>
</tr>
</thead>

<tbody>

@foreach($salaries as $salary)

<tr>

<td>{{ $salary->month }} {{ $salary->year }}</td>

<td>₹ {{ number_format($salary->amount,2) }}</td>

<td>{{ $salary->paid_date }}</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

</div>


</div>

@endsection
