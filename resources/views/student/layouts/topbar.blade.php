<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex align-items-center">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ route('student.dashboard') }}" class="logo logo-dark">
                    <!-- <span class="logo-sm">
                        <i class="fas fa-graduation-cap text-primary" style="font-size: 22px;"></i>
                    </span> -->
                    <span class="logo-lg d-flex align-items-center gap-2">
                        <i class="fas fa-graduation-cap text-primary" style="font-size: 22px;"></i>
                        <span class="logo-txt">FOMS BMS</span>
                    </span>
                </a>

                <a href="{{ route('student.dashboard') }}" class="logo logo-light">
                    <!-- <span class="logo-sm">
                        <i class="fas fa-graduation-cap text-white" style="font-size: 22px;"></i>
                    </span> -->
                    <span class="logo-lg d-flex align-items-center gap-2">
                        <!-- <i class="fas fa-graduation-cap text-white" style="font-size: 22px;"></i> -->
                        <span class="logo-txt">FOMS BMS</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item ms-2" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>
        </div>

        <div class="d-flex align-items-center gap-2">

            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item" id="mode-setting-btn">
                    <i data-feather="sun" class="icon-lg layout-mode-light"></i>
                </button>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon right-bar-toggle">
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