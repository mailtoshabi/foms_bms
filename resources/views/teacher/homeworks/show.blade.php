@extends('teacher.layouts.master-layouts-noleft')

@section('title', 'Homework Details')

@section('content')

    @include('components.alerts')

    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle"
                title="Go Back"
                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">Homework Details</h4>
        </div>
        <a href="{{ route('teacher.homeworks.index') }}" class="portal-btn"
            style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; border: none; font-weight: 600;">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row">
        {{-- Homework Info --}}
        <div class="col-lg-8">
            <div class="portal-card mb-4">
                <div class="portal-card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $homework->title }}</h4>
                    <span class="portal-badge portal-badge-primary">
                        {{ $homework->classRoom?->name ?? 'N/A' }}
                    </span>
                </div>
                <div class="portal-card-body">
                    
                    <div class="mb-4">
                        <label class="portal-label fw-bold">Instructions</label>
                        <div class="bg-light p-3 rounded text-dark" style="white-space: pre-wrap; font-size: 0.95rem;">{!! nl2br(e($homework->content ?? 'No instructions provided.')) !!}</div>
                    </div>

                    @if($homework->files->count() > 0)
                        <div class="border-top pt-3">
                            <label class="portal-label fw-bold">Attachments ({{ $homework->files->count() }})</label>
                            <div class="list-group mt-2">
                                @foreach($homework->files as $file)
                                    <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 border-bottom px-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-file-alt text-primary fa-2x me-3"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $file->file_name }}</h6>
                                                <small class="text-muted">Size: {{ $file->file_size_formatted }}</small>
                                            </div>
                                        </div>
                                        <a href="{{ route('teacher.homeworks.file.download', encrypt($file->id)) }}" target="_blank" class="portal-btn portal-btn-primary py-1 px-3">
                                            <i class="fas fa-download me-1"></i> View/Download
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Submissions Table --}}
            <div class="portal-card">
                <div class="portal-card-header">
                    <h4>Student Submissions</h4>
                </div>
                <div class="portal-card-body p-0 table-responsive">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Submitted At</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($homework->classRoom->students as $student)
                                @php
                                    $submission = $submissions->get($student->id);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $student->name }}</div>
                                        <small class="text-muted">ID: {{ $student->admission_no }}</small>
                                    </td>
                                    <td>
                                        {{ $submission ? $submission->created_at->format('d M Y, h:i A') : '-' }}
                                    </td>
                                    <td>
                                        @if(!$submission)
                                            <span class="portal-badge" style="background: rgba(148, 163, 184, 0.15); color: #64748b;">Not Submitted</span>
                                        @elseif(is_null($submission->graded_at))
                                            <span class="portal-badge portal-badge-warning">Pending Evaluation</span>
                                        @else
                                            <span class="portal-badge portal-badge-success">Graded</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($submission && !is_null($submission->mark_obtained))
                                            <strong>{{ $submission->mark_obtained }}</strong> <span class="text-muted">/ {{ $submission->total_mark }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($submission)
                                            <button class="portal-btn portal-btn-primary py-1 px-3 evaluateBtn"
                                                data-id="{{ encrypt($submission->id) }}"
                                                data-student="{{ $student->name }}"
                                                data-text="{{ $submission->submitted_text }}"
                                                data-total="{{ $submission->total_mark ?? '10' }}"
                                                data-obtained="{{ $submission->mark_obtained ?? '' }}"
                                                data-comments="{{ $submission->teacher_comments ?? '' }}"
                                                data-files="{{ json_encode($submission->files->map(fn($f) => ['name' => $f->file_name, 'url' => route('teacher.homeworks.submission-file.download', encrypt($f->id))])) }}">
                                                <i class="fas fa-edit me-1"></i> Evaluate
                                            </button>
                                        @else
                                            <button class="portal-btn btn-light py-1 px-3" disabled>
                                                No Submission
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar Info Card --}}
        <div class="col-lg-4">
            <div class="portal-card">
                <div class="portal-card-header">
                    <h4>Assignment Info</h4>
                </div>
                <div class="portal-card-body">
                    <p class="mb-2 text-dark"><strong>Class Room:</strong> {{ $homework->classRoom?->name ?? 'N/A' }}</p>
                    <p class="mb-2 text-dark"><strong>Assigned By:</strong> {{ $homework->teacher?->name ?? 'N/A' }}</p>
                    <p class="mb-2 text-dark"><strong>Created Date:</strong> {{ $homework->created_at->format('d M Y h:i A') }}</p>
                    <p class="mb-0 text-dark"><strong>Total Submissions:</strong> {{ $submissions->count() }} / {{ $homework->classRoom->students->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Evaluation Modal --}}
    <div class="modal fade" id="evaluateModal" tabindex="-1" aria-labelledby="evaluateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="evaluateModalLabel">Evaluate Homework Submission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="evaluationForm" method="POST" action="{{ route('teacher.homeworks.submissions.grade') }}">
                    @csrf
                    <input type="hidden" name="submission_id" id="modalSubmissionId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Student Name</label>
                            <input type="text" id="modalStudentName" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Submitted Text Response</label>
                            <div id="modalSubmittedText" class="p-3 bg-light rounded text-dark" style="white-space: pre-wrap; font-size: 0.95rem; max-height: 250px; overflow-y: auto;">-</div>
                        </div>

                        <div class="mb-3" id="modalFilesContainer" style="display:none;">
                            <label class="form-label fw-bold">Student Attachments</label>
                            <div class="list-group" id="modalFilesList"></div>
                        </div>

                        <hr>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total Marks Possible *</label>
                                <input type="number" name="total_mark" id="modalTotalMark" step="0.01" min="0" class="form-control" required placeholder="e.g. 10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marks Obtained *</label>
                                <input type="number" name="mark_obtained" id="modalMarkObtained" step="0.01" min="0" class="form-control" required placeholder="e.g. 8.5">
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label fw-bold">Feedback / Comments</label>
                            <textarea name="teacher_comments" id="modalComments" class="form-control" rows="3" placeholder="Enter comments or feedback for the student..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveGradeBtn">Save Evaluation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.evaluateBtn').on('click', function() {
                var btn = $(this);
                var id = btn.data('id');
                var student = btn.data('student');
                var text = btn.data('text') || 'No text response provided.';
                var total = btn.data('total');
                var obtained = btn.data('obtained');
                var comments = btn.data('comments');
                var files = btn.data('files');

                // Populate modal
                $('#modalStudentName').val(student);
                $('#modalSubmittedText').text(text);
                $('#modalTotalMark').val(total);
                $('#modalMarkObtained').val(obtained);
                $('#modalComments').val(comments);

                // Set Submission ID
                $('#modalSubmissionId').val(id);

                // Populate attachments
                var filesList = $('#modalFilesList');
                filesList.empty();
                if (files && files.length > 0) {
                    $('#modalFilesContainer').show();
                    files.forEach(function(file) {
                        filesList.append(
                            '<div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 border-bottom px-0 py-2">' +
                                '<div><i class="far fa-file-alt text-primary me-2"></i><strong>' + file.name + '</strong></div>' +
                                '<a href="' + file.url + '" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="fas fa-download"></i> View</a>' +
                            '</div>'
                        );
                    });
                } else {
                    $('#modalFilesContainer').hide();
                }

                // Show modal
                $('#evaluateModal').modal('show');
            });

            // Enforce obtained marks <= total marks in client side
            $('#evaluationForm').on('submit', function(e) {
                var total = parseFloat($('#modalTotalMark').val());
                var obtained = parseFloat($('#modalMarkObtained').val());
                if (obtained > total) {
                    e.preventDefault();
                    alert('Marks obtained (' + obtained + ') cannot exceed the total marks possible (' + total + ').');
                    $('#saveGradeBtn').prop('disabled', false).text('Save Evaluation');
                }
            });
        });
    </script>
@endsection
