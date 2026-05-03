
<!-- Right Sidebar -->
<div class="right-bar">
    <div data-simplebar class="h-100">
        <div class="rightbar-title d-flex align-items-center bg-dark p-3">
            <h5 class="m-0 me-2 text-white">Sessions</h5>
            <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                <i class="mdi mdi-close noti-icon"></i>
            </a>
        </div>

        <div class="p-3">
            @if(isset($classHours) && $classHours->count() > 0)
                @foreach($classHours as $hour)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>{{ $hour->classRoom->name ?? '-' }}</strong>
                            <span class="badge bg-warning">Pending</span>
                        </div>
                        <small class="text-muted d-block">
                            <i class="mdi mdi-calendar-clock"></i>
                            {{ $hour->created_at->format('d M Y, h:i A') }}
                        </small>
                        @if($hour->google_meet_link)
                            <a href="{{ route('student.classes.join', encrypt($hour->id)) }}"
                                target="_blank" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-video"></i> Join
                            </a>
                        @endif
                    </div>
                @endforeach
            @else
                <p class="text-muted text-center mt-3">No Sessions found.</p>
            @endif
        </div>
    </div>
</div>
<!-- /Right-bar -->

<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>
