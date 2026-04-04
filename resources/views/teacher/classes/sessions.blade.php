@extends('teacher.layouts.master')
@section('title', 'My Sessions')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">My Sessions</h4>
    </div>

    <div class="card-body">

        {{-- ── Filters ── --}}
        <form method="GET" class="row g-2 mb-4">

            <div class="col-md-3">
                <select name="filter" class="form-control">
                    <option value="">All Statuses</option>
                    <optgroup label="Session Status">
                        <option value="status_pending"   {{ request('filter') == 'status_pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="status_completed" {{ request('filter') == 'status_completed' ? 'selected' : '' }}>Completed</option>
                    </optgroup>
                    <optgroup label="Salary">
                        <option value="salary_0" {{ request('filter') == 'salary_0' ? 'selected' : '' }}>Salary Pending</option>
                        <option value="salary_1" {{ request('filter') == 'salary_1' ? 'selected' : '' }}>Salary Paid</option>
                    </optgroup>
                </select>
            </div>

            <div class="col-md-3">
                <input type="date"
                       name="date_from"
                       class="form-control"
                       value="{{ request('date_from') }}"
                       placeholder="From Date">
            </div>

            <div class="col-md-3">
                <input type="date"
                       name="date_to"
                       class="form-control"
                       value="{{ request('date_to') }}"
                       placeholder="To Date">
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('teacher.sessions.index') }}" class="btn btn-light">Reset</a>
            </div>

        </form>

        {{-- ── Table ── --}}
        <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Class</th>
                    <th>Duration</th>
                    <th>Hourly Wage</th>
                    <th>Status</th>
                    <th>Session Link</th>
                </tr>
            </thead>
            <tbody>

            @forelse($sessions as $session)
                <tr>
                    <td>{{ $sessions->firstItem() + $loop->index }}</td>

                    <td>
                        {{ $session->class_started_at
                            ? $session->class_started_at->format('d M Y')
                            : '-' }}
                    </td>

                    <td>{{ $session->classRoom->course->name ?? '-' }}: {{ $session->classRoom->name ?? '-' }}</td>

                    {{-- <td>{{ $session->classRoom->course->name ?? '-' }}</td> --}}



                    <td>
                        @if($session->duration)
                            {{ floor($session->duration / 60) }}h
                            {{ $session->duration % 60 > 0 ? ($session->duration % 60).'m' : '' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $session->hourly_wage ?? '-' }}</td>

                    <td>
                        @if($session->status == 'completed')
                            <span class="badge bg-success">Completed</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>

                    <td>
                        @if($session->google_meet_link && $session->status == 'pending')
                            <a href="{{ $session->google_meet_link }}"
                               target="_blank"
                               rel="noopener"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-video"></i> Join
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No sessions found</td>
                </tr>
            @endforelse

            </tbody>
        </table>
        </div>

        {{ $sessions->links() }}

    </div>
</div>

@endsection
