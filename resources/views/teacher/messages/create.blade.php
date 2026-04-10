@extends('teacher.layouts.master')

@section('title', 'New Message')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">New Message</h4>
        <a href="{{ route('teacher.messages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Inbox
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('teacher.messages.store') }}">
            @csrf

            {{-- Recipient Type Toggle --}}
            <div class="mb-3">
                <label class="form-label">Send To</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="to_type"
                               id="toStudent" value="student" checked>
                        <label class="form-check-label" for="toStudent">Individual Student</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="to_type"
                               id="toClass" value="class">
                        <label class="form-check-label" for="toClass">Entire Class</label>
                    </div>
                </div>
            </div>

            {{-- Student Select --}}
            <div class="mb-3" id="studentField">
                <label class="form-label">Select Student</label>
                <select name="student_id" class="form-select @error('student_id') is-invalid @enderror">
                    <option value="">-- Select Student --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->name }}
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Class Select --}}
            <div class="mb-3" id="classField" style="display:none">
                <label class="form-label">Select Class</label>
                <select name="class_room_id" class="form-select @error('class_room_id') is-invalid @enderror">
                    <option value="">-- Select Class --</option>
                    @foreach($classRooms as $room)
                        <option value="{{ $room->id }}" {{ old('class_room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->name }} ({{ $room->course->name ?? '-' }})
                        </option>
                    @endforeach
                </select>
                @error('class_room_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">All students enrolled in this class will receive this message.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control @error('message') is-invalid @enderror"
                          rows="4" required placeholder="Type your message...">{{ old('message') }}</textarea>
                @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Sending...'; this.form.submit();">
                 <i class="fas fa-paper-plane"></i> Send Message
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </form>
    </div>
</div>

@endsection

@section('script')
<script>
    document.querySelectorAll('input[name="to_type"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            var isClass = this.value === 'class';
            document.getElementById('classField').style.display   = isClass ? '' : 'none';
            document.getElementById('studentField').style.display = isClass ? 'none' : '';
        });
    });
</script>
@endsection

