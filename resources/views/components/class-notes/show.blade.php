
@section('title', 'View Class Note')

@section('content')

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle" title="Go Back" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <h5 class="mb-0">{{ $note->title }}</h5>
                </div>
                <div>
                    @if($isTeacher=='true')
                    <form action="{{ route($routePrefix.'.notes.destroy', $note->id) }}"
                          method="POST"
                          style="display:inline;"
                          onsubmit="return confirm('Delete this note?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Class:</strong>
                        <p>{{ $note->classRoom?->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Teacher:</strong>
                        <p>{{ $note->teacher?->name ?? '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Created:</strong>
                        <p>{{ $note->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <strong>Description:</strong>
                    <div class="mt-2 p-3 bg-light rounded">
                        {{ $note->content ?? 'No description provided' }}
                    </div>
                </div>

                @if($note->files->count() > 0)
                    <hr>
                    <div class="mb-3">
                        <strong>Attachments:</strong>
                        <div class="mt-2">
                            <div class="list-group">
                                @foreach($note->files as $file)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-file"></i>
                                            <span class="ms-2">{{ $file->file_name }}</span>
                                            <span class="badge bg-secondary ms-2">{{ $file->file_type }}</span>
                                            @if($file->file_size)
                                                <small class="ms-2 text-muted">({{ number_format($file->file_size / 1024, 2) }} KB)</small>
                                            @endif
                                        </div>
                                        <a href="{{ asset('storage/'.$file->file_path) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
