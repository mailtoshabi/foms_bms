@extends('teacher.layouts.master')

@section('title','Teacher Dashboard')

@section('content')

<div class="row">

{{-- SUMMARY CARDS --}}

<div class="col-md-4">
<div class="card text-center">
<div class="card-body">
<h5>Active Class Rooms</h5>
<h2>{{ $classes->count() }}</h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card text-center">
<div class="card-body">
<h5>Completed Sessions</h5>
<h2>{{ $completedClasses }}</h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card text-center">
<div class="card-body">
<h5>Latest Salary</h5>
<h2>
@if($salaries->first())
₹ {{ number_format($salaries->first()->total_amount,2) }}
@else
-
@endif
</h2>
</div>
</div>
</div>

{{-- Total Hours --}}
<div class="col-md-3">
<div class="card text-center">
<div class="card-body">
<h6>Total Hours</h6>
<h3 class="text-primary">
{{ $totalHours }} hrs
</h3>
</div>
</div>
</div>

{{-- This Month Classes --}}
<div class="col-md-3">
<div class="card text-center">
<div class="card-body">
<h6>This Month Classes</h6>
<h3 class="text-info">
{{ $thisMonthClasses }}
</h3>
</div>
</div>
</div>

{{-- Earnings This Month --}}
<div class="col-md-3">
<div class="card text-center">
<div class="card-body">
<h6>Earnings This Month</h6>
<h3 class="text-success">
₹ {{ number_format($earningsThisMonth,2) }}
</h3>
</div>
</div>
</div>

<div class="col-md-3">
    <div class="card text-center">
    <div class="card-body">
    <h6>Pending Salary</h6>
    <h3 class="text-danger">
    ₹ {{ number_format($pendingSalary,2) }}
    </h3>
    </div>
    </div>
</div>

<canvas id="teacherChart"></canvas>

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
<th>Hourly Wage</th>
</tr>
</thead>

<tbody>

@foreach($classes as $class)

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
{{ $class->pivot->hourly_wage }}
</td>

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

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Title</th>
                <th>Class</th>
                <th>Files</th>
                <th>Created</th>
                <th width="100">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notes as $note)
                <tr>
                    <td>
                        <strong>{{ $note->title }}</strong>
                    </td>
                    <td>
                        {{ $note->classRoom?->name ?? '-' }}
                    </td>
                    <td>
                        @if($note->files->count() > 0)
                            <span class="badge bg-primary">{{ $note->files->count() }} file(s)</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        {{ $note->created_at->format('d M Y') }}
                    </td>
                    <td>
                        <a href="{{ route('teacher.notes.show', encrypt($note->id)) }}"
                            class="btn btn-sm btn-info"
                            title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form action="{{ route('teacher.notes.destroy', encrypt($note->id)) }}"
                                method="POST"
                                style="display:inline;"
                                onsubmit="return confirm('Delete this note?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        No notes uploaded yet
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

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
<th>Cycle</th>
<th>Total Hours</th>
<th>Total Amount</th>
<th>Status</th>
</tr>
</thead>

<tbody>

@foreach($salaries as $salary)

<tr>

<td>
{{ \Carbon\Carbon::parse($salary->cycle_start)->format('d M Y') }}
-
{{ \Carbon\Carbon::parse($salary->cycle_end)->format('d M Y') }}
</td>

<td>
<span class="badge bg-info">
{{ number_format($salary->total_hours,2) }} hrs
</span>
</td>

<td>
<strong class="text-success">
₹ {{ number_format($salary->total_amount,2) }}
</strong>
@if($salary->status == 'paid')
<br><small class="text-muted">
Paid on {{ optional($salary->payment_date)->format('d M Y') }}
</small>
@endif
</td>

<td>
<span class="badge
    {{ $salary->status == 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
    {{ ucfirst($salary->status) }}
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

@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    const ctx = document.getElementById('teacherChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'Classes',
                    data: @json($classCounts)
                },
                {
                    label: 'Earnings',
                    data: @json($earnings)
                }
            ]
        }
    });
    </script>
@endsection
