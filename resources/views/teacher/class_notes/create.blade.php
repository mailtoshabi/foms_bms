@extends('teacher.layouts.master-layouts-noleft')

@section('title', 'Upload Class Notes')

@section('content')

    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle"
                title="Go Back"
                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">Upload Class Notes</h4>
        </div>
        <a href="{{ route('teacher.notes.index') }}" class="portal-btn"
            style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; border: none; font-weight: 600;">
            <i class="fas fa-file-signature me-1"></i> View Notes
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="portal-card">

                <div class="portal-card-header">
                    <h4>New Class Note</h4>
                </div>

                <div class="portal-card-body">

                    <form action="{{ route('teacher.notes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="portal-label">Note Title *</label>
                            <input type="text" name="title" class="portal-input @error('title') is-invalid @enderror"
                                value="{{ old('title') }}" placeholder="Enter note title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="portal-label">Class *</label>
                            <select name="class_room_id"
                                class="portal-select select2-class-ajax @error('class_room_id') is-invalid @enderror"
                                data-ajax-url="{{ route('teacher.notes.class_rooms.search') }}" required>
                                <option value="">Search class...</option>
                            </select>
                            @error('class_room_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="portal-label">Note Description</label>
                            <textarea name="note" class="portal-input @error('note') is-invalid @enderror" rows="4"
                                placeholder="Enter note details (optional)">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="portal-label">Attachments</label>
                            <div id="fileInputContainer">
                                <div class="file-input-group mb-2 d-flex gap-2">
                                    <input type="file" name="files[]"
                                        class="portal-input @error('files.*') is-invalid @enderror"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt">
                                    <button type="button" class="portal-btn removeFileInput"
                                        style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; display:none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="portal-btn mt-2"
                                style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; border: none; padding: 6px 14px;"
                                id="addFileInput">
                                <i class="fas fa-plus"></i> Add Another File
                            </button>
                            <small class="form-text text-muted d-block mt-2">
                                Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, TXT (Max 2MB each)
                            </small>
                            @error('files.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 d-flex gap-2">
                            <button type="submit" class="portal-btn portal-btn-primary"
                                onclick="this.disabled=true; this.innerText='Uploading...'; this.form.submit();">
                                <i class="fas fa-upload"></i> Upload Note
                            </button>
                            <a href="{{ route('teacher.notes.index') }}" class="portal-btn"
                                style="background: rgba(100, 116, 139, 0.1); color: #64748b;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.2.1/compressor.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileContainer = document.getElementById('fileInputContainer');
            const addFileBtn = document.getElementById('addFileInput');

            addFileBtn.addEventListener('click', function () {
                const fileGroup = document.createElement('div');
                fileGroup.className = 'file-input-group mb-2 d-flex gap-2';
                fileGroup.innerHTML = `
                                <input type="file"
                                       name="files[]"
                                       class="portal-input"
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt">
                                <button type="button" class="portal-btn removeFileInput" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;

                fileContainer.appendChild(fileGroup);
                updateRemoveButtons();
            });

            function updateRemoveButtons() {
                const groups = document.querySelectorAll('.file-input-group');
                groups.forEach((group, index) => {
                    const removeBtn = group.querySelector('.removeFileInput');
                    if (groups.length > 1) {
                        removeBtn.style.display = 'block';
                        // Clean up old click event listeners to avoid duplicates
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

            // File size validation and automatic image compression
            fileContainer.addEventListener('change', function (e) {
                if (e.target && e.target.type === 'file') {
                    const input = e.target;
                    const file = input.files[0];
                    if (!file) return;

                    const maxSize = 2 * 1024 * 1024; // 2MB

                    if (file.size > maxSize) {
                        const fileExt = file.name.split('.').pop().toLowerCase();
                        const imageExts = ['jpg', 'jpeg', 'png'];

                        if (imageExts.includes(fileExt)) {
                            if (confirm(`The image "${file.name}" is larger than 2MB (${(file.size / (1024 * 1024)).toFixed(2)}MB). Would you like to automatically compress and resize it to fit the 2MB limit?`)) {
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
                                            alert(`Even after compression, the image "${file.name}" is still larger than 2MB. Please choose a smaller image.`);
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
                            // Non-image files (PDF, DOCX, XLSX, etc.)
                            alert(`The file "${file.name}" exceeds the 2MB size limit (${(file.size / (1024 * 1024)).toFixed(2)}MB). Document formats (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT) cannot be compressed automatically in the browser. Please select a file smaller than 2MB.`);
                            input.value = '';
                        }
                    }
                }
            });

            // Initial update
            updateRemoveButtons();
        });
    </script>

@endsection