<!-- Right Sidebar -->
<div class="right-bar">
    <div data-simplebar class="h-100">
        <div class="rightbar-title d-flex align-items-center bg-dark p-3">
            <h5 class="m-0 me-2 text-white">Notification Center</h5>
            <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                <i class="mdi mdi-close noti-icon"></i>
            </a>
        </div>

        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-uppercase font-size-12 m-0">Recent Messages</h6>
                <a href="{{ route('student.messages.create') }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0 font-size-11 fw-bold" title="New Message">
                    <i class="fas fa-plus me-1"></i> New
                </a>
            </div>
            <div class="message-list">
                @forelse($studentMessages as $msg)
                    <a href="{{ route('student.messages.show', encrypt($msg->id)) }}"
                        class="text-reset notification-item d-block p-2 mb-2 rounded border-bottom bg-light">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-2">
                                <img src="https://ui-avatars.com/api/?name={{ optional($msg->sender)->name ?? 'User' }}&size=100"
                                    class="rounded-circle avatar-xs" alt="user-pic">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-1 font-size-13 text-truncate">{{ optional($msg->sender)->name ?? 'System' }}</h6>
                                    @if (!$msg->is_read && ($msg->sender_type != 'App\Models\Student' || $msg->sender_id != Auth::guard('student')->id()))
                                        <span class="badge bg-danger rounded-pill" style="font-size: 8px;">New</span>
                                    @endif
                                </div>
                                <div class="text-muted">
                                    <p class="mb-0 font-size-12 text-truncate">{{ $msg->message }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-3">
                        <i class="mdi mdi-message-off-outline font-size-24 text-muted"></i>
                        <p class="text-muted mt-2">No new messages</p>
                    </div>
                @endforelse
            </div>

            @if(isset($studentMessages) && $studentMessages->count() > 0)
                <div class="text-center mt-2 mb-4">
                    <a href="{{ route('student.messages.index') }}" class="btn btn-sm btn-link font-size-12">View All Messages</a>
                </div>
            @endif

            <hr class="my-3">
            <h6 class="fw-bold text-uppercase font-size-12 mb-3">Live Sessions</h6>
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
                <p class="text-muted text-center mt-3 font-size-12">No active sessions found.</p>
            @endif
        </div>
    </div>
</div>
<!-- /Right-bar -->

<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>