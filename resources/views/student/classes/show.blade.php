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
                        {{ $class->time_slot ? \Carbon\Carbon::parse($class->time_slot)->format('h:i A') : '' }}</p>
                    <p><strong>Duration:</strong> {{ $class->slot_duration }} minutes</p>
                    <p><strong>Monthly Sessions:</strong> {{ $class->classes_per_week * 4 }}</p>
                </div>
            </div>

            {{-- Portal Sections Grid --}}
            <hr>
            <div class="row">
                {{-- Card 1: Attendance --}}
                <div class="col-md-3 mb-3">
                    <div class="card h-100 border p-3" style="border-radius: 12px;">
                        <div class="card-body text-center d-flex flex-column justify-content-between p-0">
                            <div>
                                <h6 class="text-muted fw-bold mb-2">My Attendance</h6>
                                <h3 class="{{ $myPercentage >= 75 ? 'text-success' : ($myPercentage >= 50 ? 'text-warning' : 'text-danger') }} mb-1 fw-bold">
                                    {{ $myPercentage }}%
                                </h3>
                                <p class="text-muted mb-2 small">{{ $myPresent }} / {{ $totalClasses }} classes</p>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar {{ $myPercentage >= 75 ? 'bg-success' : ($myPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                    style="width: {{ $myPercentage }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Sessions --}}
                <div class="col-md-3 mb-3">
                    <a href="#sessions-section" class="text-decoration-none">
                        <div class="card h-100 border text-center p-3" style="transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border-radius: 12px;"
                             onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'"
                             onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-0">
                                <div class="rounded-circle mb-3 d-inline-flex justify-content-center align-items-center" style="width: 50px; height: 50px; background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                                    <i class="fas fa-video fs-5"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">My Sessions</h6>
                                <p class="text-muted small mb-0">{{ $class->classHours->count() }} Session(s)</p>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Card 3: Notes --}}
                <div class="col-md-3 mb-3">
                    <a href="#notes-section" class="text-decoration-none">
                        <div class="card h-100 border text-center p-3" style="transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border-radius: 12px;"
                             onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'"
                             onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-0">
                                <div class="rounded-circle mb-3 d-inline-flex justify-content-center align-items-center" style="width: 50px; height: 50px; background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0;">
                                    <i class="fas fa-sticky-note fs-5"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Class Notes</h6>
                                <p class="text-muted small mb-0">{{ $class->notes->count() }} Note(s)</p>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Card 4: Homeworks --}}
                <div class="col-md-3 mb-3">
                    <a href="#homework-section" class="text-decoration-none">
                        <div class="card h-100 border text-center p-3" style="transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border-radius: 12px;"
                             onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'"
                             onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-0">
                                <div class="rounded-circle mb-3 d-inline-flex justify-content-center align-items-center" style="width: 50px; height: 50px; background-color: rgba(25, 135, 84, 0.1); color: #198754;">
                                    <i class="fas fa-tasks fs-5"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Homework</h6>
                                <p class="text-muted small mb-0">{{ $class->homeworks->count() }} Assignment(s)</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>



            {{-- Sessions --}}
            <hr>
            <h5 id="sessions-section">Sessions</h5>
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
                                        @if(\Carbon\Carbon::parse($hour->link_updated_at)->isToday())
                                            <a href="{{ $hour->google_meet_link }}" target="_blank" class="btn btn-sm btn-primary"
                                                onclick="fetch('{{ route('student.classes.join', encrypt($hour->id)) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })">
                                                <i class="fas fa-video"></i> Join
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-light text-nowrap" disabled title="Wait for teacher to update the class link.">
                                                <i class="fas fa-video text-muted"></i> Join (Wait for Link)
                                            </button>
                                        @endif
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
            <h5 id="notes-section">Class Notes</h5>
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

            {{-- Homework --}}
            <hr>
            <h5 id="homework-section">Homework</h5>
            @forelse($class->homeworks as $hw)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-dark fw-bold">
                                    {{ $hw->title }}
                                </h6>
                                <small class="text-muted">
                                    By {{ $hw->teacher->name ?? 'Unknown' }} &bull;
                                    {{ $hw->created_at->format('M d, Y H:i') }}
                                </small>
                                @if($hw->content)
                                    <p class="text-muted mt-2 mb-0">{{ Str::limit($hw->content, 150) }}</p>
                                @endif
                            </div>
                            <a href="{{ route('student.homeworks.show', encrypt($hw->id)) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View/Submit
                            </a>
                        </div>

                        @if($hw->files->count() > 0)
                            <div class="mt-3">
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-paperclip"></i> {{ $hw->files->count() }} file(s)
                                </small>
                                <div class="list-group list-group-sm">
                                    @foreach($hw->files as $file)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-file text-muted me-2"></i>
                                                <strong>{{ $file->file_name }}</strong>
                                                <small class="text-muted d-block">{{ $file->file_size_formatted }}</small>
                                            </div>
                                            <a href="{{ route('student.homeworks.file.download', encrypt($file->id)) }}" target="_blank"
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
                <p class="text-muted">No homework available for this class.</p>
            @endforelse

        </div>
    </div>

@endsection