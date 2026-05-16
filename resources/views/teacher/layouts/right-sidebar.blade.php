<!-- Right Sidebar -->
<div class="right-bar">
    <div data-simplebar class="h-100">
        <div class="rightbar-title d-flex align-items-center bg-dark p-3">
            <h5 class="m-0 me-2 text-white">Notifications</h5>
            <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                <i class="mdi mdi-close noti-icon"></i>
            </a>
        </div>

        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-uppercase font-size-12 m-0">Recent Messages</h6>
                <a href="{{ route('teacher.messages.create') }}"
                    class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0 font-size-11 fw-bold"
                    title="New Message">
                    <i class="fas fa-plus me-1"></i> New
                </a>
            </div>
            <div class="message-list">
                @forelse($teacherMessages as $msg)
                    <a href="{{ route('teacher.messages.show', encrypt($msg->id)) }}"
                        class="text-reset notification-item d-block p-2 mb-2 rounded border-bottom bg-light">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-2">
                                <img src="https://ui-avatars.com/api/?name={{ optional($msg->sender)->name ?? 'User' }}&size=100"
                                    class="rounded-circle avatar-xs" alt="user-pic">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-1 font-size-13 text-truncate">
                                        {{ optional($msg->sender)->name ?? 'System' }}</h6>
                                    @if (!$msg->is_read && ($msg->sender_type != 'App\Models\Teacher' || $msg->sender_id != Auth::guard('teacher')->id()))
                                        <span class="badge bg-danger rounded-pill"
                                            style="font-size: 9px; padding: 2px 5px;">New</span>
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
            @if (isset($teacherMessagesCount) && $teacherMessagesCount > 0)
                <div class="text-center mt-2">
                    <a href="{{ route('teacher.messages.index') }}" class="btn btn-sm btn-link font-size-12">View All
                        Messages</a>
                </div>
            @endif
        </div>

    </div> <!-- end slimscroll-menu-->
</div>
<!-- /Right-bar -->

<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>