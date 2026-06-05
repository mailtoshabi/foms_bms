@extends('student.layouts.master-layouts-noleft')

@section('title', 'Class Details')

@section('content')

    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle" title="Go Back" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">
                {{ $class->name }}
                @if ($class->is_completed)
                    <span class="badge bg-soft-success text-success border border-success rounded-pill px-3 py-1 font-size-12 ms-2">
                        <i class="fas fa-check-circle me-1"></i> Completed
                    </span>
                @else
                    <span class="badge bg-soft-primary text-primary border border-primary rounded-pill px-3 py-1 font-size-12 ms-2">
                        <i class="fas fa-play-circle me-1"></i> Active
                    </span>
                @endif
            </h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="row">
                <div class="col-md-6">
                    <p><strong>Course:</strong> {{ $class->course->name ?? '-' }}</p>
                    <p><strong>Type:</strong> {{ ucfirst($class->classType->name ?? '-') . ' Class' }}</p>
                    <p><strong>Teacher:</strong> {{ $class->teachers->pluck('name')->join(', ') ?: 'Not Assigned' }}</p>
                </div>
                <div class="col-md-6">
                    @if($class->selected_days)
                        <p><strong>Days:</strong> {{ implode(', ', $class->selected_days ?? []) }}</p>
                    @endif
                    <p><strong>Time:</strong>
                        {{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}</p>
                    <p><strong>Duration:</strong> {{ $class->slot_duration }} minutes</p>
                    <p><strong>Monthly Sessions:</strong> {{ $class->classes_per_week * 4 }}</p>
                </div>
            </div>

            {{-- My Attendance --}}
            <hr>
            <h5>My Attendance</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="border rounded p-3 text-center">
                        <h3
                            class="{{ $myPercentage >= 75 ? 'text-success' : ($myPercentage >= 50 ? 'text-warning' : 'text-danger') }}">
                            {{ $myPercentage }}%
                        </h3>
                        <p class="text-muted mb-1">{{ $myPresent }} / {{ $totalClasses }} classes attended</p>
                        <div class="progress">
                            <div class="progress-bar {{ $myPercentage >= 75 ? 'bg-success' : ($myPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                style="width: {{ $myPercentage }}%"></div>
                        </div>
                    </div>
                </div>
            </div>



            {{-- Sessions --}}
            <hr>
            <h5>Sessions</h5>
            <div class="table-responsive">
                <table class="table table-bordered  align-middle table-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Session Link</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($class->classHours as $hour)
                            <tr>
                                <td>{{ $hour->created_at->format('d M Y h:i A') }}</td>
                                <td>
                                    @if($hour->status == 'pending' && $hour->google_meet_link)
                                        <a href="{{ $hour->google_meet_link }}" target="_blank" class="btn btn-sm btn-primary"
                                            onclick="fetch('{{ route('student.classes.join', encrypt($hour->id)) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })">
                                            <i class="fas fa-video"></i> Join
                                        </a>
                                    @elseif($hour->status == 'completed')
                                        <span class="text-muted">Expired</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($hour->status == 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No Sessions yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Class Notes --}}
            <hr>
            <h5>Class Notes</h5>
            @forelse($class->notes as $note)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6>
                                    {{ $note->title }}
                                    <span class="badge bg-{{ $note->visibility === 'public' ? 'info' : 'warning' }}">
                                        {{ ucfirst($note->visibility) }}
                                    </span>
                                </h6>
                                <small class="text-muted">
                                    By {{ $note->teacher->name ?? 'Unknown' }} &bull;
                                    {{ $note->created_at->format('M d, Y H:i') }}
                                </small>
                                @if($note->content)
                                    <p class="text-muted mt-2 mb-0">{{ Str::limit($note->content, 150) }}</p>
                                @endif
                            </div>
                            <a href="{{ route('student.notes.show', encrypt($note->id)) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>

                        @if($note->files->count() > 0)
                            <div class="mt-3">
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-paperclip"></i> {{ $note->files->count() }} file(s)
                                </small>
                                <div class="list-group list-group-sm">
                                    @foreach($note->files as $file)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-file text-muted me-2"></i>
                                                <strong>{{ $file->file_name }}</strong>
                                                <small class="text-muted d-block">{{ $file->file_type }}</small>
                                            </div>
                                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">No notes available for this class.</p>
            @endforelse

        </div>
    </div>

@endsection