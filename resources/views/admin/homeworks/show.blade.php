@extends('admin.layouts.master')

@section('title', 'Homework Details')

@section('content')

    @component('admin.breadcrumbs.breadcrumb')
        @slot('li_1') Academics @endslot
        @slot('li_2') <a href="{{ route('admin.homeworks.index') }}">Homework</a> @endslot
        @slot('title') Homework Details @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $homework->title }}</h5>
                    <span class="badge bg-soft-primary text-primary fs-7">
                        {{ $homework->classRoom?->name ?? 'N/A' }}
                    </span>
                </div>
                <div class="card-body">
                    
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-2">Instructions / Content</h6>
                        <div class="bg-light p-3 rounded" style="white-space: pre-wrap; font-size: 0.95rem;">{!! nl2br(e($homework->content ?? 'No text content provided.')) !!}</div>
                    </div>

                    @if($homework->files->count() > 0)
                        <div class="mb-4">
                            <h6 class="text-muted fw-bold mb-2">Attached Files</h6>
                            <div class="list-group">
                                @foreach($homework->files as $file)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-file-alt text-primary fa-2x me-3"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $file->file_name }}</h6>
                                                <small class="text-muted">Size: {{ round($file->file_size / 1024, 2) }} KB</small>
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.homeworks.file.download', encrypt($file->id)) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download me-1"></i> Download/View
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Submissions List --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Student Submissions ({{ $homework->submissions->count() }})</h5>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Submitted At</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Grader</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($homework->submissions as $submission)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $submission->student->name }}</td>
                                    <td>{{ $submission->created_at->format('d M Y h:i A') }}</td>
                                    <td>
                                        @if(is_null($submission->graded_at))
                                            <span class="badge bg-soft-warning text-warning">Pending Evaluation</span>
                                        @else
                                            <span class="badge bg-soft-success text-success">Graded</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!is_null($submission->mark_obtained))
                                            {{ $submission->mark_obtained }} / {{ $submission->total_mark }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $submission->grader?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No submissions yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Metadata</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Teacher:</strong> {{ $homework->teacher?->name ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Class Room:</strong> {{ $homework->classRoom?->name ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Created At:</strong> {{ $homework->created_at->format('d M Y h:i A') }}</p>
                    
                    <hr>
                    
                    <form action="{{ route('admin.homeworks.destroy', encrypt($homework->id)) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this homework and all student submissions?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-1"></i> Delete Homework
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
