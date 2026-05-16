<!-- Right Sidebar (Sessions) -->
<div class="right-bar" id="sessions-sidebar">
    <div data-simplebar class="h-100">
        <div class="rightbar-title d-flex align-items-center bg-dark p-3">
            <h5 class="m-0 me-2 text-white">Live Sessions</h5>
            <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                <i class="mdi mdi-close noti-icon"></i>
            </a>
        </div>

        <div class="p-3">
            <h6 class="fw-bold text-uppercase font-size-12 mb-3">Available Sessions</h6>
            @if(isset($classHours) && $classHours->count() > 0)
                @foreach($classHours as $hour)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="font-size-13">{{ $hour->classRoom->name ?? '-' }}</strong>
                            <span class="badge bg-soft-warning text-warning">Live</span>
                        </div>
                        <small class="text-muted d-block font-size-11">
                            <i class="mdi mdi-calendar-clock"></i>
                            {{ $hour->link_updated_at->format('d M Y, h:i A') }}
                        </small>
                        @if($hour->google_meet_link)
                            <a href="{{ $hour->google_meet_link }}" target="_blank" class="btn btn-sm btn-primary w-100 mt-2"
                                onclick="fetch('{{ route('student.classes.join', encrypt($hour->id)) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })">
                                <i class="fas fa-video me-1"></i> Join Now
                            </a>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <i class="mdi mdi-calendar-blank-outline font-size-24 text-muted"></i>
                    <p class="text-muted mt-2 font-size-12">No active sessions found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- /Right-bar -->
