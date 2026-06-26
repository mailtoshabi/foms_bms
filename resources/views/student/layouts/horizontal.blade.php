<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="">
                <a href="{{ route('student.dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <!-- <i class="fas fa-graduation-cap" style="font-size: 24px; color: #fff;"></i> -->
                        <span class="logo-txt">FOMS</span>
                    </span>
                    <span class="logo-lg">
                        <!-- <i class="fas fa-graduation-cap" style="font-size: 24px; color: #fff;"></i> -->

                        <span class="logo-txt">FOMS BMS</span>
                    </span>
                </a>
            </div>
        </div>

        <div class="d-flex">



            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item" id="mode-setting-btn">
                    <i data-feather="sun" class="icon-lg layout-mode-light"></i>
                </button>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon me-2 right-bar-toggle"
                    data-target="messages-sidebar">
                    <i data-feather="mail" class="icon-lg"></i>
                    @if (isset($studentMessagesUnreadCount) && $studentMessagesUnreadCount > 0)
                        <span class="badge bg-danger rounded-pill">{{ $studentMessagesUnreadCount }}</span>
                    @endif
                </button>
            </div>



            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon me-2 right-bar-toggle"
                    data-target="sessions-sidebar">
                    <i data-feather="clock" class="icon-lg"></i>
                    @if(isset($pendingClassHoursCount) && $pendingClassHoursCount > 0)
                        <span class="badge bg-danger rounded-pill">{{ $pendingClassHoursCount }}</span>
                    @endif
                </button>
            </div>


            <div class="dropdown d-inline-block">
                <button type="button" class="btn d-flex align-items-center gap-2 border-0"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user"
                        src="@if ((Auth::guard('student')->user()->photo == '') || (empty(Auth::guard('student')->user()->photo))) https://ui-avatars.com/api/?name={{ Auth::guard('student')->user()->name }}&size=200 @else {{ URL::asset('images/' . Auth::guard('student')->user()->photo) }} @endif"
                        alt="Header Avatar">
                    <span
                        class="d-none d-xl-inline-block ms-1 fw-bold text-dark">{{ Auth::guard('student')->user()->name }}</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block text-muted"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('student.dashboard') }}">
                        <i class="mdi mdi-view-dashboard font-size-16 align-middle text-primary"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('student.profile') }}">
                        <i class="mdi mdi-face-profile font-size-16 align-middle text-primary"></i>
                        <span>Profile</span>
                    </a>
                    @if(Auth::guard('student')->user()->relatedStudents()->count() > 0)
                        <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#switchAccountModal">
                            <i class="bx bx-transfer font-size-16 align-middle text-success"></i>
                            <span>Switch Account</span>
                        </a>
                    @endif
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="javascript:void(0);"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bx bx-power-off font-size-16 align-middle text-danger"></i>
                        <span>Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('student.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>

@if(Auth::guard('student')->user()->relatedStudents()->count() > 0)
<div class="modal fade" id="switchAccountModal" tabindex="-1" aria-labelledby="switchAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="switchAccountModalLabel">Switch Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    @foreach(Auth::guard('student')->user()->relatedStudents as $relatedStudent)
                        <form method="POST" action="{{ route('student.switch', encrypt($relatedStudent->id)) }}">
                            @csrf
                            <button type="submit" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border-0 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <img class="rounded-circle" width="40" height="40"
                                         src="@if (($relatedStudent->photo == '') || (empty($relatedStudent->photo))) https://ui-avatars.com/api/?name={{ urlencode($relatedStudent->name) }}&size=200 @else {{ URL::asset('storage/' . $relatedStudent->photo) }} @endif"
                                         alt="Avatar">
                                    <div class="text-start">
                                        <h6 class="mb-0 fw-bold">{{ $relatedStudent->name }}</h6>
                                        <small class="text-muted">{{ $relatedStudent->admission_no }}</small>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif