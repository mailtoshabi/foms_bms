@section('title', 'Class Notes')

@section('content')

    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle" title="Go Back" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">Class Notes</h4>
        </div>
        @if($isTeacher == 'true' || $isTeacher === true)
            <a href="{{ route('teacher.notes.create') }}" class="portal-btn portal-btn-primary">
                <i class="fas fa-plus"></i> Upload Notes
            </a>
        @endif
    </div>

    <div class="portal-card">

        <div class="portal-card-header">
            <h4>My Class Notes</h4>
            <span class="portal-badge portal-badge-primary">Total: {{ $notes->total() }}</span>
        </div>

        <div class="portal-card-body table-responsive p-0">

            <table class="portal-table">

                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Files</th>
                        <th>Created</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($notes as $note)

                        <tr>

                            <td class="fw-bold text-dark">{{ $note->title }}</td>

                            <td>
                                <span class="fw-semibold">{{ $note->classRoom?->name ?? '-' }}</span>
                            </td>

                            <td>
                                @if($note->files->count() > 0)
                                    <span class="portal-badge portal-badge-info">
                                        <i class="fas fa-paperclip me-1"></i>
                                        {{ $note->files->count() }} file(s)
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-muted">{{ $note->created_at->format('d M Y') }}</span>
                                    <small class="text-primary fw-bold mt-1">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $note->created_at->format('h:i A') }}
                                    </small>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route($routePrefix.'.notes.show', encrypt($note->id)) }}"
                                       class="portal-btn"
                                       style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 6px 12px;"
                                       title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($isTeacher == 'true' || $isTeacher === true)
                                        <form action="{{ route('teacher.notes.destroy', encrypt($note->id)) }}"
                                              method="POST"
                                              style="display:inline-block; margin: 0;"
                                              onsubmit="return confirm('Delete this note?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="portal-btn" 
                                                    style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 6px 12px; border: none;"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="p-0">
                                <div class="portal-empty-state">
                                    <i class="fas fa-file-alt portal-empty-state-icon"></i>
                                    <div class="portal-empty-state-title">No Class Notes Found</div>
                                    <p class="text-muted small mb-3">There are no class notes available at the moment.</p>
                                    @if($isTeacher == 'true' || $isTeacher === true)
                                        <a href="{{ route('teacher.notes.create') }}" class="portal-btn portal-btn-primary">
                                            <i class="fas fa-plus"></i> Upload your first note
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-3">
        {{ $notes->links() }}
    </div>

@endsection
