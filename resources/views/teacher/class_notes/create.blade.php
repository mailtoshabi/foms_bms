@extends('teacher.layouts.master')

@section('title', 'Upload Class Notes')

@section('content')

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">

            <div class="card-header">
                <h5 class="mb-0">Upload Class Notes</h5>
            </div>

            <div class="card-body">

                <form action="{{ route('teacher.notes.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Note Title *</label>
                        <input type="text"
                               name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}"
                               placeholder="Enter note title"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Class *</label>
                        <select name="class_room_id"
                                class="form-control select2-class-ajax @error('class_room_id') is-invalid @enderror"
                                data-ajax-url="{{ route('teacher.notes.class_rooms.search') }}"
                                required>
                            <option value="">Search class...</option>
                        </select>
                        @error('class_room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Note Description</label>
                        <textarea name="note"
                                  class="form-control @error('note') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Enter note details (optional)">{{ old('note') }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Attachments</label>
                        <div id="fileInputContainer">
                            <div class="file-input-group mb-2 d-flex gap-2">
                                <input type="file"
                                       name="files[]"
                                       class="form-control @error('files.*') is-invalid @enderror"
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt">
                                <button type="button" class="btn btn-danger removeFileInput" style="display:none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addFileInput">
                            <i class="fas fa-plus"></i> Add Another File
                        </button>
                        <small class="form-text text-muted d-block mt-2">
                            Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, TXT (Max 10MB each)
                        </small>
                        @error('files.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerText='Uploading...'; this.form.submit();">
                            <i class="fas fa-upload"></i> Upload Note
                        </button>
                        <a href="{{ route('teacher.notes.index') }}" class="btn btn-secondary ms-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>

                </form>

            </div>

        </div>
    </div>
</div>

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
                       class="form-control"
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt">
                <button type="button" class="btn btn-danger removeFileInput">
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
                    removeBtn.addEventListener('click', function () {
                        group.remove();
                    });
                } else {
                    removeBtn.style.display = 'none';
                }
            });
        }

        // Initial update
        updateRemoveButtons();
    });
</script>

@endsection
