@extends('teacher.layouts.master-layouts-noleft')

@section('title', 'New Message')

@section('content')

    <div class="portal-page-header">
        <h4 class="m-0 fw-bold text-dark">New Message</h4>
        <a href="{{ route('teacher.messages.index') }}" class="portal-btn"
            style="background: rgba(100, 116, 139, 0.1); color: #64748b;">
            <i class="fas fa-arrow-left"></i> Back to Inbox
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="portal-card">

                <div class="portal-card-header">
                    <h4>Compose Message</h4>
                </div>

                <div class="portal-card-body">

                    <form method="POST" action="{{ route('teacher.messages.store') }}">
                        @csrf

                        {{-- Recipient Type Toggle --}}
                        <div class="mb-4">
                            <label class="portal-label">Send To</label>
                            <div class="d-flex gap-4">
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="to_type" id="toStudent"
                                        value="student" checked>
                                    <label class="form-check-label fw-semibold text-muted" for="toStudent"
                                        style="cursor: pointer;">Individual Student</label>
                                </div>
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="to_type" id="toClass" value="class">
                                    <label class="form-check-label fw-semibold text-muted" for="toClass"
                                        style="cursor: pointer;">Entire Class</label>
                                </div>
                            </div>
                        </div>

                        {{-- Student Select --}}
                        <div class="mb-3" id="studentField">
                            <label class="portal-label">Select Student</label>
                            <select name="student_id" class="portal-select @error('student_id') is-invalid @enderror">
                                <option value="">-- Select Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Class Select --}}
                        <div class="mb-3" id="classField" style="display:none">
                            <label class="portal-label">Select Class</label>
                            <select name="class_room_id" class="portal-select @error('class_room_id') is-invalid @enderror">
                                <option value="">-- Select Class --</option>
                                @foreach($classRooms as $room)
                                    <option value="{{ $room->id }}" {{ old('class_room_id') == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }} ({{ $room->course->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('class_room_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted d-block mt-2">All students enrolled in this class will
                                receive this message.</small>
                        </div>

                        <div class="mb-4">
                            <label class="portal-label">Message</label>
                            <textarea name="message" class="portal-input @error('message') is-invalid @enderror" rows="5"
                                required placeholder="Type your message...">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="portal-btn"
                                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff;"
                                onclick="this.disabled=true; this.innerText='Sending...'; this.form.submit();">
                                <i class="fas fa-paper-plane me-1"></i> Send Message
                            </button>
                            <a href="{{ route('teacher.messages.index') }}" class="portal-btn"
                                style="background: rgba(100, 116, 139, 0.1); color: #64748b;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        document.querySelectorAll('input[name="to_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isClass = this.value === 'class';
                document.getElementById('classField').style.display = isClass ? '' : 'none';
                document.getElementById('studentField').style.display = isClass ? 'none' : '';
            });
        });
    </script>
@endsection