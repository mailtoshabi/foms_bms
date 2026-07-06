@extends('student.layouts.master-layouts-noleft')

@section('title', 'Homework Assignments')

@section('content')

    @include('components.alerts')

    <div class="portal-page-header">
        <h4 class="m-0 fw-bold text-dark">Homework Assignments</h4>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">My Homework</h5>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Class Room</th>
                        <th>Teacher</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Assigned Date</th>
                        <th width="120" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homeworks as $hw)
                        @php
                            $submission = $submissions->get($hw->id);
                        @endphp
                        <tr>
                            <td class="fw-bold text-dark">{{ $hw->title }}</td>
                            <td>{{ $hw->classRoom?->name ?? 'N/A' }}</td>
                            <td>{{ $hw->teacher?->name ?? 'N/A' }}</td>
                            <td>
                                @if(!$submission)
                                    <span class="badge bg-soft-danger text-danger">Not Submitted</span>
                                @elseif(is_null($submission->graded_at))
                                    <span class="badge bg-soft-warning text-warning">Pending Evaluation</span>
                                @else
                                    <span class="badge bg-soft-success text-success">Graded</span>
                                @endif
                            </td>
                            <td>
                                @if($submission && !is_null($submission->mark_obtained))
                                    {{ $submission->mark_obtained }} / {{ $submission->total_mark }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                {{ $hw->created_at->format('d M Y') }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('student.homeworks.show', encrypt($hw->id)) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No homework assignments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $homeworks->links() }}
    </div>

@endsection
