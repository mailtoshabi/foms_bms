@extends('teacher.layouts.master-layouts-noleft')

@section('title', 'Assigned Classes')

@section('content')

    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle"
                title="Go Back"
                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">Assigned Classes</h4>
        </div>
    </div>

    <div class="portal-card">

        <div class="portal-card-header">
            <h4>Classes Overview</h4>
            <span class="portal-badge portal-badge-primary">Total: {{ count($classes) }}</span>
        </div>

        <div class="portal-card-body table-responsive p-0">

            <table class="portal-table">

                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Class</th>
                        <th>Type</th>
                        <th>Schedule</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($classes as $class)

                        <tr>

                            <td class="fw-bold text-dark">{{ $class->course->name ?? '-' }}</td>

                            <td>
                                <span class="fw-semibold">{{ $class->name }}</span>
                                @if($class->is_completed)
                                    <span class="portal-badge portal-badge-success ms-2">Completed</span>
                                @endif
                            </td>

                            <td>
                                <span class="portal-badge portal-badge-info">
                                    {{ ucfirst($class->classType->name ?? '-') }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-muted">{{ implode(', ', $class->selected_days ?? []) }}</span>
                                    <small class="text-primary fw-bold mt-1">
                                        <i class="far fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}
                                    </small>
                                </div>
                            </td>

                            <td>
                                <a href="{{ route('teacher.classes.show', encrypt($class->id)) }}"
                                    class="portal-btn portal-btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Enter
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="p-0">
                                <div class="portal-empty-state">
                                    <i class="fas fa-folder-open portal-empty-state-icon"></i>
                                    <div class="portal-empty-state-title">No Assigned Classes Found</div>
                                    <p class="text-muted small m-0">You don't have any classes assigned to you currently.</p>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-3">
        {{ $classes->links() }}
    </div>

@endsection