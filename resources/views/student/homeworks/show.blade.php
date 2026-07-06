@extends('student.layouts.master-layouts-noleft')

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
        <a href="{{ route('student.homeworks.index') }}" class="btn btn-light" style="font-weight: 600;">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Homework Instructions Card --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">{{ $homework->title }}</h5>
                    <span class="badge bg-soft-primary text-primary fs-7">
                        {{ $homework->classRoom?->name ?? 'N/A' }}
                    </span>
                </div>
                <div class="card-body">
                    
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-2">Instructions</h6>
                        <div class="bg-light p-3 rounded text-dark" style="white-space: pre-wrap; font-size: 0.95rem;">{!! nl2br(e($homework->content ?? 'No instructions provided.')) !!}</div>
                    </div>

                    @if($homework->files->count() > 0)
                        <div class="border-top pt-3">
                            <h6 class="text-muted fw-bold mb-2">Attached Assignment Files</h6>
                            <div class="list-group">
                                @foreach($homework->files as $file)
                                    <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 border-bottom px-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-file-alt text-primary fa-2x me-3"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $file->file_name }}</h6>
                                                <small class="text-muted">Size: {{ $file->file_size_formatted }}</small>
                                            </div>
                                        </div>
                                        <a href="{{ route('student.homeworks.file.download', encrypt($file->id)) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download me-1"></i> View/Download
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Submission Status Card --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 text-dark fw-bold">My Submission</h5>
                </div>
                <div class="card-body">
                    @if(!$submission)
                        {{-- Submission Form --}}
                        <form action="{{ route('student.homeworks.submit', encrypt($homework->id)) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-bold">Written Response / Answers</label>
                                <textarea name="submitted_text" class="form-control @error('submitted_text') is-invalid @enderror" rows="5" placeholder="Type your answers here (optional if attaching files)...">{{ old('submitted_text') }}</textarea>
                                @error('submitted_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Attach Work Files</label>
                                <div id="fileInputContainer">
                                    <div class="file-input-group mb-2 d-flex gap-2">
                                        <input type="file" name="files[]" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt">
                                        <button type="button" class="btn btn-danger removeFileInput" style="display:none;"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addFileInput">
                                    <i class="fas fa-plus me-1"></i> Add Another File
                                </button>
                                <small class="form-text text-muted d-block mt-2">
                                    Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, TXT (Max 5MB each)
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                                <i class="fas fa-paper-plane me-1"></i> Submit Homework
                            </button>
                        </form>
                    @else
                        {{-- Display Submission Details --}}
                        <div class="mb-4">
                            <h6 class="text-muted fw-bold mb-2">My Text Response</h6>
                            <div class="p-3 bg-light rounded text-dark" style="white-space: pre-wrap; font-size: 0.95rem;">{{ $submission->submitted_text ?? 'No written text response provided.' }}</div>
                        </div>

                        @if($submission->files->count() > 0)
                            <div class="mb-4">
                                <h6 class="text-muted fw-bold mb-2">My Attached Files</h6>
                                <div class="list-group">
                                    @foreach($submission->files as $file)
                                        <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 border-bottom px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="far fa-file-alt text-primary fa-lg me-2"></i>
                                                <span class="text-dark">{{ $file->file_name }}</span>
                                                <small class="text-muted ms-2">({{ $file->file_size_formatted }})</small>
                                            </div>
                                            <a href="{{ route('student.homeworks.submission-file.download', encrypt($file->id)) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <hr>

                        {{-- Grade & Evaluation --}}
                        @if(is_null($submission->graded_at))
                            <div class="alert alert-warning d-flex justify-content-between align-items-center mb-0">
                                <div>
                                    <i class="fas fa-clock me-2"></i> <strong>Pending Evaluation:</strong> Your homework was submitted on {{ $submission->created_at->format('d M Y \a\t h:i A') }} and is waiting for review.
                                </div>
                                <form action="{{ route('student.homeworks.submission.destroy', encrypt($submission->id)) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this submission and resubmit?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash me-1"></i> Delete & Resubmit
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-success mb-3">
                                <h5 class="alert-heading fw-bold mb-2"><i class="fas fa-check-circle me-2"></i>Graded</h5>
                                <p class="mb-1"><strong>Score:</strong> <span class="fs-5 fw-bold">{{ $submission->mark_obtained }} / {{ $submission->total_mark }} Marks</span></p>
                                <p class="mb-1"><strong>Evaluated By:</strong> {{ $submission->grader?->name ?? 'Teacher' }} on {{ $submission->graded_at->format('d M Y') }}</p>
                                @if($submission->teacher_comments)
                                    <hr>
                                    <p class="mb-0"><strong>Teacher Comments:</strong> {{ $submission->teacher_comments }}</p>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-2"><i class="fas fa-lock me-1"></i> This submission is graded and locked from edits.</small>
                        @endif

                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar Info Card --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Assignment Info</h5>
                </div>
                <div class="card-body text-dark">
                    <p class="mb-2"><strong>Class Room:</strong> {{ $homework->classRoom?->name ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Assigned By:</strong> {{ $homework->teacher?->name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Created Date:</strong> {{ $homework->created_at->format('d M Y h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.2.1/compressor.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileContainer = document.getElementById('fileInputContainer');
            const addFileBtn = document.getElementById('addFileInput');

            if (addFileBtn) {
                addFileBtn.addEventListener('click', function () {
                    const fileGroup = document.createElement('div');
                    fileGroup.className = 'file-input-group mb-2 d-flex gap-2';
                    fileGroup.innerHTML = `
                        <input type="file" name="files[]" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt">
                        <button type="button" class="btn btn-danger removeFileInput"><i class="fas fa-times"></i></button>
                    `;

                    fileContainer.appendChild(fileGroup);
                    updateRemoveButtons();
                });
            }

            function updateRemoveButtons() {
                const groups = document.querySelectorAll('.file-input-group');
                groups.forEach((group, index) => {
                    const removeBtn = group.querySelector('.removeFileInput');
                    if (groups.length > 1) {
                        removeBtn.style.display = 'block';
                        const newRemoveBtn = removeBtn.cloneNode(true);
                        removeBtn.parentNode.replaceChild(newRemoveBtn, removeBtn);
                        newRemoveBtn.addEventListener('click', function () {
                            group.remove();
                            updateRemoveButtons();
                        });
                    } else {
                        removeBtn.style.display = 'none';
                    }
                });
            }

            if (fileContainer) {
                // File size validation and automatic image compression
                fileContainer.addEventListener('change', function (e) {
                    if (e.target && e.target.type === 'file') {
                        const input = e.target;
                        const file = input.files[0];
                        if (!file) return;

                        const maxSize = 5 * 1024 * 1024; // 5MB

                        if (file.size > maxSize) {
                            const fileExt = file.name.split('.').pop().toLowerCase();
                            const imageExts = ['jpg', 'jpeg', 'png'];

                            if (imageExts.includes(fileExt)) {
                                if (confirm(`The image "${file.name}" is larger than 5MB (${(file.size / (1024 * 1024)).toFixed(2)}MB). Would you like to automatically compress and resize it to fit the 5MB limit?`)) {
                                    const form = input.closest('form');
                                    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
                                    if (submitBtn) {
                                        submitBtn.disabled = true;
                                        submitBtn.dataset.originalText = submitBtn.innerHTML;
                                        submitBtn.innerText = 'Compressing Image...';
                                    }

                                    new Compressor(file, {
                                        quality: 0.6,
                                        maxWidth: 1600,
                                        maxHeight: 1600,
                                        success(result) {
                                            const compressedFile = new File([result], file.name, {
                                                type: result.type,
                                                lastModified: Date.now(),
                                            });

                                            const dataTransfer = new DataTransfer();
                                            dataTransfer.items.add(compressedFile);
                                            input.files = dataTransfer.files;

                                            if (compressedFile.size > maxSize) {
                                                alert(`Even after compression, the image "${file.name}" is still larger than 5MB. Please choose a smaller image.`);
                                                input.value = '';
                                            } else {
                                                alert(`Image successfully compressed from ${(file.size / (1024 * 1024)).toFixed(2)}MB to ${(compressedFile.size / (1024 * 1024)).toFixed(2)}MB!`);
                                            }

                                            if (submitBtn) {
                                                submitBtn.disabled = false;
                                                submitBtn.innerHTML = submitBtn.dataset.originalText;
                                            }
                                        },
                                        error(err) {
                                            console.error(err.message);
                                            alert("Error compressing image. Please choose a smaller file.");
                                            input.value = '';
                                            if (submitBtn) {
                                                submitBtn.disabled = false;
                                                submitBtn.innerHTML = submitBtn.dataset.originalText;
                                            }
                                        },
                                    });
                                } else {
                                    input.value = '';
                                }
                            } else {
                                alert(`The file "${file.name}" exceeds the 5MB size limit (${(file.size / (1024 * 1024)).toFixed(2)}MB). Documents cannot be compressed automatically. Please select a file smaller than 5MB.`);
                                input.value = '';
                            }
                        }
                    }
                });

                // Initial update
                updateRemoveButtons();
            }
        });
    </script>
@endsection
