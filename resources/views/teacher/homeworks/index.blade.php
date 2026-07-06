@extends('teacher.layouts.master-layouts-noleft')

@section('title', 'Homework Assignments')

@section('content')

    @include('components.alerts')

    <div class="portal-page-header">
        <h4 class="m-0 fw-bold text-dark">Homework Assignments</h4>
        <a href="{{ route('teacher.homeworks.create') }}" class="portal-btn portal-btn-primary">
            <i class="fas fa-plus me-1"></i> Assign Homework
        </a>
    </div>

    <div class="portal-card">
        <div class="portal-card-header">
            <h4>Assigned Homework</h4>
        </div>
        <div class="portal-card-body p-0 table-responsive">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Class Room</th>
                        <th>Attached Files</th>
                        <th>Submissions</th>
                        <th>Assigned Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homeworks as $hw)
                        <tr>
                            <td class="fw-bold text-dark">{{ $hw->title }}</td>
                            <td>{{ $hw->classRoom?->name ?? 'N/A' }}</td>
                            <td>
                                @if($hw->files->count() > 0)
                                    <span class="portal-badge portal-badge-info">
                                        <i class="fas fa-paperclip me-1"></i> {{ $hw->files->count() }} file(s)
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="portal-badge portal-badge-primary">
                                    {{ $hw->submissions->count() }} submission(s)
                                </span>
                            </td>
                            <td>
                                {{ $hw->created_at->format('d M Y') }}
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('teacher.homeworks.show', encrypt($hw->id)) }}" class="portal-btn portal-btn-primary">
                                        <i class="fas fa-eye"></i> View/Evaluate
                                    </a>
                                    <form action="{{ route('teacher.homeworks.destroy', encrypt($hw->id)) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this homework? All student submissions will also be deleted.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="portal-btn btn-danger text-white" style="background-color: #ef4444 !important;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                <div class="portal-empty-state">
                                    <i class="fas fa-file-signature portal-empty-state-icon"></i>
                                    <div class="portal-empty-state-title">No Homework Assigned</div>
                                    <p class="text-muted small m-0">You have not assigned any homework yet.</p>
                                </div>
                            </td>
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
