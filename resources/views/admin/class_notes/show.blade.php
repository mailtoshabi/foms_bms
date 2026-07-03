@extends('admin.layouts.master')

@section('title', 'Class Note Details')

@section('content')

    @component('admin.breadcrumbs.breadcrumb')
        @slot('li_1') Academics @endslot
        @slot('li_2') <a href="{{ route('admin.class-notes.index') }}">Class Notes</a> @endslot
        @slot('title') Class Note Details @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $note->title }}</h5>
                    <span class="badge bg-soft-primary text-primary fs-7">
                        {{ $note->classRoom?->name ?? 'N/A' }}
                    </span>
                </div>
                <div class="card-body">
                    
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-2">Note Content</h6>
                        <div class="bg-light p-3 rounded" style="white-space: pre-wrap; font-size: 0.95rem;">{!! nl2br(e($note->content ?? 'No text content provided.')) !!}</div>
                    </div>

                    @if($note->files->count() > 0)
                        <div>
                            <h6 class="text-muted fw-bold mb-2">Attached Files</h6>
                            <div class="list-group">
                                @foreach($note->files as $file)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-file-alt text-primary fa-2x me-3"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $file->file_name }}</h6>
                                                <small class="text-muted">Size: {{ round($file->file_size / 1024, 2) }} KB</small>
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.class-notes.file.download', encrypt($file->id)) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download me-1"></i> Download/View
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Metadata</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Teacher:</strong> {{ $note->teacher?->name ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Class Room:</strong> {{ $note->classRoom?->name ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Created At:</strong> {{ $note->created_at->format('d M Y h:i A') }}</p>
                    <p class="mb-2"><strong>Visibility:</strong> {{ ucfirst($note->visibility) }}</p>
                    
                    <hr>
                    
                    <form action="{{ route('admin.class-notes.destroy', encrypt($note->id)) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this class note?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-1"></i> Delete Note
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
