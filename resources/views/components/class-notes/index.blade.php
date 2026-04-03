

@section('title', 'Class Notes')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Class Notes</h5>
                @if($isTeacher=='true')
                <a href="{{ route('teacher.notes.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Upload Notes
                </a>
                @endif
            </div>

            <div class="card-body">

                @if($notes->count() > 0)

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
                                            <a href="{{ route($routePrefix.'.notes.show', encrypt($note->id)) }}"
                                               class="btn btn-sm btn-info"
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($isTeacher=='true')
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
                                            @endif
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

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $notes->links() }}
                    </div>

                @else
                    @if($isTeacher=='true')
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No notes uploaded yet. <a href="{{ route('teacher.notes.create') }}">Upload your first note</a>
                    </div>
                    @endif

                @endif

            </div>

        </div>
    </div>
</div>

@endsection
